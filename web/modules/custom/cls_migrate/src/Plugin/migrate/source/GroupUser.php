<?php

namespace Drupal\cls_migrate\Plugin\migrate\source;

use Drupal\Component\Utility\Html;
use Drupal\user\Plugin\migrate\source\d7\User;
use Drupal\migrate\Row;
use PDO;

/**
 * See https://thinktandem.io/blog/2018/03/30/migrating-drupal-7-organic-groups-to-drupal-8-group/
 */

/**
 * Extends the D7 User source plugin so we can grab OG info.
 *
 * @MigrateSource(
 *   id = "d7_group_user",
 *   source_module = "user",
 * )
 */
class GroupUser extends User {

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    // Grab our uid and grab the Group ID from the D7 OG tables.
    $uid = $row->getSourceProperty('uid');

    // Grab data from both tables.
    $query = $this->select('og_users_roles', 'our')
      ->fields('our', ['gid']);
    $query->join('og_role', 'ogr', 'ogr.rid = our.rid');
    $query->addField('ogr', 'name', 'role');
    $query->condition('our.uid', $uid) ;
    $og_users_roles = $query->execute()->fetchAllAssoc('gid', PDO::FETCH_ASSOC);
    foreach($og_users_roles as $gid => $membership) {
      $membership['role'] = Html::cleanCssIdentifier($membership['role']);
      $og_users_roles[$gid] = $membership;
    }

    $query = $this->select('og_membership', 'og')
      ->fields('og', ['gid', 'created']);
    $query->addExpression("NULL", 'role');
    $query->condition('etid', $uid)->condition('entity_type', 'user');
    $og_memberships = $query->execute()->fetchAllAssoc('gid', PDO::FETCH_ASSOC);

    // Set our array of values.
    $groups = $og_users_roles + $og_memberships;
    // Copy over created dates.
    foreach($groups as $gid => &$group) {
      if(empty($group['created']) && !empty($og_memberships[$gid]['created'])) {
        $group['created'] = $og_memberships[$gid]['created'];
      }
    }

    // Set the property to use for the user yaml ER field.
    $row->setSourceProperty('groups', $groups);

    // Set the property to use in the custom_user destination.
    $row->setDestinationProperty('groups', $groups);
    return parent::prepareRow($row);
  }



}
