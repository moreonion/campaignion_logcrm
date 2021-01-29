<?php

namespace Drupal\campaignion_logcrm;

use Drupal\campaignion_newsletters\NewsletterList;
use Drupal\little_helpers\Webform\Submission;

/**
 * Exporter for opt-in data in a submission.
 */
class OptInExporter {

  /**
   * Mapping of list_id (DB primary key) to global list identifier.
   *
   * @var string[]
   */
  protected $listIdentifiers;

  /**
   * The user’s IP-address.
   *
   * @var string
   */
  protected $ipAddress;

  /**
   * Map lists to their global identifiers.
   *
   * @param \Drupal\campaignion_newsletters\NewsletterList[] $lists
   *   The lists which’s identifers to get, keyed by list ID.
   *
   * @return string[]
   *   Identifiers keyed by list ID. The identifier consists of a part that
   *   identifies the provider (eg. mailchimp, cleverreach, …) and the
   *   provider’s list identifier.
   */
  public static function listMap(array $lists = NULL) {
    $lists = $lists ?? NewsletterList::listAll();
    return array_map(function ($list) {
      $source = strtolower(explode('-', $list->source, 2)[0]);
      if ($source == 'optivo') {
        $source = 'episerver';
      }
      return "{$source}:{$list->identifier}";
    }, $lists);
  }

  /**
   * Create a new instance by reading the global config.
   */
  public static function fromConfig() {
    return new static(static::listMap(), ip_address());
  }

  /**
   * Create a new exporter instance.
   *
   * @param string[] $list_identifiers
   *   Mapping of internal list IDs to global identifiers.
   * @param string $ip_address
   *   The user’s IP-address.
   */
  public function __construct(array $list_identifiers, string $ip_address) {
    $this->listIdentifiers = $list_identifiers;
    $this->ipAddress = $ip_address;
  }

  /**
   * Get submission data from a submission.
   *
   * @param \Drupal\little_helpers\Webform\Submission $submission
   *   The submission which’s optins to export.
   *
   * @return array
   *   Array of opt-in data keyed by component ID.
   *
   * @see campaignion_opt_in_webform_submission_load()
   */
  public function export(Submission $submission) {
    if (empty($submission->opt_in)) {
      return [];
    }
    $opt_ins = $submission->opt_in->values();
    $list_identifiers = $this->listIdentifiers;
    foreach ($opt_ins as $cid => &$opt_in) {
      if ($opt_in['channel'] == 'email') {
        $component = $submission->node->webform['components'][$cid];
        $opt_in['unsubscribe_all'] = !empty($component['extra']['optout_all_lists']);
        $opt_in['unsubscribe_unknown'] = variable_get_value('campaignion_newsletters_unsubscribe_unknown');
        $opt_in['lists'] = array_map(function ($list_id) use ($list_identifiers) {
          return $list_identifiers[$list_id];
        }, $component['extra']['lists']);
        $opt_in['ip_address'] = $this->ipAddress;
      }
    }
    return $opt_ins;
  }

}
