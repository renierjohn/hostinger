<?php

namespace Drupal\Tests\aggregator\Kernel\Plugin\aggregator\processor;

use Drupal\aggregator\Entity\Feed;
use Drupal\aggregator\Entity\Item;
use Drupal\KernelTests\KernelTestBase;

/**
 * @coversDefaultClass \Drupal\aggregator\Plugin\aggregator\processor\DefaultProcessor
 * @group aggregator
 */
class DefaultProcessorTest extends KernelTestBase {

  const AGGREGATOR_PROCESSOR_TEST_ITEM_DEFAULTS = [
    'link' => '',
    'title' => '',
    'description' => '',
    'author' => '',
    'guid' => 1,
    'timestamp' => 0,
  ];

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['aggregator', 'options'];

  /**
   * An instance of the DefaultProcessor plugin.
   *
   * @var \Drupal\aggregator\Plugin\aggregator\processor\DefaultProcessor
   */
  protected $processor;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('aggregator_feed');
    $this->installEntitySchema('aggregator_item');

    /** @var \Drupal\aggregator\Plugin\AggregatorPluginManager $plugin_manager */
    $plugin_manager = $this->container->get('plugin.manager.aggregator.processor');
    /** @var \Drupal\aggregator\Plugin\aggregator\processor\DefaultProcessor $processor */
    $this->processor = $plugin_manager->createInstance('aggregator');
  }

  /**
   * @covers ::process()
   *
   * Performs a simple test for items where we only need to verify whether they
   * are imported or not by querying for GUIDs. This does not perform any
   * validation of imported data.
   *
   * @dataProvider provideProcessData
   */
  public function testProcess(array $items, array $expected_item_guids) {
    $feed = Feed::create([
      'title' => 'Processor test feed',
      'url' => 'https://example.com/rss.xml',
      'items' => $items,
    ]);
    $feed->save();

    $this->processor->process($feed);

    // Assert that the items we expected were created.
    if ($expected_item_guids !== []) {
      $guid_check_results = \Drupal::entityQuery('aggregator_item')
        ->accessCheck(FALSE)
        ->condition('guid', $expected_item_guids)
        ->execute();
      $this->assertSame(count($expected_item_guids), count($guid_check_results));
    }
    // Assert that no additional items were created.
    $total_items_results = \Drupal::entityQuery('aggregator_item')
      ->accessCheck(FALSE)
      ->execute();
    $this->assertSame(count($expected_item_guids), count($total_items_results));
  }

  /**
   * The data provider for testProcess().
   *
   * @return array
   */
  public function provideProcessData(): array {
    return [
      'bad_timestamp' => [
        [
          [
            'link' => 'https://example.com',
            'title' => 'Feed item with bad timestamp',
            'description' => 'This item has a very old timestamp, which is known to have caused a PDOException that breaks the entire import process.  See https://www.drupal.org/project/aggregator/issues/1695852.',
            'guid' => 1,
            'timestamp' => -2209172400,
          ] + self::AGGREGATOR_PROCESSOR_TEST_ITEM_DEFAULTS,
          [
            'link' => 'https://example.com',
            'title' => 'Good feed item',
            'description' => 'This is a good feed item and should be imported properly even after the bad item fails.',
            'guid' => 2,
            'timestamp' => 0,
          ] + self::AGGREGATOR_PROCESSOR_TEST_ITEM_DEFAULTS,
        ],
        [2],
      ],
      'empty_title' => [
        [
          [
            'title' => '',
            'link' => 'http://example.com/empty/title',
            'description' => 'This is an item with an empty title. It should not be imported.',
          ] + self::AGGREGATOR_PROCESSOR_TEST_ITEM_DEFAULTS,
        ],
        [],
      ],
      'empty_description' => [
        [
          [
            'title' => 'Empty description feed item title.',
            'link' => 'http://example.com/empty/description',
            'description' => '',
          ] + self::AGGREGATOR_PROCESSOR_TEST_ITEM_DEFAULTS,
        ],
        [1],
      ],
      'empty_link' => [
        [
          [
            'title' => 'Empty link feed item title.',
            'link' => '',
            'description' => 'This is an item with an empty link.',
          ] + self::AGGREGATOR_PROCESSOR_TEST_ITEM_DEFAULTS,
        ],
        [1],
      ],
      'empty_author' => [
        [
          [
            'title' => 'Empty author feed item title.',
            'link' => 'http://example.com/empty/author',
            'description' => 'This is an item with an empty author',
            'author' => '',
          ] + self::AGGREGATOR_PROCESSOR_TEST_ITEM_DEFAULTS,
        ],
        [1],
      ],
    ];
  }

  /**
   * @covers ::process()
   *
   * Test importing an item with a long title which should be truncated to 255
   * characters.
   */
  public function testProcessLongTitle() {
    $feed = Feed::create([
      'title' => 'Processor test feed',
      'url' => 'https://example.com/rss.xml',
      'items' => [
        [
          'title' => "Second example feed item title. This title is extremely long so that it exceeds the 255 character limit for titles in feed item storage. In fact it's so long that this sentence isn't long enough so I'm rambling a bit to make it longer, nearly there now. Ah now it's long enough so I'll shut up.",
          'link' => 'http://example.com/example-turns-two',
          'description' => 'Second example feed item description.',
        ] + self::AGGREGATOR_PROCESSOR_TEST_ITEM_DEFAULTS,
      ],
    ]);
    $feed->save();

    $this->processor->process($feed);

    $item = Item::load(1);
    $this->assertStringStartsWith('Second example feed item title.', $item->getTitle());
  }

  /**
   * @covers ::process()
   *
   * Test importing an item with a long link.
   */
  public function testProcessLongLink() {
    $link = 'http://example.com/tomorrow/and/tomorrow/and/tomorrow/creeps/in/this/petty/pace/from/day/to/day/to/the/last/syllable/of/recorded/time/and/all/our/yesterdays/have/lighted/fools/the/way/to/dusty/death/out/out/brief/candle/life/is/but/a/walking/shadow/a/poor/player/that/struts/and/frets/his/hour/upon/the/stage/and/is/heard/no/more/it/is/a/tale/told/by/an/idiot/full/of/sound/and/fury/signifying/nothing';

    $feed = Feed::create([
      'title' => 'Processor test feed',
      'url' => 'https://example.com/rss.xml',
      'items' => [
        [
          'title' => 'Long link feed item title',
          'link' => $link,
          'description' => 'Long link feed item description',
        ] + self::AGGREGATOR_PROCESSOR_TEST_ITEM_DEFAULTS,
      ],
    ]);
    $feed->save();

    $this->processor->process($feed);

    $item = Item::load(1);
    $this->assertSame($link, $item->getLink());
  }

  /**
   * @covers ::process()
   *
   * Test importing an item with a long author which should be truncated to 255
   * characters.
   */
  public function testProcessLongAuthor() {
    $feed = Feed::create([
      'title' => 'Processor test feed',
      'url' => 'https://example.com/rss.xml',
      'items' => [
        [
          'title' => 'Long author feed item title',
          'link' => 'http://example.com/long/author',
          'description' => 'Long author feed item description',
          'author' => 'I wanted to get out and walk eastward toward the park through the soft twilight, but each time I tried to go I became entangled in some wild, strident argument which pulled me back, as if with ropes, into my chair. Yet high over the city our line of yellow windows must have contributed their share of human secrecy to the casual watcher in the darkening streets, and I was him too, looking up and wondering. I was within and without, simultaneously enchanted and repelled by the inexhaustible variety of life.',
        ] + self::AGGREGATOR_PROCESSOR_TEST_ITEM_DEFAULTS,
      ],
    ]);
    $feed->save();

    $this->processor->process($feed);

    $item = Item::load(1);
    $this->assertStringStartsWith('I wanted to get out and walk eastward toward', $item->getAuthor());
  }

}
