<?php

namespace Drupal\campaignion_logcrm;

use Drupal\little_helpers\Services\Container;
use Drupal\little_helpers\Webform\Submission;

/**
 * Generator for logCRM event data from a webform submission.
 */
class SubmissionExporter {

  /**
   * A plugin loader for component export plugins.
   *
   * @var \Drupal\little_helpers\ServicesContainer
   */
  protected $loader;

  /**
   * An opt-in exporter instance.
   *
   * @var \Drupal\campaignion_logcrm\OptInExporter
   */
  protected $optInExporter;

  /**
   * Create a new exporter.
   */
  public function __construct(Container $loader, OptInExporter $opt_in_exporter) {
    $this->loader = $loader;
    $this->optInExporter = $opt_in_exporter;
  }

  /**
   * Generate the action data.
   *
   * @param \Drupal\little_helpers\Webform\Submission $submission
   *   Submission that should be exported.
   *
   * @return mixed
   *   JSON serializable data representing the submission.
   */
  public function actionData(Submission $submission) {
    $node = $submission->node;
    $data = [
      'uuid' => $node->uuid,
      'title' => $node->title,
      'needs_confirmation' => $submission->webform->needsConfirmation(),
      'type' => $node->type,
      'type_title' => \node_type_get_name($node),
    ];
    if ($items = field_get_items('node', $node, 'field_reference_to_campaign')) {
      if ($campaign = node_load($items[0]['nid'])) {
        $data += [
          'campaign_uuid' => $campaign->uuid,
          'campaign_title' => $campaign->title,
        ];
      }
    }
    return $data;
  }

  /**
   * Get exportable data for a submission.
   *
   * @param \Drupal\little_helpers\Webform\Submission $submission
   *   Submission that should be exported.
   *
   * @return mixed
   *   JSON serializable data representing the submission.
   */
  public function data(Submission $submission) {
    $data['data'] = [];
    foreach ($submission->node->webform['components'] as $cid => $component) {
      $exporter = $this->loader->loadService($component['type'], FALSE) ?: $this->loader->loadService('verbatim');
      if ($value = $exporter->value($component, $submission)) {
        $data[$component['form_key']] = $value;
        $data['data'][$component['form_key']] = $value;
      }
    }
    $data['_submitted_at'] = date(DATE_ISO8601, $submission->submitted);
    $data['_completed_at'] = isset($submission->completed) ? date(DATE_ISO8601, $submission->completed) : NULL;
    $data['action'] = $this->actionData($submission);
    $data['uuid'] = $submission->uuid;
    $data['is_draft'] = (bool) $submission->is_draft;
    if ($submission->tracking) {
      $data['tracking'] = $submission->tracking;
    }
    $link_options = ['absolute' => TRUE, 'alias' => FALSE];
    $nid = $submission->node->nid;
    $data['_links'] = [
      'action_pretty_url' => url("node/$nid", ['alias' => TRUE] + $link_options),
      'action' => url("node/$nid", $link_options),
      'submission' => url("node/$nid/submission/{$submission->sid}", $link_options),
    ];
    $data['_optins'] = $this->optInExporter->export($submission);
    foreach (['submitted_at', 'completed_at', 'links', 'optins'] as $key) {
      if (!isset($data[$key]) && isset($data["_$key"])) {
        $data[$key] = $data["_$key"];
      }
    }
    return $data;
  }

}
