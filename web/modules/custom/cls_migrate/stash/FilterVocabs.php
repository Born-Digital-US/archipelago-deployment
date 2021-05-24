<?php

namespace Drupal\cls_migrate\Plugin\migrate\process;

use Drupal\migrate\Annotation\MigrateProcessPlugin;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\migrate\MigrateSkipRowException;

/**
 * Skips processing the current row when a destination value does not exist.
 *
 * The skip_row_if_not_exist process plugin checks whether a value exists. If
 * the value exists, it is returned. Otherwise, a MigrateSkipRowException is
 * thrown.
 *
 * Available configuration keys:
 * - entity: The destination entity to check for.
 * - property: The destination entity property to check for.
 * - message: (optional) A message to be logged in the {migrate_message_*}
 * table
 *   for this row. If not set, nothing is logged in the message table.
 *
 * Example:
 *  Do not import comments for migrated nodes that do not exist any more at the
 *  destination.
 *
 * @code
 *  process:
 *    entity_id:
 *    -
 *      plugin: migration_lookup
 *      migration:
 *        - d6_node
 *      source: nid
 *    -
 *      # Check if a node exists in the destination database.
 *      plugin: filter_vocabs
 *      entity: taxonomy_term
 *      property: vid
 *      message: 'Commented entity not found.'
 * @endcode
 *
 * This will return the node id if it exists. Otherwise, the row will be
 * skipped and the message "Commented entity not found." will be logged in the
 * message table.
 *
 * @see \Drupal\migrate\Plugin\MigrateProcessInterface
 *
 * @MigrateProcessPlugin(
 *   id = "filter_vocabs",
 *   handle_multiples = TRUE
 * )
 */
class SkipRowIfNotExist extends ProcessPluginBase {

  protected $entity = 'node';

  protected $property = 'nid';

  /**
   * {@inheritdoc}
   */
  function __construct($configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    if (!empty($configuration['entity'])) {
      $this->entity = $configuration['entity'];
    }

    if (!empty($configuration['property'])) {
      $this->property = $configuration['property'];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function transform(
    $value,
    MigrateExecutableInterface $migrate_executable,
    Row $row,
    $destination_property
  ) {
    $count = \Drupal::entityQuery($this->entity)
      ->condition($this->property, $value)
      ->accessCheck(FALSE)
      ->count()
      ->execute();

    if (!$count) {
      $message = isset($this->configuration['message']) ? $this->configuration['message'] : '';
      throw new MigrateSkipRowException($message);
    }

    return $value;
  }

}
