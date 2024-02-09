<?php

namespace Drupal\Tests\aggregator\Functional\Plugin\aggregator\fetcher;

use Drupal\aggregator\Entity\Feed;
use Drupal\Core\Url;
use Drupal\Tests\aggregator\Functional\AggregatorTestBase;

/**
 * @coversDefaultClass \Drupal\aggregator\Plugin\aggregator\fetcher\DefaultFetcher
 * @group aggregator
 */
class DefaultFetcherTest extends AggregatorTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Tests that a redirected feed is tracked to its target.
   */
  public function testRedirectFeed() {
    $test_cases = [
      '301' => [
        'route' => 'aggregator_test.feed',
        'parameters' => [],
      ],
      '302' => [
        'route' => 'aggregator_test.redirect',
        'parameters' => ['status_code' => 302],
      ],
      '307' => [
        'route' => 'aggregator_test.redirect',
        'parameters' => ['status_code' => 307],
      ],
      '308' => [
        'route' => 'aggregator_test.feed',
        'parameters' => [],
      ],
    ];

    foreach ($test_cases as $status_code => $expected_url_params) {
      $parameters = ['status_code' => $status_code];
      $redirect_url = Url::fromRoute('aggregator_test.redirect', $parameters)->setAbsolute()->toString();
      $feed = Feed::create([
        'url' => $redirect_url,
        'title' => $this->randomMachineName(),
      ]);
      $feed->save();
      $feed->refreshItems();

      // The feed URL should be updated in the case of a 301 or 308 status, but
      // not in the case of 302 or 307.
      $expected_url = Url::fromRoute(
        $expected_url_params['route'],
        $expected_url_params['parameters'],
        ['absolute' => TRUE]
      )->toString();
      $this->assertSame($expected_url, $feed->getUrl());
    }
  }

}
