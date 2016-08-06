<?php

namespace Drupal\migrate_youtube;

use Drupal\migrate_plus\Entity\Migration;
use Drupal\migrate_plus\Entity\MigrationGroup;
use Drupal\migrate\Plugin\MigrationPluginManagerInterface;

/**
 * Functionality to construct YouTube Migrations from broad configuration
 * settings.
 */
class YouTubeMigrationGenerator {

  /**
   * Configuration to guide our migration creation process.
   *
   * @var array
   */
  protected $configuration = [];

  /**
   * Constructs a WordPress migration generator, using provided configuration.
   *
   * @todo: Validate inputs (e.g., make sure post type exists).
   * @link https://www.drupal.org/node/2742283
   *
   * @param $configuration
   *   An associative array:
   *   - file_uri: Drupal stream wrapper of the source WordPress XML file.
   *   - group_id: ID of the MigrationGroup holding the generated migrations.
   *   - prefix: String to prefix to the IDs of generated migrations.
   *   - default_author: If present, username to author all imported content. If
   *     absent or empty, users will be imported from WordPress.
   *   - tag_vocabulary: Machine name of vocabulary to hold tags.
   *   - category_vocabulary: Machine name of vocabulary to hold categories.
   *   - [post|page]: Associative array of type-specific configuration:
   *     - type: Machine name of Drupal node bundle to hold content.
   *     - text_format: Machine name of text format for body field.
   */
  public function __construct(array $configuration) {
    $this->configuration = $configuration;
  }
  
  /**
   * Creates a set of WordPress import migrations based on configuration settings.
   */
  public function createMigrations() {

    $dependencies = [];
    $content_id = 'YoutubeMigration' . $this->configuration['youtube_channel'];
    $migration = static::createEntityFromPlugin('youtube_videos', $content_id);
    $migration->set('migration_group', 'youtube');
    $source = $migration->get('source');
    $source['id'] = $this->configuration['youtube_channel'];
    $migration->set('source', $source);

    $migration->set('migration_dependencies', ['required' => $dependencies]);
    $migration->save();

  }

  /**
   * Create a configuration entity from a core migration plugin's configuration.
   *
   * @todo: Remove and replace calls to use Migration::createEntityFromPlugin()
   * when there's a migrate_plus release containing it we can have a dependency
   * on.
   *
   * @param string $plugin_id
   *   ID of a migration plugin managed by MigrationPluginManager.
   * @param string $new_plugin_id
   *   ID to use for the new configuration entity.
   *
   * @return \Drupal\migrate_plus\Entity\MigrationInterface
   *   A Migration configuration entity (not saved to persistent storage).
   */
  protected static function createEntityFromPlugin($plugin_id, $new_plugin_id) {
    /** @var \Drupal\migrate\Plugin\MigrationPluginManagerInterface $plugin_manager */
    $plugin_manager = \Drupal::service('plugin.manager.migration');
    $migration_plugin = $plugin_manager->createInstance($plugin_id);
    $entity_array['id'] = $new_plugin_id;
    $entity_array['label'] = $migration_plugin->label();
    $entity_array['source'] = $migration_plugin->getSourceConfiguration();
    $entity_array['destination'] = $migration_plugin->getDestinationConfiguration();
    $entity_array['process'] = $migration_plugin->getProcess();
    $entity_array['migration_dependencies'] = $migration_plugin->getMigrationDependencies();
    $migration_entity = Migration::create($entity_array);
    return $migration_entity;
  }
}
