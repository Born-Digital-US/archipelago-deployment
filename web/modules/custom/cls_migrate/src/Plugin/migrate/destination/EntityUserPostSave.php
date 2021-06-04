<?php

namespace Drupal\cls_migrate\Plugin\migrate\destination;

use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\FieldTypePluginManagerInterface;
use Drupal\Core\Password\PasswordInterface;
use Drupal\migrate\Annotation\MigrateDestination;
use Drupal\migrate\Row;
use Drupal\group\Entity\Group;
use Drupal\user\Entity\User as DrupalUser;
use Drupal\user\Plugin\migrate\destination\EntityUser;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\Plugin\migrate\User;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\migrate\Plugin\MigrationInterface;

/**
 * See https://thinktandem.io/blog/2018/03/30/migrating-drupal-7-organic-groups-to-drupal-8-group/
 */

/**
 * @MigrateDestination(
 *   id = "cls_group_user",
 * )
 */
class EntityUserPostSave extends EntityUser {

  /**
   * The og group ids array we passed through.
   *
   * @var array
   */
  private $groups;
  private $idmap;

  public function __construct(array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration, EntityStorageInterface $storage, array $bundles, EntityFieldManagerInterface $entity_field_manager, FieldTypePluginManagerInterface $field_type_manager, PasswordInterface $password) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration, $storage, $bundles, $entity_field_manager, $field_type_manager, $password);
    $this->idmap = \Drupal::service('plugin.manager.migration')->createInstance('cls_node_complete_partner')->getIdMap();
  }


  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration = NULL) {
    // Basically we need to "trick" the plugin_id to use the right entity type.
//    $entity_type = static::getEntityTypeId($plugin_id);
    $entity_type = 'user';
    return new static(
      $configuration,
      'entity:user',
      $plugin_definition,
      $migration,
      $container->get('entity_type.manager')->getStorage($entity_type),
      array_keys($container->get('entity_type.bundle.info')->getBundleInfo($entity_type)),
      $container->get('entity.manager'),
      $container->get('plugin.manager.field.field_type'),
      $container->get('password')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function import(Row $row, array $old_destination_id_values = []) {
    // Set this so we can process in the save method.
    $this->groups = $row->getDestinationProperty('groups');
    return parent::import($row, $old_destination_id_values);
  }

  /**
   * {@inheritdoc}
   */
  protected function save(ContentEntityInterface $entity, array $old_destination_id_values = []) {
    // We need to pull the parts of the parents into this class.
    // If we don't, we get a failed migration.

    // From EntityUser::save()
    // Do not overwrite the root account password.
    if ($entity->id() != 1) {
      // Set the pre_hashed password so that the PasswordItem field does not hash
      // already hashed passwords. If the md5_passwords configuration option is
      // set we need to rehash the password and prefix with a U.
      // @see \Drupal\Core\Field\Plugin\Field\FieldType\PasswordItem::preSave()
      $entity->pass->pre_hashed = TRUE;
      if (isset($this->configuration['md5_passwords'])) {
        $entity->pass->value = 'U' . $this->password->hash($entity->pass->value);
      }
    }

    // Save the entity as in EntityContentBase::save().
    $entity->save();

    // Let's go through Each Group and add users.
    foreach ($this->groups as $membership) {

      if (!empty($membership['gid'])) {
        $destination_ids = $this->idmap->lookupDestinationIds([$membership['gid']]);
        if(!empty($destination_ids[0][0])) {
          $group = Group::load($destination_ids[0][0]);
          if ($group !== NULL) {
            $group->addMember($entity, ['group_roles' => [$membership['role']]]);
            // Manually set in the created date. Could not figure out how
            // to do this using the addMember $values argument.
            // Note that D7 og_membership does not provide a changed date, so we don't try to set that.
            // $group->addMember($entity, ['group_roles' => [$membership['role'], 'created' => $membership['created']]]);
            \Drupal::database()
              ->update('group_content_field_data')
              ->fields(['created' => $membership['created']])
              ->condition('gid', $membership['gid'])
              ->condition('type', 'partner-group_membership')
              ->condition('entity_id', $entity->id())
              ->execute();
          }
        }
      }
    }

    // return the entity ids as in EntityContentBase::save().
    return [$entity->id()];
  }

  /**
   * Delete group memberships on rollback.
   *
   * {@inheritdoc}
   */
  public function rollback(array $destination_identifier) {
    $entity = $this->storage->load(reset($destination_identifier));

    // Delete user's group memberships.
    if ($entity && $entity instanceof DrupalUser) {
      group_user_delete($entity);
    }

    // Execute the normal rollback from here.
    parent::rollback($destination_identifier);
  }
}
