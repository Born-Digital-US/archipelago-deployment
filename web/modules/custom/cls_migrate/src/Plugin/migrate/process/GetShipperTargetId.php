<?php

namespace Drupal\cls_migrate\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * Perform custom value transformations.
 *
 * @MigrateProcessPlugin(
 *   id = "get_shipper_id"
 * )
 *
 * To map a shipper entity reference (which may be a vendor or partner) use the following:
 *
 * @code
 * field_ship_from/target_id:
 *     -
 *      plugin: get
 *      source: field_ship_from
 *     -
 *      plugin: get_shipper_id
 * field_ship_from/target_type:
 *     -
 *      plugin: get
 *      source: field_ship_from
 *     -
 *      plugin: get_shipper_type
 * @endcode
 *
 */
class GetShipperTargetId extends ProcessPluginBase {
  /**
   * {@inheritdoc}
   */
  public function transform($sourceid, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    foreach(['migrate_map_cls_node_complete_vendor' => 'node', 'migrate_map_cls_node_complete_partner' => 'group'] as $mmap_table => $destintion_entity_type) {
      $id = \Drupal::database()->select($mmap_table, 't')->fields('t', ['destid1'])->condition('t.sourceid1', $sourceid)->execute()->fetchField();
      if($id > 0) {
        return $id;
      }
    }
    return NULL;
  }
}
