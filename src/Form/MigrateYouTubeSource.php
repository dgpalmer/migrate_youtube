<?php

namespace Drupal\migrate_youtube\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\migrate_youtube\YouTubeMigrationGenerator;

/**
 * Class MigrateYouTubeSource.
 *
 * @package Drupal\migrate_youtube\Form
 */
class MigrateYouTubeSource extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'migrate_youtube.migrateyoutubesource',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'migrate_youtube_source';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('migrate_youtube.migrateyoutubesource');

    // API Key
    $form['youtube_channel'] = array(
      '#type' => 'textfield',
      '#title' => 'Youtube Channel Name or ID',
      '#description' => t('e.g. KendrickLamarVevo'),
    );
    //  Author
    $form['author'] = array(
      '#type' => 'textfield',
      '#title' => 'Author',
      '#description' => t('e.g. Author'),
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
    parent::submitForm($form, $form_state);

    $configuration = [];
    $configuration['youtube_channel'] = $form_state->getValue('youtube_channel');
    $configuration['author'] = $form_state->getValue('author');

    $generator = new YouTubeMigrationGenerator($configuration);
    $generator->createMigrations();
    $this->config('migrate_youtube.migrateyoutubesource')
      ->save();
  }

}
