<?php

/**
 * @file
 * Implement campaignion_logcrm hooks on behalf of campaignion_supporter_tags.
 */

/**
 * Implements hook_campaignion_logcrm_event_data_alter().
 */
function campaignion_source_tags_campaignion_logcrm_event_data_alter(array &$data, $type, array $context) {
  if ($type == 'form_submission') {
    $submission = $context['submission'];
    $node = $submission->node;
    $tags_map = _campaignion_source_tags_map();
    foreach (array_keys($tags_map) as $property) {
      $data['action']["{$property}_tags"] = [];
    }
    if (!empty($node->supporter_tags)) {
      $wrapped_node = entity_metadata_wrapper('node', $node);
      $uuid_to_property = array_flip($tags_map);
      foreach ($wrapped_node->supporter_tags->value() as $term) {
        // Add each tag with a matching parent to its tags-array.
        foreach (taxonomy_get_parents($term->tid) as $pterm) {
          if ($property = $uuid_to_property[$pterm->uuid] ?? NULL) {
            $data['action']["{$property}_tags"][] = $term->name;
            break;
          }
        }
      }
    }
  }
}
