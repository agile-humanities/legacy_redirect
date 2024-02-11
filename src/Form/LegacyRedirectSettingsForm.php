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
    $message = $this->t("You have arrived here using a URL from our old site. Please update your bookmarks.");
    $form['pid_reference'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Pid Reference Field'),
      '#default_value' => $config->get('pid_reference') ? $config->get('pid_reference') : 'field_pid',
    ];
    $form['redirect_message'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Redirect message'),
      '#default_value' => $config->get('redirect_message') ? $config->get('redirect_message') : $message,
    ];

    $form['not_found'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Page not found URL'),
      '#default_value' => $config->get('not_found') ? $config->get('not_found') : '/',
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config(static::SETTINGS)
      ->set('pid_reference', $form_state->getValue('pid_reference'))
      ->set('redirect_message', $form_state->getValue('redirect_message'))
      ->set('not_found', $form_state->getValue('not_found'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
