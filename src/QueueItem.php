<?php

namespace Drupal\campaignion_logcrm;

use \Drupal\little_helpers\DB\Model;

class QueueItem extends \Drupal\little_helpers\DB\Model {
  public $type;
  public $id;
  public $created;
  public $locked = 0;
  public $event;

  protected static $table = 'campaignion_logcrm_queue';
  protected static $key = array('type', 'id');
  protected static $values = array('created', 'locked', 'event');
  protected static $serialize = array('event' => TRUE);
  protected static $serial = FALSE;

  public static function load($type, $id) {
    $table = static::$table;
    $keys = array(':type' => $type, ':id' => $id);
    $result = db_query("SELECT * FROM {{$table}} WHERE type=:type AND id=:id", $keys);
    if ($row = $result->fetch()) {
      return new static($row, FALSE);
    }
  }

  public static function byData($data) {
    if ($item = static::load($data['type'], $data['id'])) {
      $item->__construct($data, FALSE);
    }
    else {
      $item = new static($data);
    }
    return $item;
  }

  public static function claimOldest($limit, $time = 600) {
    $table = static::$table;
    $result = db_select(static::$table, 'i')
      ->fields('i')
      ->orderBy('created')
      ->condition('locked', time(), '<')
      ->range(0, $limit)
      ->execute();
    $items = array();
    foreach ($result as $row) {
      $item = new static($row, FALSE);
      $item->claim($time);
      $items[] = $item;
    }
    return $items;
  }

  public function __construct($data = array(), $new = TRUE) {
    parent::__construct($data, $new);
    if (!isset($this->created)) {
      $this->created = time();
    }
  }

  /**
   * Lock this item for $time seconds.
   *
   * @param int $time
   *   Seconds to lock this item for.
   */
  public function claim($time = 600) {
    $this->locked = time() + $time;
    $this->save();
  }

  /**
   * Release the lock on this item.
   */
  public function release() {
    $this->locked = 0;
    $this->save();
  }
}
