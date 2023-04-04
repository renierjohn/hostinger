<?php

namespace Drupal\renify_commander\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure Renify Commander settings for this site.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'renify_commander_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['renify_commander.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['pub_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Public Key'),
      '#default_value' => $this->config('renify_commander.settings')->get('pub_key'),
    ];

    $form['md5_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('MD5 Result'),
      '#default_value' => $this->config('renify_commander.settings')->get('md5_key'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('renify_commander.settings')
      ->set('pub_key', $form_state->getValue('pub_key'))
      ->set('md5_key', $form_state->getValue('md5_key'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
