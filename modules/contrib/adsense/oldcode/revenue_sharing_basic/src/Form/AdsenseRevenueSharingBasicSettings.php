<?php

namespace Drupal\adsense_revenue_sharing_basic\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\field\Entity\FieldConfig;
use Drupal\node\Entity\NodeType;
use Drupal\user\Entity\Role;
use Drupal\user\RoleInterface;

/**
 * Form for the adsense revenue sharing settings.
 *
 * @phpcs:disable
 */
class AdsenseRevenueSharingBasicSettings extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'adsense_revenue_sharing_basic_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['adsense_revenue_sharing_basic.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    module_load_include('inc', 'adsense_revenue_sharing_basic', 'help/adsense_revenue_sharing_basic.help');

    $config = $this->config('adsense_revenue_sharing_basic.settings');

    $form['help'] = [
      '#type' => 'details',
      '#open' => FALSE,
      '#title' => $this->t('Help and instructions'),
    ];

    $form['help']['help'] = ['#markup' => adsense_revenue_sharing_basic_help_text()];

    $form['required'] = [
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => $this->t('Required parameters'),
    ];

    $form['required']['client_id_profile_field'] = [
      '#type' => 'select',
      '#title' => $this->t('Google AdSense client ID profile field'),
      '#default_value' => $config->get('client_id_profile_field'),
      '#options' => $this->getProfileFields(),
      '#required' => TRUE,
      '#description' => $this->t('This is the user profile field that holds the AdSense Client ID for the site owner as well as (optionally) for site users who participate in revenue sharing.'),
    ];

    $form['percentage'] = [
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => $this->t('Revenue sharing percentage'),
    ];

    $form['percentage']['percentage_author'] = [
      '#type' => 'range',
      '#title' => $this->t('Percentage of node views going to author'),
      '#default_value' => $config->get('percentage_author'),
    ];

    $form['percentage']['role'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Percentage of node views going to author with the following roles'),
      '#description' => $this->t('When the author belongs to one or more roles, the percentage of node views using his AdSense Client ID will be the maximum between the author value and the following settings for each role.'),
    ];

    $roles = Role::loadMultiple();
    unset($roles[RoleInterface::ANONYMOUS_ID]);
    unset($roles[RoleInterface::AUTHENTICATED_ID]);
    /** @var \Drupal\user\Entity\Role $role */
    foreach ($roles as $rid => $role) {
      // When using dots in the key, the form values were empty, so use dashes.
      $form['percentage']['role']['percentage_role-' . $rid] = [
        '#type' => 'range',
        '#title' => $role->label(),
        '#default_value' => is_null($config->get('percentage_role.' . $rid)) ? ADSENSE_REVENUE_SHARING_BASIC_PERCENTAGE_ROLE_DEFAULT : $config->get('percentage_role.' . $rid),
      ];
    }

    $form['content_types'] = [
      '#type' => 'details',
      '#open' => TRUE,
      '#title' => $this->t('Content types'),
    ];

    $types = NodeType::loadMultiple();
    /** @var \Drupal\node\Entity\NodeType $type */
    foreach ($types as $id => $type) {
      // When using dots in the key, the form values were empty, so use dashes.
      $form['content_types']['node_type-' . $id] = [
        '#type' => 'checkbox',
        '#title' => $type->label(),
        '#default_value' => is_null($config->get('node_type.' . $id)) ? ADSENSE_REVENUE_SHARING_BASIC_NODE_TYPE_DEFAULT : $config->get('node_type.' . $id),
      ];
    }

    $form_state->set(['#redirect'], 'admin/config/services/adsense/publisher');

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->configFactory()->getEditable('adsense_revenue_sharing_basic.settings');
    $form_state->cleanValues();

    foreach ($form_state->getValues() as $key => $value) {
      // Replace dashes with dots so that the mapping can be properly set.
      $config->set(str_replace('-', '.', $key), $value);
    }
    $config->save();

    parent::submitForm($form, $form_state);
  }

  /**
   * Create the list for the profile field.
   *
   * @return array
   *   array of fields with the field IDs as keys and the field titles as values
   */
  protected function getProfileFields() {
    $profile_list = [];

    // Start with the core fields.
    /** @var \Drupal\Core\Entity\EntityFieldManagerInterface $entityFieldManager */
    $entityFieldManager = \Drupal::service('entity_field.manager');
    $fields = $entityFieldManager->getFieldDefinitions('user', 'user');
    foreach ($fields as $field) {
      if ($field instanceof FieldConfig && ($field->getType() == 'string')) {
        /** @var \Drupal\field\Entity\FieldConfig $field */
        $profile_list['field:' . $field->getName()] = $field->label();
      }
    }

    // Get fields from the Profile module.
    // @todo adapt this to the profile module.
    /* @codingStandardsIgnoreStart
    if (\Drupal::moduleHandler()->moduleExists('profile')) {
      $result = db_query("SELECT fid, name, title FROM {profile_field} WHERE type='textfield' ORDER BY fid");
      foreach ($result as $row) {
        $profile_list['profile:' . $row->name] = $row->title;
      }
    }
    @codingStandardsIgnoreEnd */

    return $profile_list;
  }

}
