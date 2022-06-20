<?php

namespace Drupal\data_router\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\HtmlResponse;
use Drupal\data_router\Service\AccountService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Client;
/**
 * Class ImceAdminBrowserController.
 */
class LoginController extends ControllerBase {

  protected $account;

  protected $request;

  protected $client;

  const CONFIGS_PATH  = 'private://auth/config.json';

  public function __construct(AccountService $account,RequestStack $request,Client $client) {
    $this->account = $account;
    $this->request = $request->getCurrentRequest();
    $this->client  = $client;
  }
  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('data_router.account'),
      $container->get('request_stack'),
      $container->get('http_client')
    );
  }

  /**
   * Browser Page.
   *
   * @return string
   *   Return Hello string.
   */
  public function verify($token) {
    $name = $this->account->setToken($token)->checkToken();
     if(empty($name)){
       \Drupal::messenger()->addError('Invalid Link');
     }
     else{
       \Drupal::messenger()->addMessage('Welcome '.$name);
     }

     return new RedirectResponse('/');
  }

  public function google(){
    $query   = $this->request->query->all();
    $code    = $query['code'];
    $account = $this->account;
    
    $result = [];
    $token  = FALSE;

    // $client_id     = self::CLIENT_ID;
    // $client_secret = self::CLIENT_SECRET;
    // $redirect_uri  = self::REDIRECT_URL;
    // $scope         = self::SCOPE;
    $configs = file_get_contents(self::CONFIGS_PATH);
    $configs = json_decode($configs,TRUE);
    $uri           = $configs['ENDPOINT'];
    $client_id     = $configs['CLIENT_ID'];
    $client_secret = $configs['CLIENT_SECRET'];
    $redirect_uri  = $configs['REDIRECT_URL'];
    $scope         = $configs['SCOPE'];

    $data = [
      'code'          => $code,
      'client_id'     => $client_id,
      'client_secret' => $client_secret,
      'redirect_uri'  => $redirect_uri,
      'grant_type'    => 'authorization_code',     
    ];

    try {
      $result =  \Drupal::httpClient()->post($uri,[ // get access_token
        'form_params'=> $data,
        'verify'     => TRUE,
        'headers' => [
          'Content-type' => 'application/x-www-form-urlencoded',
        ],
      ]);
      
      $result  = $result->getBody()->getContents();
      $result  = json_decode($result,TRUE);
      $token   = $result['access_token']; 
    }
    catch(ClientException $e){
      \Drupal::messenger()->addError('Something went wrong . Please Try Again');
         return new RedirectResponse('/user/login');
    } 
    catch (Exception $e) {
       \Drupal::messenger()->addError('Something went wrong . Please Try Again');
         return new RedirectResponse('/user/login');
    }

    if(!empty($token)){
      try {
        $uri     = 'https://www.googleapis.com/oauth2/v3/userinfo?access_token='.$token;
        $result  = \Drupal::httpClient()->get($uri); // get profile info
        $result  = $result->getBody()->getContents();
        $this->log($result);
        $result  = json_decode($result,TRUE);
      }
      catch(ClientException $e){
        \Drupal::messenger()->addError('Something went wrong . Please Try Again');
         return new RedirectResponse('/user/login');
      } 
      catch (Exception $e) {
        \Drupal::messenger()->addError('Something went wrong . Please Try Again');
         return new RedirectResponse('/user/login');
      }
    }

    if(empty($result)){
         \Drupal::messenger()->addError('Something went wrong . Please Try Again');
         return new RedirectResponse('/user/login');
    }

    $email   = $result['email'] ? $result['email'] : 'r_'.strval(rand()).'_user@gmail.com';
    $name    = $result['name'] || $result['given_name'] ? $result['name'].'_'.$result['given_name'] : 'r_'.strval(rand()).'_user@gmail.com';
    $profile = $result['picture'];

    $user_result = $account->checkAccount($email);
    if(empty($user_result)){
      // $profile = system_retrieve_file($profile, 'public://google', FALSE, FILE_EXISTS_REPLACE);
      $user    = $account->registerDirect($name,$email);
      user_login_finalize($user);
      \Drupal::messenger()->addMessage('Success . Welcome '.$email);
      return new RedirectResponse('/');
    }

    user_login_finalize($user_result);
    \Drupal::messenger()->addMessage('Success . Welcome '.$email);
    return new RedirectResponse('/');

  }

  private function log($data){
     $file = fopen('private://google_log.txt','a');
    fwrite($file,date('y-m-d_h:i:s',time()).$data.PHP_EOL);
    fclose($file);
  }

}
