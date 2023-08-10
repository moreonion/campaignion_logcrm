<?php

namespace Drupal\campaignion_logcrm;

/**
 * Manager of the task queue.
 */
class Queue {

  /**
   * Create and save a new queue item.
   */
  public function addItem(string $type, $id, Event $event) : void {
    QueueItem::byData([
      'type' => $type,
      'id' => $id,
      'event' => $event,
    ])->save();
  }

}
