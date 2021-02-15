<?php

namespace Drupal\campaignion_logcrm;

use \Dflydev\Hawk\Credentials\Credentials;
use \Dflydev\Hawk\Client\ClientBuilder;

/**
 * A logCRM API-client using HAWK authentication.
 */
class Client {
  protected $client;
  protected $credentials;
  protected $url;

  public static function fromConfig(array $config) {
    foreach (['events_url', 'public_key', 'secret_key'] as $v) {
      if (!isset($config[$v])) {
        throw new ApiConfigError(
          'No valid logcrm credentials found. The credentials must contain ' .
          'at least values for "event_url", "public_key" and "private_key".'
        );
      }
    }
    return new static($config['events_url'], $config['public_key'], $config['secret_key']);
  }

  public function __construct($url, $pk, $sk) {
    $this->url = $url;
    $this->credentials = new Credentials($sk, 'sha256', $pk);
    $this->client = ClientBuilder::create()->build();
  }

  /**
   * Send json encoded data to the event-API.
   *
   * @param string $json
   *   JSON encoded data.
   * @return string
   *   The response from the server.
   */
  public function post($json) {
    $content_type = 'application/json';
    $hawk = $this->client->createRequest(
      $this->credentials,
      $this->url,
      'POST',
      ['payload' => $json, 'content_type' => $content_type]
    );
    $headers['Content-Type'] = $content_type;
    $headers[$hawk->header()->fieldName()] = $hawk->header()->fieldValue();

    $options['headers'] = $headers;
    $options['data'] = $json;
    $options['method'] = 'POST';
    $r = drupal_http_request($this->url, $options);
    if ($r->code < 0) {
      // Some kind of connection error.
      throw new ApiError($r->code, $r->error, '');
    }
    if ($r->code != 200) {
      $d = \drupal_json_decode($r->data);
      throw new ApiError($r->code, $r->status_message, $d['message']);
    }
  }

  public function sendEvent(Event $event) {
    return $this->post(drupal_json_encode($event->toArray()));
  }

}
