<?php

namespace Drupal\data_router\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\data_router\Service\AccountService;
use Drupal\Component\Utility\EmailValidatorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Alias mass delete form.
 */
class SignupForm extends FormBase {

  protected $entityTypeManager;

  protected $account;

  protected $emailValidator;

  /**
   *
   */
  public function __construct(EntityTypeManager $entityTypeManager, AccountService $account, EmailValidatorInterface $emailValidator) {
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
      $container->get('email.validator')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'data_route_signup_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $captcha = $this->account->getCapchaSiteKey();

    $form['signup']['email'] = [
      '#type'   => 'email',
      '#title'  => $this->t('Delete options'),
      '#size'   => '300',
      '#suffix' => '<div class="g-recaptcha" data-size="normal" data-tabindex="10" data-sitekey=' . $captcha . '></div>',
      '#attributes' => [
        'class' => ['email'],
        'id'    => ['register-email'],
        'placeholder' => ['Email Address'],
        'style' => ['color: white !important'],
      ],
    ];

    $form['signup']['captcha'] = [
      '#type'   => 'hidden',
      '#value'  => '',
    ];

    $form['signup']['submit'] = [
      '#type' => 'submit',
      '#value' => 'SIGN UP',
      '#prefix' => '<div class="submit-wrapper">',
      '#suffix' => '</div>',
      '#attributes' => [
        'class' => ['register'],
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
    $token          = $input['g-recaptcha-response'];
    $email          = $input['email'];

    if (!$emailValidator->isValid($email)) {
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
   *
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $account = $this->account;
    $values  = $form_state->getValues();

    $email  = $values['email'];
    $result = $account->setEmail($email)->register();

    if ($result['status'] == TRUE) {
      \Drupal::messenger()->addMessage($result['email'] . ' was successfully registed . Please confirm the email');
    }

    if ($result['status'] == FALSE) {
      \Drupal::messenger()->addError($result['message']);
    }
  }

}
