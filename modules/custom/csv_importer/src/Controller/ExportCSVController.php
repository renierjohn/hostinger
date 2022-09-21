<?php

namespace Drupal\csv_importer\Controller;

use Drupal\Core\Url;
use Drupal\Core\Language\LanguageManager;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Drupal\Component\Utility\Xss;

use Drupal\csv_importer\BatchExport;
use Drupal\csv_importer\Service\EntityService;
/**
 * Redirect Page Controller
 */
class ExportCSVController extends ControllerBase {

   protected $request;

   protected $langcode;

   protected $entity;

   const COOKIE_FILENAME = 'product_filename';

   const COOKIE_NID   = 'product_compare';

   const COOKIE_FIELD = 'product_columns';

   const COOKIE_CSV   = 'csv_columns';

   public function __construct(RequestStack $request ,LanguageManager $language,EntityService $entity) {
       $this->entity    = $entity;
       $this->request   = $request->getCurrentRequest();
       $this->langcode  = $language->getCurrentLanguage()->getId();
  }
 /**
  * {@inheritdoc}
  */
 public static function create(ContainerInterface $container) {
   return new static(
     $container->get('request_stack'),
     $container->get('language_manager'),
     $container->get('csv_importer.entity')
   );
 }

 public function compare(){
    $entity   = $this->entity;
    $langcode = $this->langcode;
    $cookies  = $this->request->cookies->all();    

    if(empty($cookies)){
      // return new RedirectResponse('/',301);
    }

    $nids  = !empty($cookies[self::COOKIE_NID])   ? explode(',',$cookies[self::COOKIE_NID])   : FALSE;
    $items = !empty($cookies[self::COOKIE_FIELD]) ? explode(',',$cookies[self::COOKIE_FIELD]) : FALSE;  

    // $nids  = Xss::filter($nids);
    // $items = Xss::filter($items);
    
    $nodes = $entity->nids($nids)->setItems($items)->langcode($langcode)->loadAllCompare();
    if(empty($nodes)){
        $nodes = [];
        // return new RedirectResponse('/',301);
    }

    // $filename = Xss::filter($cookies[self::COOKIE_FILENAME]);
    
    $filename = $cookies[self::COOKIE_FILENAME];

    if(empty($filename)){
      $filename = 'KYOCERA';
    }

    $uri = $this->processCsvFile($nodes,$filename);
    return $this->downloadFile($uri);
 }

  
  private function getFilename($nids){
    if(empty($nids)){
      return FALSE;
    }
    $nid = reset($nids);
    $a=1;

  }

  private function downloadFile($outputFilepath) {
    $headers = [];
    $headers = [
      'Content-Type' => 'text/csv',
      'Content-Description' => 'File Download',
      'Content-Disposition' => 'attachment; filename='.basename($outputFilepath)
    ];
    return new BinaryFileResponse($outputFilepath,200,$headers,true);
  }

  // private function downloadFilePOST($outputFilepath) {
  //   $headers = [];
  //   $headers = [
  //     'Content-Type' => 'text/csv',
  //     'Content-Disposition' => 'attachment; filename='.basename($outputFilepath)
  //   ];
  //   $binary = new BinaryFileResponse($outputFilepath,200,$headers,true);
  //   return $binary->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT)->sendContent();
  // }

  private function processCsvFile(array $nodes,$filename){
    $data     = '1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZabcefghijklmnopqrstuvwxyz';
    $hash     = substr(str_shuffle($data),0,5 );
    $filename = str_replace('/','_',$filename);
    $csv_file = 'public://tmp/'.$filename.' '.$hash.'.csv';
    // $csv_file = sys_get_temp_dir().'/'.$filename.' '.$hash.'.csv';
    $csv_file = fopen($csv_file,'w');
    $uri      = stream_get_meta_data($csv_file)['uri'];
    fwrite($csv_file, implode(',',array_keys($nodes[0])).PHP_EOL);
    // fputcsv($csv_file,array_keys($nodes[0]),',');
    foreach ($nodes as $node) {
      // fputcsv($csv_file,$node,',');
      fwrite($csv_file,implode(',',$node).PHP_EOL);
    }
    
    fclose($csv_file);
    return $uri;
  }
}
