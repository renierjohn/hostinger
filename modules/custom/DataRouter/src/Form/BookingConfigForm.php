<?php

namespace Drupal\data_router\Form;

use Drupal\Core\Site\Settings;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Flood\FloodInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Component\Utility\EmailValidatorInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Drupal\data_router\Service\AccountService;
use Drupal\data_router\Service\BookingService;

/**
 * Alias mass delete form.
 */
class BookingConfigForm extends FormBase {

  /**
   * The flood control mechanism.
   *
   * @var \Drupal\Core\Flood\FloodInterface
   */
  protected $flood;
  /**
   * The flood control mechanism.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;
  /**
   * The flood control mechanism.
   *
   * @var \Drupal\data_router\Service\AccountService
   */
  protected $account;
  /**
   * The flood control mechanism.
   *
   * @var \Drupal\Component\Utility\EmailValidatorInterface
   */
  protected $emailValidator;

  protected $bookingTemplate;

  public function __construct(EntityTypeManager $entityTypeManager,AccountService $account,EmailValidatorInterface $emailValidator,FloodInterface $flood,BookingService $bookingTemplate) {
    $this->entityTypeManager = $entityTypeManager;
    $this->emailValidator    = $emailValidator;
    $this->account           = $account;
    $this->bookingTemplate   = $bookingTemplate;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('data_router.account'),
      $container->get('email.validator'),
      $container->get('flood'),
      $container->get('data_router.bookservice')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'data_route_config_booking_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $files = scandir('private://book');
    unset($files[0]);unset($files[1]);unset($files[2]);
    
    $form['book_list'] = [
      '#type'    => 'select',
      '#title'   => $this->t('Select Hash'),
      '#options' => array_combine($files,$files),
    ];

    $form['price'] = [
      '#type'      => 'textfield',
      '#title'     => $this->t('Price'),
      // '#required'  => TRUE,
    ];

    $form['remarks'] = [
      '#type'      => 'textarea',
      '#size'      => 10,
      '#title'     => $this->t('Remarks'),
      // '#required'  => TRUE,
      '#default_value' => 'Success !!! . Booking Completed . Thank You',
    ];

    $form['status'] = [
      '#type'      => 'checkbox',
      '#title'     => $this->t('Pending'),
      '#default_value' => TRUE,
    ];

   

    $form['submit'] = [
      '#type'   => 'submit',
      '#value'  => 'Set Book Ticket',
  
    ];

  // \Drupal::messenger()->addMessage('ThankYou For Booking Visit');

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
  
  }
  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $curent_path = \Drupal::request()->getRequestUri();
    $values    = $form_state->getValues();
    // $input   = $form_state->getUserInput();
    $hash    = $values['book_list'];
    $price   = $values['price'];
    $remarks = $values['remarks'];
    $status  = $values['status'];

    $file = fopen('private://book/'.$hash,'r');
    $data = fread($file,10000);
    fclose($file);

    $data = json_decode($data,TRUE);
    $old_status      = $data['pending']; 
    $data['pending'] = $status;
    
    if(!empty($remarks)){
      $data['remarks'] = $remarks;
    }

    if(!empty($price)){
      $data['price'] = $price;
    }

    if($status == FALSE && $old_status == TRUE){
      $this->bookingTemplate->formatBookingMessage($data)->sendMailManual();
    }

    $this->storeFile($hash,$data);
    \Drupal::messenger()->addMessage('Success');
  }

  private function storeFile($hash,$data){
    $file = fopen('private://book/'.$hash,'w');
    $data = json_encode($data);
    fwrite($file,$data);
    fclose($file);
  }

}
