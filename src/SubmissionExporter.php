<?php

namespace Drupal\campaignion_logcrm;

class SubmissionExporter {
  protected $loader;

  public function __construct($loader) {
    $this->loader = $loader;
  }

  public function actionData($submission) {
    return [
      'uuid' => $submission->node->uuid,
      'title' => $submission->node->title,
      'needs_confirmation' => $submission->webform->needsConfirmation(),
      'type' => $submission->node->type,
      'type_title' => \node_type_get_name($submission->node),
    ];
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
