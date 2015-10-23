<?php

namespace Drupal\campaignion_logcrm;

use \Dflydev\Hawk\Credentials\Credentials;
use \Dflydev\Hawk\Client\ClientBuilder;

class Client {
  protected $client;
  protected $credentials;
  protected $url;

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
    return drupal_http_request($this->url, $options);
  }

  public function sendEvent($event) {
    return $this->post(drupal_json_encode($event));
  }

}

