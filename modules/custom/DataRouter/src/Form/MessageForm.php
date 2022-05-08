<?php

namespace Drupal\data_router\Form;

use Drupal\Core\Site\Settings;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Flood\FloodInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\data_router\Service\AccountService;
use Drupal\Component\Utility\EmailValidatorInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Alias mass delete form.
 */
class MessageForm extends FormBase {

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

  public function __construct(EntityTypeManager $entityTypeManager,AccountService $account,EmailValidatorInterface $emailValidator,FloodInterface $flood) {
    $this->entityTypeManager = $entityTypeManager;
    $this->emailValidator    = $emailValidator;
    $this->account           = $account;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('data_router.account'),
      $container->get('email.validator'),
      $container->get('flood')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'data_route_message_form';
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
        'placeholder' => 'youre_email@gmail.com',
      ],
    ];

    $form['message'] = [
      '#type'   => 'textarea',
      '#title'  => $this->t('Message'),
      '#size'   => '600',
      '#suffix' => '<div class="g-recaptcha" data-size="normal" data-tabindex="10" data-sitekey='.$captcha.'></div>',
      '#attributes' => [
        'class'       => ['full-width'],
        'placeholder' => 'Your message (Max 600 characters)',
      ],
    ];

    $form['submit'] = [
      '#type'   => 'submit',
      '#value'  => 'Send Message',
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
    $account = $this->account;
    $input   = $form_state->getUserInput();
    $token   = $input['g-recaptcha-response'];
    $email   = $input['mail'];

    $anonyms = \Drupal::currentUser()->isAnonymous();
    if(!$emailValidator->isValid($email) && $anonyms){
      return $form_state->setErrorByName('email','Email Not Valid');
    }

    if(empty($token)){
      return $form_state->setErrorByName('captcha','Please Use Captcha');
    }

    $response = $account->setToken($token)->checkCaptcha();
    if($response == false){
      return $form_state->setErrorByName('captcha','Sorry , Youre Captcha was expired. Please Login again');
    }
  }
  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $curent_path = \Drupal::request()->getRequestUri();
    $account = $this->account;
    $values  = $form_state->getValues();
    $email   = $values['mail'];
    $message = $values['message'];
    $result  = $account->setEmail($email)->setMessage('path : '.$curent_path.'</br> message: '.$message)->store_message();
    \Drupal::messenger()->addMessage('Thankyou For Messaging Us . Please Keep in Touch');
  }
}
