<?php

namespace Drupal\data_router\Service;

use Drupal\Core\Entity\FloodService;
use PHPMailer\PHPMailer\PHPMailer;
use Drupal\Core\Entity\EntityTypeManager;

class MailService
{

  protected $entityTypeManager;

  protected $mail;

  protected $mailer;

  protected $message;

  protected $subject;

  const CONFIG_TYPE   = 'mail_config';

  const BUNDLE        = 'config';

  public function __construct(EntityTypeManager $entityTypeManager){
    $this->entityTypeManager = $entityTypeManager;
    $this->mailer = new PHPMailer(true);
  }

  public function setMail($mail){
    $this->mail = $mail;
    return $this;
  }

  public function setSubject($subject){
    $this->subject = $subject;
    return $this;
  }

  public function setMessage($message){
    $this->message = $message;
    return $this;
  }

  public function send(){
    $entity = $this->entityTypeManager;
    $config = $entity->getStorage('node')->loadByProperties([
      'type'              => self::BUNDLE,
      'field_config_name' => self::CONFIG_TYPE
    ]);
    if(empty($config)){
      return FALSE;
    }

    $mail_config = array_values($config)[0]->toArray();
    $mail_config = $mail_config['field_data'][0]['value'];
    $mail_config = json_decode($mail_config,TRUE);
    // return $mail_config;
    // $mail_config = self::getJsonFromFile(self::config()['path']['files']['configs']['mail']);
    // $id          = $array['id'];
    // $mail_config = $mail_config[$id];
    $protocol    = $mail_config['protocol'];
    $host        = $mail_config['host'];
    $port        = $mail_config['port'];
    $email_from  = $mail_config['email_from'];
    $username    = $mail_config['username'];
    $password    = $mail_config['password'];
    $from_name   = $mail_config['from_name'];
    $bcc         = $mail_config['bcc'];
    // $subject     = $mail_config['subject'];

    $email        = $this->mail;
    $subject      = $this->subject;
    $body         = $this->message;

    if(empty($email)){
      return FALSE;
    }

    $mailer = $this->mailer;
    $mailer->IsSMTP();
    $mailer->SMTPSecure = $protocol; // secure transfer enabled REQUIRED for Gmail
    $mailer->SMTPAuth = TRUE;
    $mailer->AuthType = 'LOGIN';
    $mailer->Host = $host;
    $mailer->Port = $port; // or 465
    $mailer->IsHTML(true);
    $mailer->Username = $username;
    $mailer->Password = $password;
    // $mailer->addReplyTo($email_from,$from_name);
    $mailer->setFrom($email_from,$from_name);
    $mailer->Subject = $subject;
    $mailer->AddAddress($email);
    $bcc ?  $mailer->addBCC($bcc): '';
    // $mailer->CharSet       = 'ISO-2022-JP';
    // $mailer->Encoding      = "7bit";
    // $mailer->Body = mb_convert_encoding($body, "ISO-2022-JP-MS", "UTF-8");
    $mailer->Body = $body;
    // $mailer->SMTPDebug = 12;
    $mailer->SMTPOptions = [
        'ssl' => [
            'verify_peer'  => false,
            'verify_peer_name'  => false,
            'allow_self_signed' => true,
          ]
        ];
      // return $mailer;
    try {
      return $mailer->send();
    } catch (\Exception $e) {
      return $e->errorMessage();
    }
  }

}
