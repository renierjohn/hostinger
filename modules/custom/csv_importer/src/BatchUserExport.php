<?php

namespace Drupal\csv_importer;
use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Term;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Drupal\Core\File\FileSystemInterface;
/**
 * Provides a base class for ImporterBase plugins.
 *
 * @see \Drupal\csv_importer\Annotation\Importer
 * @see \Drupal\csv_importer\Plugin\ImporterManager
 * @see \Drupal\csv_importer\Plugin\ImporterInterface
 * @see plugin_api
 */

class BatchUserExport {

    protected $limit;

    public function __construct(){
      $this->limit = 5;
    }

    public function runExport($uids,$uri,&$context) {
       $contents   = $this->getcontents($uids,$uri);
       
       $csv_header = fopen($uri,'r');
       $header = fread($csv_header,10);
       fclose($csv_header);

       $csv_file = fopen($uri,'a');

       if(empty($header) && !empty($contents[0])){
       	 fputcsv($csv_file,array_keys($contents[0]),';');
       }

        if (empty($context['sandbox'])) {
          $context['sandbox'] = [];
          $context['sandbox']['progress'] = 1;
          $context['sandbox']['current_node'] = 0;
          $context['sandbox']['max'] = count($contents);
          $context['finished'] = FALSE;
        }

        foreach ($contents as $row => $content) {
          $context['sandbox']['progress']++;
          $context['sandbox']['current_node'] = $row;
          $context['message'] = t('Generating CSV File Contents . Row number @row / @total , limit : @limit', [
            '@row'    => $row,
            '@limit'  => $this->limit,
            '@total'  => count($contents),
          ]);
          fputcsv($csv_file,$content,';');
        }

      fclose($csv_file);
      if ($context['sandbox']['progress'] != $context['sandbox']['max']) {
        $context['finished'] = $context['sandbox']['progress'] > $context['sandbox']['max'];
      }
    }

  function batch_finished($success, $results, $operations) {
    \Drupal::messenger()->addMessage(t('Done Process'));
  }

  public function execute(){
    $limit   = $this->limit;
    $start   = 0;
    $process = [];
    $uri     = 'private://users.csv';

    $csv_file = fopen($uri,'w+'); // init file
    fclose($csv_file);

    while (true) {
      $uids = $this->getUids($start,$limit);
      if(empty($uids)){
      	break;
      }
      $process['operations'][] = [
        [$this,'runExport'],
        [$uids,$uri]
      ];
      $start = $start + $limit;
    }
    
    if(!empty($process['operations'])){
      $process['finished'] = [$this, 'batch_finished'];
      batch_set($process);
    }
    else{
      \Drupal::messenger()->addError('No Contents');
    }
  }

  private function getcontents($ids){
  	$users    = \Drupal::service('entity_type.manager')->getStorage('user')->loadMultiple($ids);
  	$contents = [];
  	foreach ($users as $user) {
      if($user->roles->target_id != 'student'){
        continue;
      }

  	  $fields = [
  	  	'uid'  => $user->id(),
  	  	'name' => $user->name->value,
  	  	'mail' => $user->mail->value,
  	  	'field_gender' => $user->field_gender->value,
  	  	'field_level'  => $user->field_level->value,
  	  	'user_picture' => !empty($user->user_picture->entity) ? $user->user_picture->entity->getFileUri() : '',
        'qr_code'      => $user->field_qr->value,
  	  	'roles'        => $user->roles->target_id,
  	  ];
  	  $contents[] = $fields;
  	}
  	return $contents;
  }

  private function getUids($start,$limit){
  	$query = \Drupal::service('entity_type.manager')->getStorage('user')->getQuery();
  	// $query->condition('roles','student');
  	$query->range($start,$limit);
  	$uids = $query->execute();
  	return $uids;
  }

  private function getPictureID($fid){
    return \Drupal::service('entity_type.manager')->getStorage('file')->load($fid)->getFileUri();
   }

}
