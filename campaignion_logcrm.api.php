<?php

/**
 * @file
 * Document hooks invoked by this module.
 *
 * Code in this file is only for documentation purposes. It's never executed.
 */

/**
 * Allow other modules to modify the event data.
 *
 * @param array $data
 *   Original event data array.
 * @param string $type
 *   The event type.
 * @param array $context
 *   Array of objects used to generate the event data.
 */
function hook_campaignion_logcrm_payment_event_data_alter(array &$data, $type, array $context) {
  if (isset($context['submission'])) {
    $link_options = ['absolute' => TRUE, 'alias' => TRUE];
    $data['_link']['edit'] = url("node/{$submission->node->nid}/submission/{$submission->sid}/edit", $link_options);
  }
}

/**
 * Allow other modules to modify the event data for payment events.
 *
 * @param array $data
 *   Original event data array.
 * @param \Payment $payment
 *   The payment object for which we do generate data.
 */
function hook_campaignion_logcrm_payment_event_data_alter(array &$data, Payment $payment) {
  $data['tax'] = $payment->totalAmount(TRUE) - $payment->totalAmount(FALSE);
}
