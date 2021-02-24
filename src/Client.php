<?php

namespace Drupal\campaignion_logcrm;

use \Dflydev\Hawk\Credentials\Credentials;
use \Dflydev\Hawk\Client\ClientBuilder;

use \Drupal\little_helpers\Rest\Client as RestClient;

/**
 * A logCRM API-client using HAWK authentication.
 */
class Client extends RestClient {

  protected $client;
  protected $credentials;

  public static function fromConfig(array $config) {
    foreach (['endpoint', 'public_key', 'secret_key'] as $v) {
      if (!isset($config[$v])) {
        throw new ApiConfigError(
          'No valid logcrm credentials found. The credentials must contain ' .
          'at least values for "endpoint", "public_key" and "private_key".'
        );
      }
    }
    return new static($config['endpoint'], $config['public_key'], $config['secret_key']);
  }

  /**
   * Create a new instance.
   */
  public function __construct($endpoint, $pk, $sk) {
    parent::__construct($endpoint);
    $this->credentials = new Credentials($sk, 'sha256', $pk);
    $this->client = ClientBuilder::create()->build();
  }

  /**
   * Add the HAWK authentication headers and send.
   */
  protected function sendRequest($url, array $options) {
    $request_options = [];
    if (isset($options['data'])) {
      $request_options['content_type'] = $options['headers']['Content-Type'];
      $request_options['payload'] = $options['data'];
    }
    else {
      $request_options['content_type'] = '';
      $request_options['payload'] = '';
    }
    $hawk = $this->client->createRequest($this->credentials, $url, $options['method'], $request_options);
    $options['headers'][$hawk->header()->fieldName()] = $hawk->header()->fieldValue();
    return parent::sendRequest($url, $options);
  }

  /**
   * Send an event to the API.
   */
  public function sendEvent(Event $event) {
    return $this->post('/events', [], $event->toArray());
  }

}
