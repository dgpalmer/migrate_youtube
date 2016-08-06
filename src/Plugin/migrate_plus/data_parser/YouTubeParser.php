<?php

namespace Drupal\migrate_youtube\Plugin\migrate_plus\data_parser;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\migrate\MigrateException;
use Drupal\migrate_youtube\Plugin\migrate_plus\ClientDataParserPluginBase;
use GuzzleHttp\Exception\RequestException;

/**
 * A Google API Youtube Migration
 *
 * @DataParser(
 *   id = "youtubeparser",
 *   title = @Translation("YouTube")
 * )
 */
class YouTubeParser extends ClientDataParserPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The request headers passed to the data fetcher.
   *
   * @var array
   */
  protected $headers = [];

  /**
   * Iterator over the JSON data.
   *
   * @var \Iterator
   */
  protected $iterator;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  protected function getSourceData($id) {
    error_log('getSourceData myt');
    $iterator = $this->getSourceIterator($id);

    // Recurse through the result array. When there is an array of items at the
    // expected depth, pull that array out as a distinct item.
    $identifierDepth = $this->itemSelector;
    $items = [];
    $iterator->rewind();
    while ($iterator->valid()) {
      $item = $iterator->current();
      if (is_array($item) && $iterator->getDepth() == $identifierDepth) {
        $items[] = $item;
      }
      $iterator->next();
    }
    return $items;
  }

  /**
   * Get the source data for reading.
   *
   * @param string $id
   *   The YouTube username or channel id to read the source data from.
   *
   * @return \RecursiveIteratorIterator|resource
   *
   * @throws \Drupal\migrate\MigrateException
   */
  protected function getSourceIterator($id) {
    try {
      $array = $this->getDataFetcherPlugin()->get_videos($id);
      // The TRUE setting means decode the response into an associative array.
      //
      // Return the results in a recursive iterator that
      // can traverse multidimensional arrays.
      return new \RecursiveIteratorIterator(
        new \RecursiveArrayIterator($array),
        \RecursiveIteratorIterator::SELF_FIRST);
    }
    catch (RequestException $e) {
      throw new MigrateException($e->getMessage(), $e->getCode(), $e);
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function openSourceId($id) {
    $source_data = $this->getSourceData($id);
    $this->iterator = new \ArrayIterator($source_data);
    return TRUE;
  }
  /**
   * {@inheritdoc}
   */
  protected function fetchNextRow() {
    $current = $this->iterator->current();
    if ($current) {
      foreach ($this->fieldSelectors() as $field_name => $selector) {
        $this->currentItem[$field_name] = $current[$selector];
      }
      $this->iterator->next();
    }
  }

}
