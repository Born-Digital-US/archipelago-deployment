<?php

namespace Drupal\webform_car_ip_data_for_vendor\Plugin\WebformElement;

use Drupal\Core\Form\FormStateInterface;
use Drupal\webform\Plugin\WebformElement\WebformCompositeBase;
use Drupal\webform\WebformSubmissionInterface;

/**
 * Provides a 'car_related_material' complex element.
 *
 * @WebformElement(
 *   id = "car_related_material",
 *   label = @Translation("Related Materials"),
 *   description = @Translation("Provides a composite webform element for related materials metadata."),
 *   category = @Translation("CAR Elements"),
 *   multiline = TRUE,
 *   composite = TRUE,
 *   states_wrapper = TRUE,
 * )
 *
 * @see \Drupal\webform_car_ip_data_for_vendor\Element\CarRelatedMaterial
 * @see \Drupal\webform\Plugin\WebformElement\WebformCompositeBase
 * @see \Drupal\webform\Plugin\WebformElementBase
 * @see \Drupal\webform\Plugin\WebformElementInterface
 * @see \Drupal\webform\Annotation\WebformElement
 */
class CarRelatedMaterial extends WebformCompositeBase {

  /**
   * {@inheritdoc}
   */
  protected function formatHtmlItemValue(
    array $element,
    WebformSubmissionInterface $webform_submission,
    array $options = []
  ) {
    return $this->formatTextItemValue($element, $webform_submission, $options);
  }

  /**
   * {@inheritdoc}
   */
  protected function formatTextItemValue(
    array $element,
    WebformSubmissionInterface $webform_submission,
    array $options = []
  ) {
    $value = $this->getValue($element, $webform_submission, $options);
    return $value;

    // TODO: This is unfinished!
    $lines = [];
//    $lines[] = ($value['ip_item_part_label'] ? $value['ip_item_part_label'] : '') .
//      ($value['ip_call_number'] ? ' ' . $value['ip_call_number'] : '') .
//      ($value['ip_temporary_id'] ? ' ' . $value['ip_temporary_id'] : '') .
//      ($value['ip_relation_type'] ? ' ' . $value['ip_relation_type'] : '') .
//      ($value['ip_price_bundle'] ? ' (' . $value['ip_price_bundle'] . ')' : '');
    return $lines;
  }
}
