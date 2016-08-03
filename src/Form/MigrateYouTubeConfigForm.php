<?php

namespace Drupal\migrate_youtube\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class MigrateYouTubeConfigForm
 *
 * @package Drupal\migrate_youtube\Form
 */
class MigrateYouTubeConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'migrate_youtube.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'migrate_youtube_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('migrate_youtube.settings');

   /**
    * YouTube Settings
    */

    $form['youtube'] = array(
      '#type' => 'fieldgroup',
      '#title' => t('Youtube Migration Settings'),
    );

    // API Key
    $form['youtube']['api_key'] = array(
      '#type' => 'textfield',
      '#title' => 'Youtube API Key',
      '#description' => t('Set up an API account here https://console.developers.google.com/projectselector/apis/credentials'),
      '#default_value' => $config->get('api_key'),
    );

    // Update Frequency
    $update_options = array(
      '1' => t('Every Hour'),
      '2' => t('Every 2 Hours'),
      '4' => t('Every 4 Hours'),
      '12' => t('Every 12 Hours'),
      '24' => t('Every Day'),
    );
    $form['youtube']['update_frequency'] = array(
      '#type' => 'select',
      '#title' => 'Youtube Update Frequency',
      '#options' => $update_options,
      '#default_value' => $config->get('update_frequency'),
    );

    // Display Last Updated Date
    $form['youtube']['last_updated'] = array(
      '#type' => 'value',
      '#title' => t('Last Updated'),
      '#value' => t('Never'),
    );

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
    $config = \Drupal::service('config.factory')->getEditable('migrate_youtube.settings');
    $config->set('api_key', $form_state->getValue('api_key'))
      ->save();
    $config->set('update_frequency', $form_state->getValue('update_frequency'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
