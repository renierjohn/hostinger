<?php

namespace Drupal\rest_api_access_token\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class ConfigForm.
 *
 * @package Drupal\rest_api_access_token\Form
 */
class ConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'rest_api_access_token.config',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'rest_api_access_token_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['container'] = [
      '#type' => 'container',
      '#prefix' => '<div id="rest_api_access_token_config_form">',
      '#suffix' => '</div>',
    ];

    $form['container']['signature_verification'] = [
      '#type' => 'checkbox',
      '#description' => $this->t('X-AUTH-SIGNATURE HEADER: sha256("token|requestId|path|base64_body|secret")'),
      '#title' => $this->t('Enable signature verification.'),
      '#default_value' => $this->config('rest_api_access_token.config')
        ->get('signature_verification'),
    ];

    $form['container']['cache_endpoints'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable cache endpoints by REQUEST-ID (in header).'),
      '#default_value' => $this->config('rest_api_access_token.config')
        ->get('cache_endpoints'),
    ];

    $form['container']['cache_endpoints_lifetime'] = [
      '#type' => 'number',
      '#title' => $this->t('Set lifetime of cache endpoints in seconds.'),
      '#default_value' => (int) $this->config('rest_api_access_token.config')
        ->get('cache_endpoints_lifetime'),
      '#description' => $this->t('For CACHE_PERMANENT set 0.'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $values = $form_state->getValues();

    if (isset($values['signature_verification'])) {
      $this->config('rest_api_access_token.config')
        ->set('signature_verification', $values['signature_verification'])
        ->save();
    }

    if (isset($values['cache_endpoints'])) {
      $this->config('rest_api_access_token.config')
        ->set('cache_endpoints', $values['cache_endpoints'])
        ->save();
    }

    if (isset($values['cache_endpoints_lifetime'])) {
      $this->config('rest_api_access_token.config')
        ->set('cache_endpoints_lifetime', $values['cache_endpoints_lifetime'])
        ->save();
    }
  }

}
