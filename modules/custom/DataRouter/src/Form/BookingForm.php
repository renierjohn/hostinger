<?php

namespace Drupal\data_router\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Flood\FloodInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\data_router\Service\AccountService;
use Drupal\Component\Utility\EmailValidatorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\data_router\Service\BookingService;

/**
 * Alias mass delete form.
 */
class BookingForm extends FormBase {

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

  const HASH = 'renify1234abcd';

  const BOOK_PATH = 'private://book/';

  /**
   *
   */
  public function __construct(EntityTypeManager $entityTypeManager, AccountService $account, EmailValidatorInterface $emailValidator, FloodInterface $flood, BookingService $bookingTemplate) {
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
    return 'data_route_booking_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $captcha = $this->account->getCapchaSiteKey();

    $form['mail'] = [
      '#type'   => 'email',
      '#title'  => $this->t('Email'),
      '#size'   => '50',
      '#attributes' => [
        'class'       => ['full-width'],
        'placeholder' => 'renify.official@gmail.com',
      ],
      '#required'  => TRUE,
      // '#value' => 'idiots2k13@gmail.com',
    ];

    $form['name'] = [
      '#type'   => 'textfield',
      '#title'  => $this->t('Name'),
      '#size'   => '50',
      '#attributes' => [
        'class'       => ['full-width'],
        'placeholder' => 'Juan',
      ],
      '#required'  => TRUE,
      // '#value' => 'renier',
    ];

    $form['lastname'] = [
      '#type'   => 'textfield',
      '#title'  => $this->t('Last Name'),
      '#size'   => '50',
      '#attributes' => [
        'class'       => ['full-width'],
        'placeholder' => 'Dela Cruz',
      ],
      '#required'  => TRUE,
      // '#value' => 'john',
    ];

    $form['number'] = [
      '#type'   => 'number',
      '#title'  => $this->t('Number'),
      '#size'   => '50',
      '#attributes' => [
        'class'       => ['full-width'],
        'placeholder' => '09153914473',
      ],
      '#required'  => TRUE,
      // '#value' => '09153914473',
    ];

    $form['address'] = [
      '#type'   => 'textfield',
      '#title'  => $this->t('Address'),
      '#size'   => '50',
      '#attributes' => [
        'class'       => ['full-width'],
        'placeholder' => 'Dumaguete City, Negros Oriental',
      ],
      '#required'  => TRUE,
      // '#value' => 'Dumaguete City, Negros Oriental',
    ];

    $form['gender'] = [
      '#type'   => 'select',
      '#title'  => $this->t('Gender'),
      '#title_display' => $this->t('Gender'),
      '#default_value' => 'male',
      '#options' => [
        'male'   => $this->t('Male'),
        'female' => $this->t('Female'),
      ],
      '#required'  => TRUE,
    ];

    $form['birthday'] = [
      '#type'   => 'date',
      '#title'  => $this->t('Birthday'),
      '#size'   => '50',
      '#validation' => FALSE,
      '#required'  => TRUE,
    ];

    $form['datetime'] = [
      '#type'   => 'datetime',
      '#title'  => $this->t('Date and Time'),
      '#size'   => '50',
      '#attributes' => [
        'class'       => ['full-width'],
      ],
    ];

    $form['vessel'] = [
      '#type'   => 'textfield',
      '#title'  => $this->t('Vessel'),
      '#size'   => '50',
      '#attributes' => [
        'class'       => ['full-width'],
      ],
      '#attributes' => [
        'class'       => ['full-width'],
        'placeholder' => 'Ocean Jet / 2Go etc...',
      ],
    ];

    $form['accomodation'] = [
      '#type'   => 'textfield',
      '#title'  => $this->t('Accomodation'),
      '#size'   => '50',
      '#attributes' => [
        'class'       => ['full-width'],
        'placeholder' => 'Open Air / Business Class / Tourist Class',
      ],
    ];

    $form['origin'] = [
      '#type'   => 'textfield',
      '#title'  => $this->t('Origin'),
      '#size'   => '50',
      '#attributes' => [
        'class'       => ['full-width'],
        'placeholder' => 'Cebu',
      ],
    ];

    $form['destination'] = [
      '#type'   => 'textfield',
      '#title'  => $this->t('Destination'),
      '#size'   => '50',
      '#attributes' => [
        'class'       => ['full-width'],
        'placeholder' => 'Dumaguete / Iloilo / Tagbilaran etc.',
      ],
    ];

    $form['price'] = [
      '#type'   => 'hidden',
      '#title'  => $this->t('price'),
    ];

    $form['hash'] = [
      '#type'   => 'hidden',
      '#title'  => $this->t('hash'),
      '#attributes' => [
        'class'     => [time()],
        'hash'      => [self::HASH],
      ],
    ];

    $form['submit'] = [
      '#type'   => 'submit',
      '#value'  => 'Book Ticket',
      '#prefix' => '<div class="message-wrapper">',
      '#suffix' => '</div>',
      '#attributes' => [
        'class'       => ['btn--primary full-width'],
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $emailValidator = $this->emailValidator;
    $account        = $this->account;
    $input          = $form_state->getUserInput();
    $values         = $form_state->getValues();
    $token          = $input['g-recaptcha-response'];
    $email          = $input['mail'];
    $bookDate       = $values['datetime'];

    if (empty($email)) {
      return $form_state->setErrorByName('email', 'Please Type Youre Email . Thankyou');
    }

    if (empty($bookDate)) {
      return $form_state->setErrorByName('email', 'Please Select @ Rates & Schedules Tab to Proceed Booking . Thankyou');
    }

    $hash  = $values['hash'];
    $price = $values['price'];

    $genHash = md5($price . self::HASH);
    if ($hash != $genHash) {
      return $form_state->setErrorByName('email', "Please Don't Change the Rate. Thankyou");
    }

    $anonyms = \Drupal::currentUser()->isAnonymous();
    if (!$emailValidator->isValid($email) && $anonyms) {
      return $form_state->setErrorByName('email', 'Email Not Valid');
    }

    if (empty($token)) {
      return $form_state->setErrorByName('captcha', 'Please Use Captcha');
    }

    $response = $account->setToken($token)->checkCaptcha();
    if ($response == FALSE) {
      return $form_state->setErrorByName('captcha', 'Sorry , Youre Captcha was expired. Please Login again');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $curent_path = \Drupal::request()->getRequestUri();
    $values      = $form_state->getValues();
    $email       = $values['mail'];
    $bookDate    = $values['datetime']->__toString();

    $values['datetime'] = $bookDate;
    $values['url']      = $curent_path;
    $values['pending']  = TRUE;
    $values['remarks']  = '"Please Pay using GCash<br><br><br> <b><h4>Note:</h4></b> <i><h3>Once Paid, Processing will take 5 ~ 10 min. Thank you.</h3></i>';

    $hash = time();
    $hash = 'r.' . md5($hash) . '-' . $hash;
    $values['hash'] = $hash;
    $data = json_encode($values);
    $this->storeFile($hash, $values);

    $this->bookingTemplate->formatBookingMessage($values)->sendMailManual();

    $url = 'https://renifysite.com/book/' . $hash;
    \Drupal::messenger()->addMessage(t('ThankYou For Booking . Youre Booking Status is <a href="@link" target="_blank">@link</a>', [
      '@link' => $url,
    ]));
  }

  /**
   *
   */
  private function storeFile($hash, $data) {
    $file = fopen(self::BOOK_PATH . $hash, 'w');
    $data = json_encode($data);
    fwrite($file, $data);
    fclose($file);
  }

}
