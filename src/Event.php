<?php

namespace Drupal\campaignion_logcrm;

use Drupal\little_helpers\Services\Container;
use Drupal\little_helpers\Webform\Submission;

class Event {
  protected $type;
  protected $date;
  protected $data;

  public static function fromSubmission(Submission $submission, $type = 'form_submission') {
    $data = Loader::instance()->submissionExporter()->data($submission);
    $context['submission'] = $submission;
    return static::fromData($type, $submission->submitted, $data, $context);
  }

  public static function fromSubmissionConfirmation(Submission $submission, $type = 'form_submission_confirmed') {
    $data = ['uuid' => $submission->uuid];
    $context['submission'] = $submission;
    return static::fromData($type, time(), $data, $context);
  }

  /**
   * Create new event by passing data and context.
   *
   * @param string $type
   *   Event type.
   * @param int $time
   *   The point in time the event was triggerd.
   * @param array $data
   *   The generated event data.
   * @param array $context
   *   An array of data used as source for generating the event data.
   */
  public static function fromData($type, $time, array $data, array $context) {
    drupal_alter('campaignion_logcrm_event_data', $data, $type, $context);
    return new static($type, $time, $data);
  }

  public static function fromPayment(\Payment $payment, $type = 'payment_success') {
    $exporter = Container::get()->loadService('campaignion_logcrm.payment_exporter');
    $submission_obj = $payment->contextObj->getSubmission();
    $data['uuid'] = $submission_obj->uuid;
    $data['action'] = Loader::instance()->submissionExporter()->actionData($submission_obj);
    $data += $exporter->toJson($payment);

    // Let other modules alter the data.
    drupal_alter('campaignion_logcrm_payment_event_data', $data, $payment);
    $context['payment'] = $payment;
    $context['submission'] = $submission_obj;
    return static::fromData($type, $payment->getStatus()->created, $data, $context);
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
