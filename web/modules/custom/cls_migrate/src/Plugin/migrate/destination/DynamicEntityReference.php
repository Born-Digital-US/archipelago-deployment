<?php

namespace Drupal\cls_migrate\Plugin\migrate\destination;

use Drupal\Core\Entity\Entity;
use Drupal\migrate\Plugin\migrate\destination\EntityContentBase;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\Row;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @MigrateDestination(
 *   id = "der"
 * )
 */
class DynamicEntityReference extends EntityContentBase {

  /** @var string $entityType */
  public static $entityType = 'node';

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration = NULL) {
    return parent::create($container, $configuration, 'entity:' . static::$entityType, $plugin_definition, $migration);
  }

  /**
   * {@inheritdoc}
   */
  public function import(Row $row, array $old_destination_id_values = array()) {
    // Add custom code here, if needed.
    dpm(get_defined_vars(), __FILE__ . ":" . __LINE__);
    return parent::import($row, $old_destination_id_values);
  }

}

// \Drupal\field\Entity\FieldConfig::loadByName('entity_type', 'bundle', 'field_name')->delete();
// \Drupal\field\Entity\FieldStorageConfig::loadByName('node', 'field_additional_technical_notes')->delete();
$delete_fields = [
  'field_ts_sent4digit_date' => 'node',
  'field_ts_ship_from_partner' => 'node',
  'field_ts_ship_to_vendor' => 'node',
  'field_ts_stream' => 'node',
  'field_ts_vendor_job_id' => 'node',
  'field_type' => 'node',
  'field_vendor_notes' => 'node',
  'field_worldcat_url' => 'node',
  'group_access' => 'node',
  'group_content_access' => 'node',
  'group_group' => 'node',
  'og_group_ref' => 'node',
  'field_code' => 'taxonomy_term',
  'field_field_sub_part_type_abbr' => 'taxonomy_term',
  'field_pbcore_asset_type_ur' => 'taxonomy_term',
  'field_stream_content_type' => 'taxonomy_term',
  'field_sub_part_type_abbr' => 'taxonomy_term',
  'field_term_abbreviation' => 'taxonomy_term',
  'og_user_node' => 'user',
];
foreach($delete_fields as $delete_field => $entity_type) {
  try {
    \Drupal\field\Entity\FieldStorageConfig::loadByName($entity_type, $delete_field)->delete();
    \Drupal::database()->delete('migrate_map_cls_d7_field')->condition('sourceid1', $delete_field)->condition('sourceid2', $entity_type)->execute();
    dpm($delete_field . ":" . $entity_type . " deleted");
  }
  catch(Exception $e) {
    dpm("Could not delete" . $delete_field . ":" . $entity_type);
  }
}
