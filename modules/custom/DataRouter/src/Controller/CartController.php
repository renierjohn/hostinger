<?php

namespace Drupal\data_router\Controller;

use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Cache\Cache;
/**
 * Class ImceAdminBrowserController.
 */
class CartController extends ControllerBase {

  public function render(Request $request){
    $cookie = $request->cookies->get('cart');
    $cookie = json_decode($cookie,TRUE);
    $data   = [];
    if(!empty($cookie)){
      foreach ($cookie as $value) {
        if(!empty($value['pid'])){
         $node  = \Drupal::service('entity_type.manager')->getStorage('node')->load($value['pid']);
         $data[] = $this->getData($node,$value['qty']);
        }
      }
    }

    $placeholder = 'cart_placeholder';
  	$build  = [
  		'#theme' => 'cart',
      '#cache' => [
        'contexts' => ['cookies:cart'],
        'tags' => ['cart'],
      ],
    	'#attached' => [
          'library' => [
            'renify/products',  
            'core/drupal',
          ],
      ],
      '#data' => $data,

    ];
  	return $build;
  }

  public function test_lazy_buider(){
    return time();
  }

  private function getData($node,$qty){
    $img = $node->field_banner_image->entity->getFileUri();
    $img = str_replace('public://','/sites/default/files/public/',$img);
    return [
      'id'    => $node->id(),
      'title' => $node->getTitle(),
      'image' => $img,
      'price' => $node->field_product_price->value,
      'link'  => $node->path->first()->getValue()['alias'],
      'qty'   => $qty,
    ];
  }

}
