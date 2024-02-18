<?php

namespace Drupal\Tests\aggregator\Kernel\Plugin\aggregator\parser;

use Drupal\aggregator\Entity\Feed;
use Drupal\KernelTests\KernelTestBase;

/**
 * @coversDefaultClass \Drupal\aggregator\Plugin\aggregator\parser\DefaultParser
 * @group aggregator
 */
class DefaultParserTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['aggregator', 'options'];

  /**
   * An instance of the DefaultParser plugin.
   *
   * @var \Drupal\aggregator\Plugin\aggregator\parser\DefaultParser
   */
  protected $parser;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('aggregator_feed');
    $this->installEntitySchema('aggregator_item');

    /** @var \Drupal\aggregator\Plugin\AggregatorPluginManager $plugin_manager */
    $plugin_manager = $this->container->get('plugin.manager.aggregator.parser');
    /** @var \Drupal\aggregator\Plugin\aggregator\parser\DefaultParser $parser */
    $this->parser = $plugin_manager->createInstance('aggregator');
  }

  /**
   * Tests a feed that uses the Atom format.
   */
  public function testAtomSample() {
    $feed = Feed::create([
      'title' => 'Processor test feed',
      'url' => 'https://example.com/rss.xml',
      'source_string' => file_get_contents($this->getModulePath('aggregator_test') . '/aggregator_test_atom.xml'),
    ]);
    $feed->save();

    $this->parser->parse($feed);

    $item = $feed->items[0];
    $this->assertSame('Atom-Powered Robots Run Amok', $item['title']);
    $this->assertSame('http://example.org/2003/12/13/atom03', $item['link']);
    $this->assertSame('Some text.', $item['description']);
    $this->assertEquals('urn:uuid:1225c695-cfb8-4ebb-aaaa-80da344efa6a', $item['guid'], 'Atom entry id element is parsed correctly.');

    // Check for second feed entry.
    $item = $feed->items[1];
    $this->assertSame('We tried to stop them, but we failed.', $item['title']);
    $this->assertSame('http://example.org/2003/12/14/atom03', $item['link']);
    $this->assertSame('Some other text.', $item['description']);
    $this->assertEquals('urn:uuid:1225c695-cfb8-4ebb-bbbb-80da344efa6a', $item['guid'], 'Atom entry id element is parsed correctly.');
  }

  /**
   * Tests error handling when an invalid feed is added.
   */
  public function testInvalidFeed() {
    // Simulate a typo in the URL to force a curl exception.
    $invalid_url = 'https:/www.drupal.org';
    $feed = Feed::create([
      'url' => $invalid_url,
      'title' => $this->randomMachineName(),
    ]);
    $feed->save();

    $this->parser->parse($feed);

    // Check for the error message in the stack.
    /** @var \Drupal\Core\Render\Markup $message */
    $message = \Drupal::messenger()->messagesByType('error')[0];
    $this->assertStringStartsWith('The feed from <em class="placeholder">' . $feed->getUrl() . '</em> seems to be broken because of error', (string) $message);
  }

  /**
   * Test that post-dated item dates are corrected. A NULL value in
   * $expected_result indicates that it should be the request time.
   *
   * @dataProvider provideDateData
   */
  public function testParsePostDatedItem(string $date, ?bool $normalize, ?int $expected_result) {
    if ($expected_result === NULL) {
      $expected_result = \Drupal::time()->getRequestTime();
    }

    $feed = Feed::create([
      'title' => 'Processor test feed',
      'url' => 'https://example.com/rss.xml',
      'source_string' => <<<EOT
        <?xml version="1.0" encoding="UTF-8"?>
        <rss version="0.91">
          <channel>
            <title>Example</title>
            <link>https://example.com/</link>
            <item>
              <title>Example Item</title>
              <link>https://example.com/</link>
              <pubDate>$date</pubDate>
            </item>
          </channel>
        </rss>
        EOT,
    ]);
    $feed->save();

    $config = \Drupal::configFactory()->getEditable('aggregator.settings');
    $config->set('normalize_post_dates', $normalize);
    $config->save();

    $this->parser->parse($feed);

    $this->assertSame($expected_result, $feed->items[0]['timestamp']);
  }

  /**
   * The data provider for testParseDateModified().
   *
   * @return array
   */
  public function provideDateData(): array {
    return [
      'past_date_not_normalized' => [
        'Sat, Jan 01 2000 00:00:00 -0500',
        FALSE,
        946702800,
      ],
      'past_date_normalized' => [
        'Sat, Jan 01 2000 00:00:00 -0500',
        TRUE,
        946702800,
      ],
      'future_date_not_normalized' => [
        'Sat, Jan 01 3000 00:00:00 -0500',
        FALSE,
        32503957200,
      ],
      'future_date_normalized' => [
        'Sat, Jan 01 3000 00:00:00 -0500',
        TRUE,
        NULL,
      ],
      'null_setting' => [
        'Sat, Jan 01 3000 00:00:00 -0500',
        NULL,
        32503957200,
      ],
    ];
  }

  /**
   * Tests a feed that uses the RSS 0.91 format.
   */
  public function testRss091Sample() {
    $feed = Feed::create([
      'title' => 'Processor test feed',
      'url' => 'https://example.com/rss.xml',
      'source_string' => file_get_contents($this->getModulePath('aggregator_test') . '/aggregator_test_rss091.xml'),
    ]);
    $feed->save();

    $this->parser->parse($feed);

    $this->assertSame('http://example.com/images/druplicon.png', $feed->getImage());

    $item = $feed->items[0];
    $this->assertSame('First example feed item title', $item['title']);
    $this->assertSame('http://example.com/example-turns-one', $item['link']);
    $this->assertSame('First example feed item description.', $item['description']);

    // Assert that our laminas-feed extension parses any author data, not just
    // the laminas-feed defaults.
    $item = $feed->items[3];
    $this->assertSame('Long author feed item title.', $item['title']);
    $this->assertSame('Long author feed item description.', $item['description']);
    $this->assertSame('http://example.com/long/author', $item['link']);
    $this->assertStringContainsString('I wanted to get out and walk eastward toward', $item['author']);

    // Assert that the item with the empty <author> tag was parsed.
    $item = $feed->items[7];
    $this->assertSame('Empty author feed item title.', $item['title']);
    $this->assertSame('http://example.com/empty/author', $item['link']);
    $this->assertSame("We've tested items with no author, but what about an empty author tag?", $item['description']);
    $this->assertSame('', $item['author']);

    // Assert that the laminas-feed default author parsing works as expected.
    $item = $feed->items[8];
    $this->assertSame('laminas-feed compatible author', $item['title']);
    $this->assertSame('http://example.com/compatible/author', $item['link']);
    $this->assertSame('The laminas-feed library expects author fields to contain data in the format "email@domain.extension (author name)" which is parses into email and name array keys. Arbitrary data not matching this format is discarded.', $item['description']);
    $this->assertSame('John Doe', $item['author']);
  }

}
