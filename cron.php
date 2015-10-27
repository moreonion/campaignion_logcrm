<?php

namespace Drupal\campaignion_logcrm;

/**
 * Send items from the cron queue.
 */
function send_queue() {
  $batchSize = variable_get('campaignion_logcrm_batch_size', 50);
  $items = QueueItem::claimOldest($batchSize);
  $client = Client::fromConfig();

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
