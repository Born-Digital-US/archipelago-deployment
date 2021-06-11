<?php

/**
 * See https://thinktandem.io/blog/2018/03/30/migrating-drupal-7-organic-groups-to-drupal-8-group/
 */

namespace Drupal\cls_migrate;

use Drupal\group\Entity\Group;
use Drupal\user\Entity\User;

class ClsMigrateGroup {

  /**
   * @var object
   */
  protected $user;

  /**
   * DgreatGroup constructor.
   *
   * @param  \Drupal\user\Entity\User  $entity
   */
  public function __construct(User $user) {
    $this->user = $user;
  }

  /**
   * Adds a user to a group specified by an entity relation field on the user profile.
   *
   * @param $field
   *   The field we are using as a reference for the group.
   *
   * @return bool
   */
  public function addUserToGroup($field) {
    if ($this->user->hasField($field)) {
      $group_ids = $this->user->get($field)->getValue();
      // Let's go through Each Group and add users.
      foreach ($group_ids as $gid) {
        if (isset($gid['target_id'])) {

          $group = Group::load($gid['target_id']);

          if ($group !== NULL) {
            $group->addMember($this->user);
          }
        }
      }
    }

    // Fail safe return
    return FALSE;
  }
}
