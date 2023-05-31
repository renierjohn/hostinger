<?php

namespace Drupal\data_router\Service;

use Drupal\Core\Entity\EntityTypeManager;
use Drupal\data_router\Service\MailService;

class BookingService
{

  protected $entityTypeManager;

  protected $mail;

  const FEE = 100;

  public function __construct(EntityTypeManager $entityTypeManager,MailService $mail){
    $this->entityTypeManager = $entityTypeManager;
    $this->mail = $mail;
  }

  public function setHash($hash){
  	$this->hash = $hash;
  	return $this;
  }

  /**
   * BookingController
   * */ 
  public function getBookingTemplate(){
  	$hash = $this->hash;

  	if(!file_exists('private://book/'.$hash)){
  		return FALSE;
    }
  	
    $file = fopen('private://book/'.$hash,'r');
  	
    if(empty($file)){
    	return FALSE;
  	}

		$data = fread($file,10000);
  	fclose($file);

	  $data = json_decode($data,TRUE);

	  $this->formatBookingMessage($data);
    
    return [
    	'template' => $this->template,
    	'data'		 => $data
    ];
  }

  public function formatBookingMessage($data){
  	$filename      = 'private://book/000ticket.html';
	  $file_template = fopen($filename,'r');
	  $template      = fread($file_template,filesize($filename));
	  fclose($file_template);

	  $status           = 'SUCCESS';
	  $status_bg_color  = 'green';
	  $status_txt_color = 'white';
  	$link_tag         = '<a href="https://renifysite.com/sites/default/files/public/etickets/'.$data['hash'].'.pdf" title="link" target="_blank">Download Youre Ticket Here</a>';

    if($data['pending']){
      $status           = 'PENDING';
      $status_bg_color  = '#ffcc00';
      $status_txt_color = 'black';
      $link_tag         = '<a href="https://renifysite.com/book/'.$data['hash'].'" title="link" target="_blank">Click Here to Check Status</a>';
    }


    $template = str_replace('{{fee}}',self::FEE,$template);
    $template = str_replace('{{firstname}}',$data['name'],$template);
    $template = str_replace('{{lastname}}',$data['lastname'],$template);
    $template = str_replace('{{hash}}',$data['hash'],$template);
    $template = str_replace('{{price}}',$data['price'],$template);
    $template = str_replace('{{vessel}}',$data['vessel'],$template);
    $template = str_replace('{{accomodation}}',$data['accomodation'],$template);
    $template = str_replace('{{origin}}',$data['origin'],$template);
    $template = str_replace('{{destination}}',$data['destination'],$template);
    $template = str_replace('{{remarks}}',$data['remarks'],$template);
    $template = str_replace('{{total_price}}',number_format($data['price'] + self::FEE,2),$template);
    $template = str_replace('{{status}}',$status,$template);
    $template = str_replace('{{status_bg_color}}',$status_bg_color,$template);
    $template = str_replace('{{status_txt_color}}',$status_txt_color,$template);
    $template = str_replace('{{link}}','https://renifysite.com/book/'.$data['hash'],$template);

    $template = str_replace('{{link_tag}}',$link_tag,$template);
    
    $this->template = $template;
    $this->email    = $data['mail'];
    
    return $this;
  }

  /**
   * BookingForm BookingConfigForm
   * */ 
  public function sendMailManual(){
  	$this->mail->setMail($this->email)
      ->setSubject('Payment Confirmation')
      ->setMessage($this->template)
      ->send();
  }

  public function sendMail(){
  	$bookingTemplate = $this->getBookingTemplate();
  	$email    = $bookingTemplate['data']['mail'];
  	$template = $bookingTemplate['template'];
  	$this->mail->setMail($email)
      ->setSubject('Payment Confirmation')
      ->setMessage($template)
      ->send();
  }

}
