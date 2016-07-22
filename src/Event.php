<?php

namespace Drupal\campaignion_logcrm;

use \Drupal\little_helpers\Webform\Submission;

class Event {
  protected $type;
  protected $date;
  protected $data;

  public static function actionData(Submission $submission) {
    return [
      'uuid' => $submission->node->uuid,
      'title' => $submission->node->title,
      'needs_confirmation' => $submission->webform->needsConfirmation(),
      'type' => $submission->node->type,
      'type_title' => node_type_get_name($submission->node),
    ];
  }

  public static function fromSubmission(Submission $submission, $type = 'form_submission') {
    $data = [];
    foreach ($submission->node->webform['components'] as $cid => $component) {
      if ($value = $submission->valueByCid($cid)) {
        $data[$component['form_key']] = $submission->valueByCid($cid);
      }
      $data['action'] = static::actionData($submission);
    }
    $data['uuid'] = $submission->uuid;
    return new static($type, $submission->submitted, $data);
  }

  public static function fromSubmissionConfirmation(Submission $submission, $type = 'form_submission_confirmed') {
    return new static($type, time(), [
      'uuid' => $submission->uuid,
    ]);
  }

  public static function fromPayment(\Payment $payment, $type = 'payment_success') {
    $submission_obj = $payment->contextObj->getSubmission();
    $data['uuid'] = $submission_obj->uuid;
    $data['action'] = static::actionData($submission_obj);
    $data['pid'] = $payment->pid;
    $status = $payment->getStatus();
    $data['currency_code'] = $payment->currency_code;
    $data['total_amount'] = $payment->totalAmount(TRUE);
    $data['status'] = $status->status;
    $data['method_specific'] = $payment->method->title_specific;
    $data['method_generic'] = $payment->method->title_generic;
    $data['controller'] = $payment->method->controller->name;
    return new static($type, $status->created, $data);
  }

  public function __construct($type, $date = NULL, $data = []) {
    $this->type = $type;
    $this->date = $date;
    $this->data = $data;
  }

  public function toArray() {
    $d = $this->date ?: time();
    return [
      'type' => $this->type,
      'date' => date('c', $d),
    ] + $this->data;
  }
}
