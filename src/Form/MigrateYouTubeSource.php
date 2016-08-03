<?php

namespace Drupal\migrate_youtube\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

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

    $this->config('migrate_youtube.migrateyoutubesource')
      ->save();
  }

}
