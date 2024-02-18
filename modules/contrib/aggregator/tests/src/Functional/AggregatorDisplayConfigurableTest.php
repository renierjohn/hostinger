<?php

namespace Drupal\Tests\aggregator\Functional;

use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\node\Entity\Node;

/**
 * Tests making aggregator_feed and aggregator_item base fields' displays
 * configurable.
 *
 * @group aggregator
 */
class AggregatorDisplayConfigurableTest extends AggregatorTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->drupalPlaceBlock('page_title_block');
  }

  /**
   * Sets feed base fields to configurable display and checks settings are
   * respected.
   */
  public function testFeedDisplayConfigurable() {
    $display = EntityViewDisplay::load('aggregator_feed.aggregator_feed.summary');
    $display->setComponent('description', ['region' => 'content'])
      ->setComponent('items', ['region' => 'hidden'])
      ->save();

    $feed = $this->createFeed($this->getRSS091Sample());
    $feed->refreshItems();
    $assert = $this->assertSession();

    // Check the aggregator_feed with Drupal default non-configurable display.
    $this->drupalGet('/aggregator/sources');
    $assert->elementTextContains('css', '.views-row > h2', $feed->label());
    $assert->elementTextContains('css', 'div.feed-description', $feed->getDescription());
    $assert->elementNotExists('css', '.field--name-title');
    $assert->elementNotExists('css', '.field--name-description');

    // Enable helper module to make base fields' displays configurable.
    \Drupal::service('module_installer')->install(['aggregator_display_configurable_test']);

    // Configure display.
    $display->setComponent('title', [
      'type' => 'text_default',
      'label' => 'above',
    ]);
    $display->setComponent('description', [
      'type' => 'aggregator_xss',
      'label' => 'hidden',
    ])->save();

    // Recheck the aggregator_feed with configurable display.
    $this->drupalGet('/aggregator/sources');
    $label = $feed->label();
    $assert->elementExists('css', ".views-row > div > div:nth-child(2):contains('$label')");
    $assert->elementTextContains('css', '.views-row > div > div:nth-child(2)', $feed->label());
    $assert->elementTextContains('css', 'div:last-child', $feed->getDescription());

    // Remove 'title' field from display.
    $display->removeComponent('title')->save();

    // Recheck the aggregator_feed with 'title' field removed from display.
    $this->drupalGet('/aggregator/sources');
    $assert->elementNotExists('css', ".views-row > div > div:nth-child(2):contains('$label')");
  }

  /**
   * Sets item base fields to configurable display and checks settings are
   * respected.
   */
  public function testItemDisplayConfigurable() {
    $this->createSampleNodes(1);
    $item = Node::load(1);
    $feed = $this->createFeed();
    $this->updateFeedItems($feed);
    $assert = $this->assertSession();

    // Check the aggregator_feed with Drupal default non-configurable display.
    $this->drupalGet('/aggregator');
    $assert->elementTextContains('css', '.aggregator-wrapper article > h3 > a', $item->label());
    $assert->elementTextNotContains('css', '.aggregator-wrapper', 'Title');

    // Enable helper module to make base fields' displays configurable.
    \Drupal::service('module_installer')->install(['aggregator_display_configurable_test']);

    // Recheck the aggregator_feed with configurable display.
    $this->drupalGet('/aggregator');
    $assert->elementNotExists('css', '.aggregator-wrapper h3 a');
    $assert->elementTextContains('css', '.aggregator-wrapper article > div > div', 'Title');
  }

}
