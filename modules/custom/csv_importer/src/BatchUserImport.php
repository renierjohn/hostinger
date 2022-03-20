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

class BatchUserImport {

    protected $limit;

    protected $fid;

    protected $status;

    public function __construct($fid){
      $this->limit = 100;
      $this->fid    = $fid;
    }

  public function execute(){
    $entity  = \Drupal::service('csv_importer.entity');
    $index   = 0;
    $limit   = $this->limit;
    $fid     = $this->fid;
    $process = [];

    $csv = $this->getCsvSummary($fid);
    if(empty($csv)){
      \Drupal::messenger()->addError('Empty File');
      return; 
    }
    $total_items = $csv['total_items'];
    $header      = $csv['header'];
    $uri         = $csv['uri'];

    $items_per_page = $this->getItemsPerPage($total_items);
    $pages          = $total_items / $items_per_page;
    $pages          = floor($pages);
    $iterators      = range(0,$pages);     

    foreach($iterators as $iterator){
      $start = $iterator * $items_per_page + 1;
      $end   = ($iterator + 1) * $items_per_page;

      if($total_items < $end ){
            $end = $total_items;
      }
      
      $process['operations'][] = [
        [$this,'runImport'],
        [$start,$end,$uri]
      ];
    }
    
    if(!empty($process['operations'])){
      $process['finished'] = [$this, 'batch_finished'];
      batch_set($process);
    }
    else{
      \Drupal::messenger()->addError('No Contents');
    }
  }

    public function runImport($start,$end,$uri,&$context) {
        $contents = $this->getcontent($start,$end,$uri);
        if (empty($context['sandbox'])) {
          $context['sandbox'] = [];
          $context['sandbox']['progress'] = 1;
          $context['sandbox']['current_node'] = 0;
          $context['sandbox']['max'] = count($contents);
          $context['finished'] = FALSE;
        }

      foreach ($contents as $row => $content) {
          // $context['results'][] = 'row # '.($row);
          $context['sandbox']['progress']++;
          $context['sandbox']['current_node'] = $row;
          $context['message'] = t('Generating CSV File Contents . Row number @row / @total , limit : @limit', [
            '@row'    => $row,
            '@limit'  => $this->limit,
            '@total'  => count($contents),
          ]);

          if(!empty($content['delete'])){
            if($content['delete'] == 1){
              $this->deleteUser($content,$context);
              continue;
            }
          }

          if(empty($content['uid'])){
            $this->addUser($content,$context);
          }
          else{
            $this->updateUser($content,$context);
          }
      }

      if ($context['sandbox']['progress'] != $context['sandbox']['max']) {
        $context['finished'] = $context['sandbox']['progress'] > $context['sandbox']['max'];
      }
    }

  function batch_finished($success, $results, $operations) {
    
    $add    = !empty($results['count_add'])    ? count($results['count_add'])    : 0;
    $update = !empty($results['count_update']) ? count($results['count_update']) : 0;
    $delete = !empty($results['count_delete']) ? count($results['count_delete']) : 0;
    $skip   = !empty($results['count_skip'])   ? count($results['count_skip'])   : 0;
    $total  = $add + $update + $delete + $skip;
    $message = t('Success User Imported | ADD : @add , UPDATE : @update , DELETE : @delete , SKIP : @skip , TOTAL : @total',[
      '@add' => $add,
      '@update' => $update,
      '@delete' => $delete,
      '@skip'   => $skip,
      '@total'  => $total
    ]);

    \Drupal::messenger()->addMessage($message);
    $this->logSummary($message);
  }

  private function getCsvSummary($fid){
      $file = \Drupal::service('entity_type.manager')->getStorage('file')->load($fid);
      if(empty($file)){
        return FALSE;
      }
      $uri = $file->getFileUri();
      $csv = fopen($uri,'r');
      $row = 0;
      $header = fgetcsv($csv,10000,',');
      while(fgetcsv($csv,10000,',')) {
        $row++;
      }
      fclose($csv);
      return [
               'total_items' => $row,
               'header'      => $header ,
               'uri'         => $uri
             ];
  }

