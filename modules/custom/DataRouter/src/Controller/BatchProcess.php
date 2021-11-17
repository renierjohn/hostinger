<?php

namespace Drupal\data_router\Controller;

use Drupal\Core\State\State;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
/**
 * Class ImceAdminBrowserController.
 */
class BatchProcess extends ControllerBase {

  public function run(){
//   	$batch = array(
//   'title' => t('Exporting'),
//   'operations' => array(
//     array(
//       'my_function_1',
//       array(
//         $account->uid,
//         'story',
//       ),
//     ),
//     array(
//       'my_function_2',
//       array(),
//     ),
//   ),
//   'finished' => 'my_finished_callback',
//   'file' => 'path_to_file_containing_myfunctions',
// );
	$batch = [];
  	$batch = [
  		'title' => 'test',
  		'operations' => [
  			['function1',['1','renier']],
  			['function2',['2','john']]
  		],
  		'finished' => 'finished',
  		'file'	  => 'public://functions.txt',
  	];

  	batch_set($batch);
  	\Drupal::logger('BATCH')->notice('set');
  	return batch_process($batch);
return new JsonResponse(['done']);
  }

  public function function1($id,$name,&$context){
  	\Drupal::logger('BATCH F1')->notice('function1'.$id.$name);
  	$file = fopen('public://f1.txt','a');
  	fwrite($file,'id:'.$id.' name:'.$name);
  	fclose($file);
  }

  public function function2($id,$name,&$context){
  	\Drupal::logger('BATCH F2')->notice('function2'.$id.$name);
  	$file =fopen('public://f2.txt','a');
  	fwrite($file,'id:'.$id.' name:'.$name);
  	fclose($file);
  }

  public function process($params,&$context){
  	\Drupal::logger('BATCH ')->notice('process');
  }

  public function finished(){
  	\Drupal::logger('BATCH')->notice('done');
  	\Drupal::messenger()->addMessage('Done');
  }

}
