<?php

namespace Drupal\cls_migrate\Plugin\migrate\source;

use DateTime;
use Drupal\migrate\Row;
use Drupal\node\Plugin\migrate\source\d7\NodeComplete;
use PDO;

/**
 * See https://thinktandem.io/blog/2018/03/30/migrating-drupal-7-organic-groups-to-drupal-8-group/
 */

/**
 * Extends the d7_node_complete source plugin so we can grab OG info.
 *
 * @MigrateSource(
 *   id = "cls_partner_set",
 *   source_module = "node",
 * )
 */
class PartnerSet extends NodeComplete {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = parent::query();
    $query->orderBy('nr.timestamp', 'ASC');
    return $query;
  }

  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    // Grab our uid and grab the Group ID from the D7 OG tables.
    $etid = $row->getSourceProperty('nid');
    $entity_type = 'node';
    $changed = $row->getSourceProperty('changed');

    $query = $this->select('og_membership', 'og')
      ->fields('og', ['gid']);
    $query->condition('etid', $etid)->condition('entity_type', $entity_type);
    $gids = $query->execute()->fetchAll(PDO::FETCH_ASSOC);

    // Set the property to use for the cls_node_complete_partner_set.yml entity reference field.
    $row->setSourceProperty('gids', $gids);
    // Set the property to use in the destination.
    $row->setDestinationProperty('gids', $gids);


    // Add cf_dams_node_calc_fields whose timestamp is equal or greater than this revision, but less than the next revision.
    // This query finds the next node revision and its timestamp.
    $query = $this->select('node_revision', 'nr');
    $query->condition('nr.timestamp', $changed, ">");
    $query->condition('nr.nid', $etid);
    $query->orderBy('timestamp', 'ASC');
    $query->range(0, 1);
    $query->fields('nr', ['vid', 'timestamp']);
    $next_revision = $query->execute()->fetch(PDO::FETCH_ASSOC);

    // Find the calculated fields for this revision
    $query = $this->select('cf_dams_node_calc_fields', 'cf');
    $query->addField('cf', 'field_name', 'type');
    $query->addField('cf', 'format', 'format');
    $query->addField('cf', 'timestamp', 'timestamp');
    $query->addField('cf', 'uid', 'author_uid');
    $query->addField('cf', 'data', 'data');
    $query->condition('nid', $etid);
    $query->condition('cf.timestamp', $changed, '>=');

    if(!empty($next_revision['timestamp'])) {
      $query->condition('cf.timestamp', $next_revision['timestamp'], '<');
    }
    $calc_data = $query->execute()->fetchAll( PDO::FETCH_ASSOC);

    $json_data = [];
    foreach($calc_data as $data) {
      $type = $data['type'];
      $data['date_time'] = date(DateTime::ISO8601, $data['timestamp']);
      unset($data['type']);
      unset ($data['timestamp']);
      $json_data[$type][] = ['json' => json_encode($data)];
    }
    foreach($json_data as $field_name => $field_calc_data) {
      $row->setSourceProperty($field_name, $field_calc_data);
      $row->setDestinationProperty($field_name, $field_calc_data);
    }
    return parent::prepareRow($row);
  }

}