  private function getItemsPerPage($total_items){
     $items_per_page = 500;

     if($total_items > 500 && $total_items < 1000 ){
       $items_per_page = 100;      
     }

     if($total_items > 100 && $total_items < 500){
       $items_per_page = 50;      
     }

     if($total_items > 10 && $total_items < 100){
       $items_per_page = 10;      
     }

     if($total_items > 0 && $total_items < 10){
       $items_per_page = 1;      
     }
     return $items_per_page;
  }

  private function getcontent($start,$end,$uri){
    $tmp_header = [];
    $contents = [];
    $row      = 1;
    $csv      = fopen($uri,'r');
     $header   = fgetcsv($csv,10000,';');
      
    foreach ($header as $key => $value) {
          $tmp_header[] = explode('|',$value)[0];  
    }
    $header = $tmp_header;
    
    while ($data = fgetcsv($csv,10000,';')) {
      if($row >= $start && $row <= $end ){
        $contents[] = array_combine($header,$data);
      }
      if($row > $end){
        break;
      }
      $row++;
    }

    fclose($csv);
    return $contents;
  }


  private function addUser($content,&$context){
    $mail    = !empty($content['mail'])         ? $content['mail']         : '';
    $level   = !empty($content['field_level'])  ? $content['field_level']  : 0;
    $picture = !empty($content['user_picture']) ? $content['user_picture'] : 'public://favicon.png';
    $gender  = !empty($content['field_gender']) ? $content['field_gender'] : '';
    $name    = $content['name'];

    if(!$this->checkUserExists($name)){
      $context['results']['count_skip'][] = TRUE;
      return FALSE;
    }

    $qr = \Drupal::service('data_router.student')->generateHash($name);
    $user = \Drupal::service('entity_type.manager')->getStorage('user')->create([
      'pass'  => 'renify1234',
      'roles' => ['student'],
      'name'  => $name,
      'mail'  => $mail,
      'status'       => 1,
      'field_qr'     => $qr,
      'field_gender' => $gender,
      'field_level'  => $level,
      'user_picture' => $this->getPictureID($picture),
    ])->save();
    $context['results']['count_add'][] = TRUE;
  }

  private function checkUserExists($name){
    $user = \Drupal::service('entity_type.manager')->getStorage('user')->getQuery()->condition('name',$name)->execute();
    if(empty($user)){
      return TRUE;
    }
    return FALSE;
  }

  private function updateUser($content,&$context){
    $user = \Drupal::service('entity_type.manager')->getStorage('user')->load($content['uid']);
    if(empty($user)){
      $context['results']['count_skip'][] = TRUE;   
      return;
    }

    if(!empty($content['name'])){
        $user->set('name',$content['name']);
    }

    if(!empty($content['mail'])){
        $user->setEMail($content['mail']);
    }

    if(!empty($content['field_level'])){
        $user->set('field_level',$content['field_level']);
    }

    if(!empty($content['user_picture'])){
        $picture = $this->getPictureID($content['user_picture']);
        $user->set('user_picture',$picture);
    }

    if(!empty($content['field_gender'])){
        $user->set('field_gender',$content['field_gender']);
    }
    $user->save();
    $context['results']['count_update'][] = TRUE;   
  }

  private function deleteUser($content,&$context){
    $user = \Drupal::service('entity_type.manager')->getStorage('user')->load($content['uid']);
    if(empty($user)){
      $context['results']['count_skip'][] = TRUE;   
      return;
    }
    $user->delete();
    $context['results']['count_delete'][] = TRUE;   
  }

  private function getPictureID($uri){
    $file = \Drupal::service('entity_type.manager')->getStorage('file');
    $fid  = $file->getQuery()->condition('uri',$uri)->execute();

    if(!empty($fid)){
     return reset($fid);
    }
    $file = \Drupal::service('entity_type.manager')->getStorage('file')->create(['uri'=>$uri])->save();
    $fid =  \Drupal::service('entity_type.manager')->getStorage('file')->getQuery()->condition('uri',$uri)->execute();
    return $fid;
  }

  public function logSummary($logs){
       $date = date("F d,Y h:i:s A");
       $file = fopen('private://csv_user_import.txt','a');
       fwrite($file,$date.' : '.$logs.PHP_EOL);
       fclose($file); 
  }

}
