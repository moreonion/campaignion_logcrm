<?php

namespace Drupal\campaignion_logcrm;

use Drupal\little_helpers\Services\Container;

use Drupal\campaignion_newsletters\NewsletterList;
use Drupal\campaignion_newsletters\ProviderInterface;
use Drupal\campaignion_newsletters\QueueItem;
use Drupal\campaignion_newsletters\Subscription;

/**
 * Stub newsletter provider for integrations handled via logCRM.
 *
 * The only method actually implemented is getLists().
 */
class NewsletterProvider implements ProviderInterface {

  /**
   * The API-client.
   *
   * @var \Drupal\campaignion_logcrm\Client
   */
  protected $client;

  /**
   * Return a new instance based on the params defined in the info-hook.
   *
   * @see campaignion_logcrm_campaignion_newsletters_providers_info()
   */
  public static function fromParameters(array $params) {
    $client = Container::get()->loadService('campaignion_logcrm.client');
    return new static($client);
  }

  /**
   * Create a new provider instance by passing the client object.
   */
  public function __construct(Client $client) {
    $this->client = $client;
  }

  /**
   * Fetches current lists from the provider.
   *
   * @return array
   *   An array of Drupal\campaignion_newsletters\NewsletterList objects
   */
  public function getLists() {
    $lists = [];
    foreach ($this->client->get('/newsletter/lists')['lists'] as $list_data) {
      $lists[] = NewsletterList::fromData([
        'identifier' => $list_data['global_identifier'],
        'title' => $list_data['title'],
        'source' => 'logcrm',
        'data' => NULL,
      ]);
    }
    return $lists;
  }

  /**
   * Fetches current lists of subscribers from the provider.
   *
   * @return array
   *   an array of subscribers.
   */
  public function getSubscribers($list) {
    return [];
  }

  /**
   * Subscribe a user, given a newsletter identifier and email address.
   */
  public function subscribe(NewsletterList $newsletter, QueueItem $item) {
  }

  /**
   * Update user data without modifying subscription status.
   */
  public function update(NewsletterList $newsletter, QueueItem $item) {
  }

  /**
   * Subscribe a user, given a newsletter identifier and email address.
   *
   * Should ignore the request if there is no such subscription.
   */
  public function unsubscribe(NewsletterList $newsletter, QueueItem $item) {
  }

  /**
   * Get additional data for this subscription and a unique fingerprint.
   *
   * @param \Drupal\campaignion_newsletters\Subscription $subscription
   *   The subscription object.
   * @param mixed|null $old_data
   *   Data from an existing queue item or NULL if there is none.
   *
   * @return array
   *   An array containing some data object and a fingerprint:
   *   array($data, $fingerprint).
   *   - The $data is passed as $data parameter of subscribe() during
   *     cron runs.
   *   - The $fingerprint must be an sha1-hash. Usually it's a hash
   *     of some subset of $data.
   */
  public function data(Subscription $subscription, $old_data) {
    // Use sha1("logCRM") as dummy value.
    return [[], '5da0376863c6278e3c8804449bc1814ca4c4217b'];
  }

  /**
   * Get a provider polling object if this provider uses polling.
   *
   * @return \Drupal\campaignion_newsletters\PollingInterface|null
   *   A polling object or NULL if the provider doesn’t implement polling.
   */
  public function polling() {
    return NULL;
  }

}
