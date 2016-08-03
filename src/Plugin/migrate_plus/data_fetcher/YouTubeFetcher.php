<?php

namespace Drupal\migrate_youtube\Plugin\migrate_plus\data_fetcher;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\migrate\MigrateException;
use Drupal\migrate_plus\DataFetcherPluginBase;
use GuzzleHttp\Exception\RequestException;
use Symfony\Component\HttpFoundation\Request;

/**
 * Retrieve data over an HTTP connection for migration.
 *
 * @DataFetcher(
 *   id = "youtubefetcher"
 * )
 */
class YouTubeFetcher extends DataFetcherPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The Google Client
   *
   * @var \Google_Client
   */
  protected $GoogleClient;

  /**
   * The YouTube Client
   *
   * @var \YouTube
   */
  protected $YouTube;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $key = \Drupal::config('migrate_youtube.settings')->get('api_key');

    $this->GoogleClient = new \Google_Client();
    $this->GoogleClient->setScopes('https://www.googleapis.com/auth/youtube');
    $this->GoogleClient->setDeveloperKey($key);

    $server_http_host = \Drupal::request()->server->get('HTTP_HOST'); // server variable
    $server_php_self = \Drupal::request()->server->get('PHP_SELF'); // server variable

    $redirect = filter_var('http://' . $server_http_host . $server_php_self, FILTER_SANITIZE_URL);
    $this->GoogleClient->setRedirectUri($redirect);

    //For some reason my client was trying to verify an SSL cert that it couldnt, so this disabled it.
    $guzzleClient = new \GuzzleHttp\Client(array( 'curl' => array( CURLOPT_SSL_VERIFYPEER => false, ), ));
    $this->GoogleClient->setHttpClient($guzzleClient);

    $this->YouTube = new \Google_Service_YouTube($this->GoogleClient);
  }

  /**
   * {@inheritdoc}
   */
  public function get_videos($id) {
    try {
      $playlist_id = $this->get_playlist($id);
      if (!empty($playlist_id)) {
        $continue = true;
        $videos = array();
        $options = array(
          'playlistId' => $playlist_id,
          'maxResults' => 50,
        );
        while ($continue) {
          $video_ids = array();
          if (isset($pageToken)) {
            $options['pageToken'] = $pageToken;
          }
          $playlistItemsResponse = $this->YouTube->playlistItems->listPlaylistItems('contentDetails', $options);
          if (isset($playlistItemsResponse->nextPageToken)) {
            $pageToken = $playlistItemsResponse->nextPageToken;
            $continue = true;
          } else {
            $continue = false;
          }
          foreach ($playlistItemsResponse['items'] as $playlistItem) {
            $video_ids[] = $playlistItem['contentDetails']['videoId'];
          }
          $videos = array_merge($videos, $this->get_video_details($video_ids));
        }
      }
    }
    catch (RequestException $e) {
      throw new MigrateException('Error message: ' . $e->getMessage() . ' at get_videos for ' . $id.'.');
    }
    return $videos;
  }

  /**
   * {@inheritdoc}
   */
  protected function get_playlist($id) {
    try {
      $parameters = array('forUsername' => $id);
      $response = $this->YouTube->channels->listChannels('contentDetails', $parameters);

      if (empty($response)) {
        throw new MigrateException('No response at ' . $id. '.');
      } else {
        foreach ($response['items'] as $channel) {
          // Extract the unique playlist ID that identifies the list of videos
          // uploaded to the channel, and then call the playlistItems.list method
          // to retrieve that list.
          $playlist_id = $channel['contentDetails']['relatedPlaylists']['uploads'];
        }
      }
    }
    catch (RequestException $e) {
      throw new MigrateException('Error message: ' . $e->getMessage() . ' at get_playlist for ' . $id.'.');
    }
    return $playlist_id;
  }

  /**
   * {@inheritdoc}
   */
  protected function get_video_details($video_ids) {
    $video_ids_str = implode(',', $video_ids);
    $videosItemsResponse = $this->YouTube->videos->listVideos('status, contentDetails, snippet, statistics', array(
      'id' => $video_ids_str,
    ));
    $videos = [];
    foreach($videosItemsResponse['items'] as $video) {
      $videos[] = array(
        'id' => $video['id'],
        'title' => $video['snippet']['title'],
        'description' => $video['snippet']['description'],
        'category' => $video['snippet']['categoryId'],
        'published' => $video['snippet']['publishedAt'],
        'duration' => $video['contentDetails']['duration'],
        'view_count' => $video['statistics']['viewCount'],
        'author_name' => $video['snippet']['channelTitle'],
        'author_url' => 'http://www.youtube.com/user/' . $video['snippet']['channelTitle'],
        'video_url' => 'http://www.youtube.com/watch?v=' . $video['id'],
      );
    }
    
    return $videos;
  }
  
  function setRequestHeaders(array $headers) { }
  function getRequestHeaders() { }
  function getResponseContent($url) { }
  function getResponse($url) { }
}
