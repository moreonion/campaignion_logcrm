<?php

/**
 * @file
 * Document hooks invoked by this module.
 *
 * Code in this file is only for documentation purposes. It's never executed.
 */

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
