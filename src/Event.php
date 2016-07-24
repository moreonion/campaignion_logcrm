<?php

namespace Drupal\campaignion_logcrm;

use \Drupal\little_helpers\Webform\Submission;

class Event {
  protected $type;
  protected $date;
  protected $data;

  public static function fromSubmission(Submission $submission, $type = 'form_submission') {
    $data = Loader::instance()->submissionExporter()->data($submission);
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
    $data['action'] = Loader::instance()->submissionExporter()->actionData($submission_obj);
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
