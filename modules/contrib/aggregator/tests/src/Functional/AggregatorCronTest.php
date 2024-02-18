<?php

namespace Drupal\Tests\aggregator\Functional;

use Drupal\aggregator\Entity\Feed;
use Drupal\Component\Render\FormattableMarkup;
use Drupal\Tests\Traits\Core\CronRunTrait;

/**
 * Update feeds on cron.
 *
 * @group aggregator
 */
class AggregatorCronTest extends AggregatorTestBase {

  use CronRunTrait;

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Adds feeds and updates them via cron process.
   */
  public function testCron() {
    // Create feed and test basic updating on cron.
    $this->createSampleNodes();
    $feed = $this->createFeed();
    $count_query = \Drupal::entityQuery('aggregator_item')
      ->accessCheck(FALSE)
      ->condition('fid', $feed->id())
      ->count();

    $this->cronRun();
    $this->assertEquals(5, $count_query->execute());
    $this->deleteFeedItems($feed);
    $this->assertEquals(0, $count_query->execute());
    $this->cronRun();
    $this->assertEquals(5, $count_query->execute());

    // Test feed locking when queued for update.
    $this->deleteFeedItems($feed);
    $feed->setQueuedTime(REQUEST_TIME)->save();
    $this->cronRun();
    $this->assertEquals(0, $count_query->execute());
    $feed->setQueuedTime(0)->save();
    $this->cronRun();
    $this->assertEquals(5, $count_query->execute());

    // Test that edited and deleted feeds are not restored from queued objects
    $queue = \Drupal::queue('aggregator_feeds');
    $queue->createItem($feed->id());
    // Overwrite the existing feed values.
    $edit = $this->getFeedEditArray();
    $this->drupalGet('aggregator/sources/' . $feed->id() . '/configure');
    $this->submitForm($edit, 'Save');
    $this->assertSession()->pageTextContains(new FormattableMarkup('The feed @name has been updated.', ['@name' => $edit['title[0][value]']]));
    // Reload the feed with the new values.
    \Drupal::entityTypeManager()->getStorage('aggregator_feed')->loadUnchanged($feed->id());
    $feed = Feed::load($feed->id());
    // Verify the feed still has the new title after cron runs.
    $this->cronRun();
    $this->drupalGet('aggregator/sources/' . $feed->id());
    $this->assertSession()->responseContains($edit['title[0][value]']);
    // Verify that queued feeds are not restored after being deleted and that
    // cron has run the queue successfully.
    $queue->createItem($feed->id());
    $this->deleteFeed($feed);
    $database = \Drupal::database();
    $this->assertCount(0, Feed::loadMultiple(), 'There are no feeds in the database.');
    $this->assertEquals(1, $queue->numberOfItems(), 'There is one queued feed in the database.');
    $this->cronRun();
    $this->assertCount(0, Feed::loadMultiple(), 'There are still no feeds in the database.');
    $this->assertEquals(0, $queue->numberOfItems(), 'There are no queued feeds in the database.');
  }

}
