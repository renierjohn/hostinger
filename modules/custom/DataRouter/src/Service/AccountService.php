<?php

namespace Drupal\data_router\Service;

use GuzzleHttp\Client;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\data_router\Service\MailService;

class AccountService
{
  protected $email;

  protected $token;

  protected $message;

  protected $entityTypeManager;

  protected $mailer;

  const DEFAULT_PASS  = 'abcd1234';

  const PATTERN_LINK = '{{link}}';

  const PATTERN_MAIL  = '{{mail}}';

  const PATTERN_NAME  = '{{name}}';

  const CONFIGS_PATH  = 'private://auth/config.json';

  public function __construct(EntityTypeManager $entityTypeManager,MailService $mailer){
    $this->entityTypeManager = $entityTypeManager;
    $this->mailer            = $mailer;
  }

  public function setEmail($email){
    $this->email = $email;
    return $this;
  }

  public function setMessage($message){
    $this->message = $message;
    return $this;
  }

  public function setToken($token){
    $this->token = $token;
    return $this;
  }

  public function getCapchaSiteKey(){
     $configs = file_get_contents(self::CONFIGS_PATH);
     $configs = json_decode($configs,TRUE);
     return $configs['SITE_KEY'];
  }

  public function getCapchaSecretKey(){
     $configs = file_get_contents(self::CONFIGS_PATH);
     $configs = json_decode($configs,TRUE);
     return $configs['SECRET_KEY'];
  }

  public function checkCaptcha(){
    
    $configs = file_get_contents(self::CONFIGS_PATH);
    $configs = json_decode($configs,TRUE);

    $token       = $this->token;
    $secret_key  = $configs['SECRET_KEY'];
    $response    = \Drupal::httpClient()->post('https://www.google.com/recaptcha/api/siteverify?secret='.$secret_key.'&response='.$token,[]);
    $data        = $response->getBody()->getContents();
    $data        = json_decode($data,TRUE);
    if($data['success'] == true){
      return TRUE;
    }
    return FALSE;
  }

  public function register(){
    $entityTypeManager = $this->entityTypeManager;
    $email             = $this->email;
    $mailer            = $this->mailer;
    $existing_email    = $entityTypeManager->getStorage('user')->loadByProperties(['mail' => $email]);

    if(!empty($existing_email)){
      return [
        'status'  => FALSE,
        'message' => 'Sorry ,Email Already Exists',
      ];
    }
    $token = md5(mt_rand(0, 1000) . time());
    $tokn = 'https://www.renify.store/verify/'.$token;
    $pass  = self::DEFAULT_PASS;
    $name  = explode('@',$email)[0];

    $config   = $this->getConfig('signup_data');
    $subject  = $config['subject'];
    $body     = $config['body'];
    $body     = str_replace(self::PATTERN_NAME,$name,$body);
    $body     = str_replace(self::PATTERN_LINK,$tokn,$body);

    $mailer->setMail($email)->setSubject($subject)->setMessage($body)->send();

    $fields   = [
      'field_token' => $token,
      'name'        => $name,
      'mail'        => $email,
      'pass'        => $pass,
    ];
    $user   = $entityTypeManager->getStorage('user')->create($fields)->save();
    return [
      'status'  => TRUE,
      'email' => $email,
    ];
  }

  public function store_message(){
    $entityTypeManager = $this->entityTypeManager;
    $email             = $this->email;
    $mailer            = $this->mailer;
    $message           = $this->message;

    if(empty($email)){
        $email = \Drupal::currentUser()->getEmail();
    }

    if(!empty($email)){
      $config   = $this->getConfig('mail_data');
      $subject  = $config['subject'];
      $body     = $config['body'];
      $body     = str_replace(self::PATTERN_MAIL,$email,$body);

      $mailer->setMail($email)->setSubject($subject)->setMessage($body)->send();
    }

    if(empty($email)){
        $email = \Drupal::currentUser()->id();
    }

    $fields   = [
      'title'       => $email,
      'type'        => 'messages',
      'field_email' => $email,
      'body'        => $message,
    ];

    return $entityTypeManager->getStorage('node')->create($fields)->save();
  }

  private function getAvatar($email){

  }

  private function replaceToken($message,$token){
    $message = str_replace('{{token}}',$token,$str);
    return $message;
  }

  public function getConfig($config_id){
    $entityTypeManager = $this->entityTypeManager;
    $config = $entityTypeManager->getStorage('node')
      ->loadByProperties([
        'type'              => 'config',
        'field_config_name' => $config_id,
      ]);
    $config   = array_values($config)[0]->toArray();
    $subject  = $config['field_subject'] ? $config['field_subject'][0]['value'] : FALSE;
    $body     = $config['field_message'] ? $config['field_message'][0]['value'] : FALSE;
    $data     = $config['field_data']    ? $config['field_data'][0]['value'] : FALSE;
    $data     = json_decode($data,TRUE);
    return [
      'subject' => $subject,
      'body'    => $body,
      'data'    => $data
    ];
  }

  public function checkToken(){
    $token  = $this->token;
    $entity = $this->entityTypeManager;
    if(empty($token)){
      return FALSE;
    }
    $user = $entity->getStorage('user')->loadByProperties(['field_token' => $token , 'status' => 0]);
    if(empty($user)){
      return FALSE;
    }
    $user  = reset($user);
    $name  = $user->name ? $user->name->getValue()[0]['value'] : 'user';
    $fname = $name[0];
    $uri   = 'public://profile/'.$fname.'.png';

    $fid   = $entity->getStorage('file')->getQuery()->condition('uri',$uri)->execute();
    if(empty($fid)){
      $entity->getStorage('file')->create(['uri'=>$uri])->save();
      $fid   = $entity->getStorage('file')->getQuery()->condition('uri',$uri)->execute();
    }
    $fid   = reset($fid);
    $user->activate()->set('user_picture',$fid)->save();
    user_login_finalize($user);
    return $name;
  }

  public function checkAccount($email){
    $entity = $this->entityTypeManager;
    $user   = $entity->getStorage('user')->loadByProperties(['mail'=>$email]);
    if(empty($user)){
      return FALSE;
    }
    return reset($user);
  }

  public function registerDirect($name,$email){
    $entity = $this->entityTypeManager;
    $pass   = self::DEFAULT_PASS;
    
    $profile =  'public://profile/'.$email[0].'.png';
    
    $file = $entity->getStorage('file');
    $fid  = $file->loadByProperties(['uri' => $profile]);
    $fid  = reset($fid);
    if(!empty($fid)){
      $fid = $fid->id();
    }
    if(empty($fid)){
      $fid =  $file->create(['uri'=>$profile])->save();
      $fid =  $file->loadByProperties(['uri' => $profile]);
      $fid = reset($fid);
    }


    $fields   = [
      'name'         => $name,
      'mail'         => $email,
      'pass'         => $pass,
      'user_picture' => $fid,
      'status'       => TRUE,
    ];
   $entity->getStorage('user')->create($fields)->save();
   $user = $entity->getStorage('user')->loadByProperties(['mail'=>$email]);
   return reset($user);
  }

}
