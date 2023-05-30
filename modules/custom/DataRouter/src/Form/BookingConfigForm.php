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

  const BOOK_PATH = 'private://book/';

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
    // $form['#tree'] = TRUE;
    $files = scandir(self::BOOK_PATH);
    unset($files[0]);unset($files[1]);unset($files[2]);unset($files[3]);

    $form['tickets'] = [
      '#type' => 'details',
      '#title' => t('Submissions'),
      '#collapsible' => TRUE, // Added
      '#collapsed' => TRUE,  // Added
    ];

    foreach ($files as $index => $hash) {
      $data = $this->getData($hash);

     $form['tickets']['detail'][$index] = [
      '#type' => 'details',
      '#title' =>  $data['name'] . ' ' .  $data['lastname'] . ' , ' . $hash,
      '#collapsible' => TRUE, // Added
      '#collapsed' => TRUE,  // Added
     ];

      $ticket_status = $data['pending'] ?
        'Status:  <i>In Progress</i>' :
        'Status:  <i>Done</i>';

      $form['tickets']['detail'][$index]['boolean'] = [
        '#type' => 'checkbox',
        '#title' => $data['hash'],
        '#value' => $data['hash'],
        '#prefix' => '<br>',
        '#suffix' => $ticket_status .'<br> Total Balance: <b>PHP ' . $data['price'] . '<b>'
      ];

      $form['tickets']['detail'][$index]['vessel'] = [
        '#type' => 'textfield',
        '#title' => 'Vessel',
        '#disabled' => True,
        '#value' => $data['vessel'],
      ];

      $form['tickets']['detail'][$index]['accomodation'] = [
        '#type' => 'textfield',
        '#title' => 'Accomodation',
        '#disabled' => True,
        '#value' => $data['accomodation'],
      ];

      $form['tickets']['detail'][$index]['datetime'] = [
        '#type' => 'textfield',
        '#title' => 'Date & Time',
        '#disabled' => True,
        '#value' => $data['datetime'],
      ];

      $form['tickets']['detail'][$index]['route'] = [
        '#type' => 'textfield',
        '#title' => 'Route',
        '#disabled' => True,
        '#value' => 'from ' . $data['origin'] .' ~ to ' . $data['destination'] ,
      ];

      $form['tickets']['detail'][$index]['name'] = [
        '#type' => 'textfield',
        '#title' => 'Name',
        '#disabled' => True,
        '#value' => $data['name'],
      ];

      $form['tickets']['detail'][$index]['lastname'] = [
        '#type' => 'textfield',
        '#title' => 'Lastname',
        '#disabled' => True,
        '#value' => $data['lastname'],
      ];

      $form['tickets']['detail'][$index]['bday'] = [
        '#type' => 'textfield',
        '#title' => 'Birthday',
        '#disabled' => True,
        '#value' => $data['birthday'],
      ];

      $form['tickets']['detail'][$index]['gender'] = [
        '#type' => 'textfield',
        '#title' => 'Gender',
        '#disabled' => True,
        '#value' => $data['gender'],
      ];

      $form['tickets']['detail'][$index]['number'] = [
        '#type' => 'textfield',
        '#title' => 'Number',
        '#disabled' => True,
        '#value' => $data['number'],
      ];
    }


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


    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {}

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $curent_path = \Drupal::request()->getRequestUri();
    $values    = $form_state->getValues();
    $input   = $form_state->getUserInput();
    $hash    = $values['book_list'];
    $price   = $values['price'];
    $remarks = $values['remarks'];
    $status  = $values['status'];

    $file = fopen(self::BOOK_PATH . $hash, 'r');
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

  protected function storeFile($hash,$data){
    $file = fopen(self::BOOK_PATH . $hash, 'w');
    $data = json_encode($data);
    fwrite($file,$data);
    fclose($file);
  }

  protected function getData($hash) {
    try {
      $file = fopen(self::BOOK_PATH . $hash, 'r+');
    }
    catch(e) {
      $file = False;
    }

    if (empty($file)) {
      return;
    }

    $data = fread($file,10000);
    $data = json_decode($data,TRUE);
    fclose($file);
    return $data;
  }

}

