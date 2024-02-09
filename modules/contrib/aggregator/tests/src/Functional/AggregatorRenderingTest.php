<?php

namespace Drupal\Tests\aggregator\Functional;

use Drupal\aggregator\Entity\Item;
use Drupal\views\Entity\View;

/**
 * Tests display of aggregator items on the page.
 *
 * @group aggregator
 */
class AggregatorRenderingTest extends AggregatorTestBase {

  /**
   * Modules to install.
   *
   * @var array
   */
  protected static $modules = ['block', 'test_page_test'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  protected function setUp(): void {
    parent::setUp();

    $this->drupalPlaceBlock('page_title_block');
  }

  /**
   * Adds a feed block to the page and checks its links.
   */
  public function testBlockLinks() {
    // Create feed.
    $this->createSampleNodes();
    $feed = $this->createFeed();
    $this->updateFeedItems($feed, $this->getDefaultFeedItemCount());

    // Need admin user to be able to access block admin.
    $admin_user = $this->drupalCreateUser([
      'administer blocks',
      'access administration pages',
      'administer news feeds',
      'access news feeds',
    ]);
    $this->drupalLogin($admin_user);

    $block = $this->drupalPlaceBlock("aggregator_feed_block", ['label' => 'feed-' . $feed->label()]);

    // Configure the feed that should be displayed.
    $block->getPlugin()->setConfigurationValue('feed', $feed->id());
    $block->getPlugin()->setConfigurationValue('block_count', 2);
    $block->save();

    // Confirm that the block is now being displayed on pages.
    $this->drupalGet('test-page');
    $this->assertSession()->pageTextContains($block->label());

    // Confirm items appear as links.
    $items = $this->container->get('entity_type.manager')->getStorage('aggregator_item')->loadByFeed($feed->id(), 1);
    $this->assertSession()->linkByHrefExists(reset($items)->getLink());

    // Find the expected read_more link.
    $this->assertSession()->linkByHrefExists($feed->toUrl()->toString());
    $this->assertSession()->responseHeaderContains('X-Drupal-Cache-Tags', 'aggregator_feed:' . $feed->id());

    // Visit that page.
    $this->drupalGet($feed->toUrl()->getInternalPath());
    // Verify that aggregator feed page is available and has the correct title.
    $this->assertSession()->elementTextContains('xpath', '//h1', $feed->label());
    $this->assertSession()->responseHeaderContains('X-Drupal-Cache-Tags', 'aggregator_feed:' . $feed->id());
    $this->assertSession()->responseHeaderContains('X-Drupal-Cache-Tags', 'aggregator_feed_view');
    $this->assertSession()->responseHeaderContains('X-Drupal-Cache-Tags', 'aggregator_item_view');
  }

  /**
   * Creates a feed and checks that feed's page.
   */
  public function testFeedPage() {
    // Increase the number of items published in the rss.xml feed so we have
    // enough articles to test paging.
    $view = View::load('frontpage');
    $display = &$view->getDisplay('feed_1');
    $display['display_options']['pager']['options']['items_per_page'] = 30;
    $view->save();

    // Create a feed with 30 items.
    $this->createSampleNodes(30);

    // Request the /aggregator page so that it is cached.
    $this->drupalGet('aggregator');

    $feed = $this->createFeed();
    $this->updateFeedItems($feed, 30);
    // Change the post date for an item to be the most recent item to ensure
    // consistency in sorting.
    /** @var \Drupal\aggregator\Entity\Item $latest_item */
    $latest_item = Item::load(30);
    $latest_item->setPostedTime(time() + 1000);
    $latest_item->save();

    // Verify that items are loaded on the page by checking for the pager.
    $this->drupalGet('aggregator');
    $this->assertSession()->elementExists('xpath', '//ul[contains(@class, "pager__items")]');

    // Save the feed entity to invalidate the cache so that the paginated cache
    // context can be tested.
    $feed->save();

    // Request page with no feed items to ensure cache context is set correctly.
    $this->drupalGet('aggregator', ['query' => ['page' => 2]]);
    // Check for presence of an aggregator pager.
    $this->drupalGet('aggregator');
    $this->assertSession()->elementExists('xpath', '//ul[contains(@class, "pager__items")]');

    // Check for sources page title.
    $this->drupalGet('aggregator/sources');
    $this->assertSession()->elementTextContains('xpath', '//h1', 'Sources');

    // Find the expected read_more link on the sources page.
    $href = $feed->toUrl()->toString();
    $this->assertSession()->linkByHrefExists($href);
    $this->assertSession()->responseHeaderContains('X-Drupal-Cache-Tags', 'aggregator_feed:' . $feed->id());

    // Check the rss aggregator page as anonymous user.
    $this->drupalLogout();
    $this->drupalGet('aggregator/rss');
    $this->assertSession()->statusCodeEquals(403);

    // Check the rss aggregator page as admin.
    $this->drupalLogin($this->adminUser);
    $this->drupalGet('aggregator/rss');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->responseHeaderEquals('Content-Type', 'application/rss+xml; charset=utf-8');

    // We can't use Mink xpath queries for checking the content of the RSS and
    // OPML pages because they are XML. Mink only supports HTML.
    $rss_content = $this->getSession()->getPage()->getContent();
    $rss_xml = simplexml_load_string($rss_content);
    $rss_xml->registerXPathNamespace('dc', 'http://purl.org/dc/elements/1.1/');
    $rss = $rss_xml->xpath('//rss/channel');
    $this->assertEquals((string) $rss[0]->title, 'Aggregator RSS feed');
    $this->assertEquals(count($rss[0]->item), 10, 'Found 10 feed items.');

    // Check for item content in the RSS feed.
    $this->assertEquals((string) $rss[0]->item[0]->title, $latest_item->label());
    $this->assertEquals((string) $rss[0]->item[0]->description, $latest_item->getDescription());
    $this->assertEquals((string) $rss[0]->item[0]->pubDate, gmdate('r', $latest_item->getPostedTime()));
    $this->assertEquals((string) $rss[0]->item[0]->xpath('//dc:creator')[0], $latest_item->getAuthor());
    $this->assertEquals((string) $rss[0]->item[0]->guid, $latest_item->getGuid());

    // Check the opml aggregator page.
    $this->drupalGet('aggregator/opml');
    $opml_content = $this->getSession()->getPage()->getContent();
    $opml_xml = simplexml_load_string($opml_content);
    $attributes = $opml_xml->xpath('//outline[1]')[0]->attributes();
    $this->assertEquals('rss', $attributes->type);
    $this->assertEquals($feed->label(), $attributes->text);
    $this->assertEquals($feed->getUrl(), $attributes->xmlUrl);

    // Check for the presence of a pager.
    $this->drupalGet('aggregator/sources/' . $feed->id());
    $this->assertSession()->elementExists('xpath', '//ul[contains(@class, "pager__items")]');
    $this->assertSession()->responseHeaderContains('X-Drupal-Cache-Tags', 'aggregator_feed:' . $feed->id());
    $this->assertSession()->responseHeaderContains('X-Drupal-Cache-Tags', 'aggregator_feed_view');
    $this->assertSession()->responseHeaderContains('X-Drupal-Cache-Tags', 'aggregator_item_view');
  }

  /**
   * Tests that HTML entities in item titles are rendered correctly.
   */
  public function testHtmlEntitiesSample() {
    $feed = $this->createFeed($this->getHtmlEntitiesSample());
    $feed->refreshItems();
    $this->drupalGet('aggregator/sources/' . $feed->id());
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->responseContains("Quote&quot; Amp&amp;");
  }

}
