<?php

namespace Drupal\migrate_youtube\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class YouTube extends DeriverBase implements ContainerDeriverInterface {

  protected $migrations;

  public function __construct(array $migrations) {
    $this->migrations = $migrations;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $container->get('entity.manager')->getDefinitions()
    );
  }

  public function getDerivativeDefinitions($base_plugin_definition) {
    $youtubes = array('KendricKLamarVevo', 'SelenaGomezVevo', 'GwenStefaniVevo');
    $fields = [];
    $fields[] = array(
      'name' => 'youtube_id',
      'label' => 'YouTube Id',
      'selector' => 'id',
    );
    $fields[] = array(
      'name' => 'title',
      'label' => 'YouTube Title',
      'selector' => 'title',
    );
    foreach ($youtubes as $youtube) {
      $this->derivatives[$youtube] = $base_plugin_definition;
      $this->derivatives[$youtube]['id'] = $youtube;
      $this->derivatives[$youtube]['source']['id'] = $youtube;
      $this->derivatives[$youtube]['source']['plugin'] = 'youtube';
      $this->derivatives[$youtube]['source']['data_fetcher_plugin'] = 'youtubefetcher';
      $this->derivatives[$youtube]['source']['data_parser_plugin'] = 'youtubeparser';
      $this->derivatives[$youtube]['source']['fields'] = $fields;
      $this->derivatives[$youtube]['source']['ids']['youtube_id']['type'] = 'string';
      $this->derivatives[$youtube]['destination']['plugin'] = 'entity:node';
      $this->derivatives[$youtube]['process']['type']['plugin'] = 'default_value';
      $this->derivatives[$youtube]['process']['type']['default_value'] = 'video';
      $this->derivatives[$youtube]['process']['id'] = 'youtube_id';
      $this->derivatives[$youtube]['process']['title'] = 'title';
      $this->derivatives[$youtube]['migration_dependencies'] = [];
    }
    return $this->derivatives;
  }
}
