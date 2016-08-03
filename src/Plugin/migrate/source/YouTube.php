<?php

namespace Drupal\migrate_youtube\Plugin\migrate\source;

use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate_plus\DataParserPluginInterface;
use Drupal\migrate_plus\Plugin\migrate\source\SourcePluginExtension;

/**
 * A Google API Youtube Migration
 *
 * @MigrateSource(
 *   id = "youtube"
 * )
 */
class YouTube extends SourcePluginExtension {
  /**
   * The source YouTubeIDs to retrieve
   *
   * @var array
   */ 
  protected $youtubeIDs = [];

  /**
    * The data parser plugin
    *
    * @var \Drupal\migrate_plus\DataParserPluginInterface
    */
  protected $dataParserPlugin;

  /**
    * {@inheritdoc}
    */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration) {
    if (!is_array($configuration['id'])) {
      $configuration['id'] = [$configuration['id']]; 

      $this->youtubeIDs = $configuration['id'];
      parent::__construct($configuration, $plugin_id, $plugin_definition, $migration);
    }
  }
  /**
   * Return a string representing the source URLs.
   *
   * @return string
   *   Comma-separated list of URLs being imported.
   */
  public function __toString() {
    // This could cause a problem when using a lot of urls, may need to hash.
    $id = implode(', ', $this->youtubeIDs);
    return $id;
  }

  /**
   * Returns the initialized data parser plugin.
   *
   * @return \Drupal\migrate_plus\DataParserPluginInterface
   *   The data parser plugin.
   */
  public function getDataParserPlugin() {
    if (!isset($this->dataParserPlugin)) {
      $this->dataParserPlugin = \Drupal::service('plugin.manager.migrate_plus.data_parser')->createInstance($this->configuration['data_parser_plugin'], $this->configuration);
    }
    return $this->dataParserPlugin;
  }

  /**
   * Creates and returns a filtered Iterator over the documents.
   *
   * @return \Iterator
   *   An iterator over the documents providing source rows that match the
   *   configured item_selector.
   */
  protected function initializeIterator() {
    return $this->getDataParserPlugin();
  }
}
