<?php

namespace Drupal\campaignion_logcrm;

use Drupal\little_helpers\Webform\Submission;

class SubmissionExporter {
  protected $loader;

  public function __construct($loader) {
    $this->loader = $loader;
  }

  public function actionData($submission) {
    $node = $submission->node;
    $data = [
      'uuid' => $node->uuid,
      'title' => $node->title,
      'needs_confirmation' => $submission->webform->needsConfirmation(),
      'type' => $node->type,
      'type_title' => \node_type_get_name($node),
    ];
    if ($items = field_get_items('node', $node, 'field_reference_to_campaign')) {
      if($campaign = node_load($items[0]['nid'])) {
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
    $data = [];
    foreach ($submission->node->webform['components'] as $cid => $component) {
      $values = $submission->valuesByCid($cid);
      $exporter = $this->loader->componentExporter($component['type']);
      if ($value = $exporter->value($component, $values)) {
        $data[$component['form_key']] = $value;
      }
    }
    $data['action'] = $this->actionData($submission);
    $data['uuid'] = $submission->uuid;
    $data['is_draft'] = (bool) $submission->is_draft;
    if ($submission->tracking) {
      $data['tracking'] = $submission->tracking;
    }
    return $data;
  }

}
