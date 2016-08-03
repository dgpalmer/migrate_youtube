<?php

namespace Drupal\migrate_youtube\Plugin\migrate_plus;

use Drupal\Core\Plugin\PluginBase;
use Drupal\migrate_plus\DataParserPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a base data parser implementation.
 *
 * @see \Drupal\migrate_plus\Annotation\DataParser
 * @see \Drupal\migrate_plus\DataParserPluginInterface
 * @see \Drupal\migrate_plus\DataParserPluginManager
 * @see plugin_api
 */
abstract class ClientDataParserPluginBase extends PluginBase implements DataParserPluginInterface {

  /**
   * List of source ids.
   *
   * @var string[]
   */
  protected $id;

  /**
   * Index of the currently-open url.
   *
   * @var int
   */
  protected $activeId;

  /**
   * String indicating how to select an item's data from the source.
   *
   * @var string
   */
  protected $itemSelector;

  /**
   * Current item when iterating.
   *
   * @var mixed
   */
  protected $currentItem = NULL;

  /**
   * Value of the ID for the current item when iterating.
   *
   * @var string
   */
  protected $currentId = NULL;

  /**
   * The data retrieval client.
   *
   * @var \Drupal\migrate_plus\DataFetcherPluginInterface
   */
  protected $dataFetcher;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->id = $configuration['id'];
    $this->itemSelector = $configuration['item_selector'];
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition);
  }


  /**
   * Returns the initialized data fetcher plugin.
   *
   * @return \Drupal\migrate_plus\DataFetcherPluginInterface
   *   The data fetcher plugin.
   */
  public function getDataFetcherPlugin() {
    if (!isset($this->dataFetcherPlugin)) {
      $this->dataFetcherPlugin = \Drupal::service('plugin.manager.migrate_plus.data_fetcher')->createInstance($this->configuration['data_fetcher_plugin'], $this->configuration);
    }
    return $this->dataFetcherPlugin;
  }

  /**
   * {@inheritdoc}
   */
  public function rewind() {
    $this->activeId = NULL;
    $this->next();
  }

  /**
   * Implementation of Iterator::next().
   */
  public function next() {
    $this->currentItem = $this->currentId = NULL;
    if (is_null($this->activeId)) {
      if (!$this->nextSource()) {
        // No data to import.
        return;
      }
    }
    // At this point, we have a valid open source url, try to fetch a row from
    // it.
    $this->fetchNextRow();
    // If there was no valid row there, try the next url (if any).
    if (is_null($this->currentItem)) {
      if ($this->nextSource()) {
        $this->fetchNextRow();
      }
    }
    if ($this->valid()) {
      foreach ($this->configuration['ids'] as $id_field_name => $id_info) {
        $this->currentId[$id_field_name] = $this->currentItem[$id_field_name];
      }
    }
  }

  /**
   * Opens the specified Id.
   *
   * @param $id
   *   Id to open.
   *
   * @return bool
   *   TRUE if the URL was successfully opened, FALSE otherwise.
   */
  abstract protected function openSourceId($id);

  /**
   * Retrieves the next row of data from the open source URL, populating
   * currentItem.
   */
  abstract protected function fetchNextRow();

  /**
   * Advances the data parser to the next source url.
   *
   * @return bool
   *   TRUE if a valid source URL was opened
   */
  protected function nextSource() {
    while ($this->activeId === NULL || (count($this->id) - 1) > $this->activeId) {
      if (is_null($this->activeId)) {
        $this->activeId = 0;
      }
      else {
        // Increment the activeUrl so we try to load the next source.
        $this->activeId = $this->activeId + 1;
        if ($this->activeId >= count($this->id)) {
          return FALSE;
        }
      }

      if ($this->openSourceId($this->id[$this->activeId])) {
        // We have a valid source.
        return TRUE;
      }
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function current() {
    return $this->currentItem;
  }

  /**
   * {@inheritdoc}
   */
  public function key() {
    return $this->currentId;
  }
  /**
   * {@inheritdoc}
   */
  public function valid() {
    return !empty($this->currentItem);
  }

  /**
   * {@inheritdoc}
   */
  public function count() {
    $count = 0;
    foreach ($this as $item) {
      $count++;
    }
    return $count;
  }

  /**
   * Return the selectors used to populate each configured field.
   *
   * @return string[]
   *   Array of selectors, keyed by field name.
   */
  protected function fieldSelectors() {
    $fields = [];
    foreach ($this->configuration['fields'] as $field_info) {
      if (isset($field_info['selector'])) {
        $fields[$field_info['name']] = $field_info['selector'];
      }
    }
    return $fields;
  }
}
