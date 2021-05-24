<?php
/**
 * @file
 * Contains \Drupal\migrate_custom\Plugin\migrate\source\User.
 */

namespace Drupal\cls_migrate\Plugin\migrate\source;

use Drupal\paragraphs\Plugin\migrate\source\d7\ParagraphsItemRevision;

/**
 * Extract inspection paragraphs from Drupal 7 database.
 *
 * @MigrateSource(
 *   id = "cls_d7_paragraph_inspection",
 *   source_module = "paragraphs",
 * )
 */
class Inspection extends ParagraphsItemRevision {


  /**
   * {@inheritdoc}
   */
  public function getIds() {
    $item_id_mapping = ['item_id' => [
      'type' => 'integer',
      'alias' => 'p',
    ]];

    $ids = parent::getIds();

//    $ids = $item_id_mapping + $ids;
//    dpm($ids, __FILE__ . ":" . __LINE__);

    return $ids;
  }


}
