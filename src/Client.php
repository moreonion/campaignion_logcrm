<?php

namespace Drupal\campaignion_logcrm;

use Drupal\campaignion_auth\AuthAppClient;
use Drupal\little_helpers\Rest\Client as RestClient;

/**
 * A logCRM API-client using HAWK authentication.
 */
class Client extends RestClient {

  const API_VERSION = 'v1';

  /**
   * A auth app API client.
   *
   * @var \Drupal\campaignion_email_to_target\Api\AuthAppClient
   */
  protected $authClient;

  /**
   * The Impact-Stack organization that owns the data that’s being sent.
   *
   * @var string
   */
  protected $organization;

  /**
   * Create a new instance.
   *
   * @param string $url
   *   The URL for the API endpoint (withut the version prefix).
   * @param \Drupal\campaignion_auth\AuthAppClient $auth_client
   *   A auth app API client.
   * @param string $organization
   *   The current site’s impact-stack organization.
   */
  public function __construct(string $url, AuthAppClient $auth_client, string $organization) {
    parent::__construct($url . '/' . static::API_VERSION);
    $this->authClient = $auth_client;
    $this->organization = $organization;
  }

  /**
   * Add the JWT Authorization header to the request.
   */
  protected function sendRequest($url, array $options) {
    $token = $this->authClient->getToken();
    $options['headers']['Authorization'] = "Bearer $token";
    return parent::sendRequest($url, $options);
  }

  /**
   * Send an event to the API.
   */
  public function sendEvent(Event $event) {
    $event_data = $event->toArray();
    $event_data['organization'] = $this->organization;
    return $this->post('/events', [], $event_data);
  }

}
