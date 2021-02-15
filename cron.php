<?php

namespace Drupal\campaignion_logcrm;

use Drupal\little_helpers\Services\Container;

/**
 * Send items from the cron queue.
 */
function send_queue() {
  $batchSize = variable_get('campaignion_logcrm_batch_size', 50);
  $items = QueueItem::claimOldest($batchSize);
  $client = Container::get()->loadService('campaignion_logcrm.client');

  foreach ($items as $item) {
    try {
      $client->sendEvent($item->event);
      $item->delete();
    }
    catch (ApiError $e) {
      $e->log();
    }
  }
}
