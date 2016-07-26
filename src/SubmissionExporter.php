<?php

namespace Drupal\campaignion_logcrm;

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

  public function data($submission) {
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
    return $data;
  }
}
