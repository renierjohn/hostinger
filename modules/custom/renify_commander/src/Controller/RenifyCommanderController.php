<?php

namespace Drupal\renify_commander\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Cache\CacheableJsonResponse;
use Drupal\Core\Cache\CacheableMetadata;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
/**
 * Returns responses for Renify Commander routes.
 */
class RenifyCommanderController extends ControllerBase {

 /**
   * Run Drush.
   */
  public function run(Request $request) {
    $result = False;
    $query = $request->query->all();
    if (!$this->hasAccess($request)) {
      return new JsonResponse(['status' => False, 'message' => 'wrong key','key' => $query['key']]);
    }
    $command = !empty($query['mode']) ? $query['mode'] : False;
    if (empty($command)) {
      return new JsonResponse(['status' => False, 'message' => 'wrong command','mode' => $query['mode']]);
    }
    $t1 = time();

    if ($command === 'cache') {
      \Drupal::service('cache.render')->invalidateAll();
      \Drupal::service('cache.data')->invalidateAll();
      \Drupal::service('cache.bootstrap')->invalidateAll();
      \Drupal::service('cache.config')->invalidateAll();
      \Drupal::service('cache.default')->invalidateAll();
      \Drupal::service('cache.entity')->invalidateAll();
      \Drupal::service('cache.menu')->invalidateAll();
    }

    if ($command === 'composer') {
     $result =  shell_exec('composer install');
    }

    $t2 = time();
    return new JsonResponse(['status' => True, 'result' => $result, 'message' => 'key matched','time' => $t2, 'duration' => $t2 - $t1]);
  }

 /**
   * Run cron.
   */
  public function cron(Request $request) {
    if (!$this->hasAccess($request)) {
      return new JsonResponse(['status' => False, 'message' => 'wrong key','key' => $request->query->all()['key']]);
    }
    $t1 = time();
    \Drupal::service('cron')->run();
    $t2 = time();
    return new JsonResponse(['status' => True, 'message' => 'key matched','time' => $t2, 'duration' => $t2 - $t1]);
    // $metadata = new CacheableMetadata();
    // $metadata->setCacheMaxAge(0);
    // $jsonResponse = new CacheableJsonResponse(['status' => 'cron success', 'ts' => time()]);
    // $jsonResponse->addCacheableDependency($metadata);
    // return $jsonResponse;
  }

  protected function hasAccess(Request $request) {
    $query = $request->query->all();
    $pub_key = $query['key'];
    $md5_key = \Drupal::service('config.factory')->getEditable('renify_commander.settings')->get('md5_key');
    if(md5($pub_key) === $md5_key) {
      return True;
    }
    return False;
  }

}
