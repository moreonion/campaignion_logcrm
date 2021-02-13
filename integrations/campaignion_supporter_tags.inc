<?php

/**
 * @file
 * Implement campaignion_logcrm hooks on behalf of campaignion_supporter_tags.
 */

/**
 * Implements hook_campaignion_logcrm_event_data_alter().
 */
function campaignion_supporter_tags_campaignion_logcrm_event_data_alter(array &$data, $type, array $context) {
  if ($type == 'form_submission') {
    $submission = $context['submission'];
    $node = $submission->node;
    $data['action']['tags'] = [];
    if (!empty($node->supporter_tags)) {
      $wrapped_node = entity_metadata_wrapper('node', $node);
      foreach ($wrapped_node->supporter_tags->value() as $term) {
        $data['action']['tags'][] = $term->name;
      }
    }
  }
}
