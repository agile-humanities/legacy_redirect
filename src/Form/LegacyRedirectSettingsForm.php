<?php

namespace Drupal\legacy_redirect\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure example settings for this site.
 */
class LegacyRedirectSettingsForm extends ConfigFormBase {

  /**
   * Config settings.
   *
   * @var string
   */
  const SETTINGS = 'legacy_redirect.settings';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'legacy_redirect_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      static::SETTINGS,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config(static::SETTINGS);
    $default = $config->get('pid_reference');
    if (!$default) {
      $default = 'field_pid';
    }
    $form['pid_reference'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Pid Reference Field'),
      '#default_value' => $default,
    ];
    $form['namespaces'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Allowed namespaces'),
      '#default_value' => $config->get('namespaces'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Retrieve the configuration.
    $this->config(static::SETTINGS)
      // Set the submitted configuration setting.
      ->set('pid_reference', $form_state->getValue('pid_reference'))
      // You can set multiple configurations at once by making
      // multiple calls to set().
      ->set('other_things', $form_state->getValue('other_things'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}

