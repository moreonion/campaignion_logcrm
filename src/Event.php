<?php

namespace Drupal\campaignion_logcrm;

use \Drupal\little_helpers\Webform\Submission;

class Event {
  protected $date;
  protected $data;

  public static function fromSubmission(Submission $submission) {
    $data = [];
    foreach ($submission->node->webform['components'] as $cid => $component) {
      if ($value = $submission->valueByCid($cid)) {
        $data[$component['form_key']] = $submission->valueByCid($cid);
      }
    }
    $data['uuid'] = $submission->uuid;
    return new static($submission->submitted, $data);
  }

  public static function fromPayment(\Payment $payment) {
    $submission_obj = $payment->contextObj->getSubmission();
    $data['uuid'] = $submission_obj->uuid;
    $node = $submission_obj->node;
    $data['action']['uuid'] = $node->uuid;
    $data['action']['title'] = $node->title;
    $data['pid'] = $payment->pid;
    $status = $payment->getStatus();
    $data['currency_code'] = $payment->currency_code;
    $data['total_amount'] = $payment->totalAmount(TRUE);
    $data['status'] = $status->status;
    $data['method_specific'] = $payment->method->title_specific;
    $data['method_generic'] = $payment->method->title_generic;
    $data['controller'] = $payment->method->controller->name;
    return new static($status->created, $data);
  }

  public function __construct($date = NULL, $data = []) {
    $this->date = $date;
    $this->data = $data;
  }

  public function toArray() {
    $d = $this->date ?: time();
    return $this->data + ['date' => date('c', $d)];
  }
}
