<?php

namespace Drupal\car_partners;

use Drupal;
use Drupal\Core\Entity\EntityInterface;
use Drupal\group\Entity\Group;

class PartnerRelations {

  /**
   * Adds/updates/removes partner group content relation for digital objects
   * based on object_identifier.
   *
   * Looks to see if the node being saved is a digital object with an object
   * identifier in its metadata. If so, it extracts the marc code from the object
   * identifier, and attempts to find a partner with a matching marc code. If
   * found, it first looks to see if this object is associated with any other
   * partners and removes it from them. Finally, it adds this object as content
   * to the partner if it is not already.
   *
   * @param  \Drupal\Core\Entity\EntityInterface  $node
   *
   */
  public static function ManagePartnerRelations(EntityInterface $node) {
    if ($node->bundle() == 'digital_object') {
      $md = $node->get('field_descriptive_metadata')->getValue();
      if (is_array($md) && !empty($md[0]['value'])) {
        $data = json_decode($md[0]['value']);
        $marc_code = NULL;
        if (!empty($data->obj_marc)) {
          $marc_code = $data->obj_marc;
        }
        elseif (!empty($data->obj_object_identifier)) {
          preg_match('/^(?<marc>[a-z]+)_\d*$/', $data->obj_object_identifier,
            $matches);
          if (!empty($matches['marc'])) {
            $marc_code = $matches['marc'];
          }
        }
        $pluginId = 'group_node:' . $node->getType();

        // TODO: I don't have time to figure out how to get the group content type without having a group loaded first.
        // It hashes the name if it exceeds EntityTypeInterface::BUNDLE_MAX_LENGTH (32) characters.
        // The hashed portion of the id uses this function which would always return the same value:
        // \Drupal\group\Plugin\GroupContentEnablerBase::getContentTypeConfigId
        // Here's how you get it *if you have a group loaded*, but we don't at the moment and may not ever:
        // $group->getGroupType()->getContentPlugin('group_node:'.$node->bundle())->getContentTypeConfigId()
        $group_content_type_config_id = 'group_content_type_7e63bbcf402cc';

        // See if it's a member of any groups already;
        $query = \Drupal::database()
          ->select('group_content_field_data', 'gc')
          ->condition('gc.entity_id', $node->id(), '=')
          ->condition('gc.type', $group_content_type_config_id)
          ->fields('gc', ['gid']);
        $existing_gids = $query->execute()->fetchCol();

        // If
        if ($marc_code) {
          $gid = Drupal::database()->select('group__field_marc_code', 'marc')
            ->condition('field_marc_code_value', $marc_code)
            ->fields('marc', array('entity_id'))
            ->execute()
            ->fetchField();

          if ($gid) {
            $group = Group::load($gid);
            if ($group) {
              // Remove this gid from existing_gids, we don't want to remove it!
              $existing_gids = array_diff($existing_gids, [$gid]);

              // Create the relation if it doesn't exist.
              $relation = $group->getContentByEntityId($pluginId, $node->id());
              if (!$relation) {
                $group->addContent($node, $pluginId);
                $msg = t('Added @title (nid: @nid) to group @group_title (gid: @gid)',
                  [
                    '@title' => $node->label(),
                    '@nid' => $node->id(),
                    '@group_title' => $group->label(),
                    '@gid' => $group->id(),
                  ]);
                Drupal::messenger()->addMessage($msg);
                Drupal::logger('car_partners')->notice($msg);
              }
            }
          }
          else {
            $msg = t('No partner found for @title (nid: @nid) with marc code @marc',
              [
                '@title' => $node->label(),
                '@nid' => $node->id(),
                '@marc' => $marc_code,
              ]);
            Drupal::messenger()->addMessage($msg);
            Drupal::logger('car_partners')->notice($msg);
          }
        }
        // Delete other pre-existing gids. It should only belong to one partner.
        if (!empty($existing_gids)) {
          $other_groups = Group::loadMultiple($existing_gids);
          foreach ($other_groups as $other_group) {
            foreach ($other_group->getContent($pluginId,
              ['entity_id' => $node->id()]) as $group_content) {
              $group_content->delete();
              $msg = t('Removed @title (nid: @nid) from group @group_title (gid: @gid)',
                [
                  '@title' => $node->label(),
                  '@nid' => $node->id(),
                  '@group_title' => $other_group->label(),
                  '@gid' => $other_group->id(),
                ]);
              Drupal::messenger()->addMessage($msg);
              Drupal::logger('car_partners')->notice($msg);
            }
          }
        }
      }
    }
  }
}
