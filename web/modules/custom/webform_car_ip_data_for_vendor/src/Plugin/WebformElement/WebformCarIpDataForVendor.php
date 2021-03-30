<?php

namespace Drupal\webform_car_ip_data_for_vendor\Plugin\WebformElement;

use Drupal\Core\Form\FormStateInterface;
use Drupal\webform\Plugin\WebformElement\WebformCompositeBase;
use Drupal\webform\WebformSubmissionInterface;

/**
 * Provides a 'webform_car_ip_data_for_vendor' element.
 *
 * @WebformElement(
 *   id = "webform_car_ip_data_for_vendor",
 *   label = @Translation("Item part data for vendor"),
 *   description = @Translation("Provides a webform element example."),
 *   category = @Translation("Example elements"),
 *   multiline = TRUE,
 *   composite = TRUE,
 *   states_wrapper = TRUE,
 * )
 *
 * @see \Drupal\webform_car_ip_data_for_vendor\Element\WebformCarIpDataForVendor
 * @see \Drupal\webform\Plugin\WebformElement\WebformCompositeBase
 * @see \Drupal\webform\Plugin\WebformElementBase
 * @see \Drupal\webform\Plugin\WebformElementInterface
 * @see \Drupal\webform\Annotation\WebformElement
 */
class WebformCarIpDataForVendor extends WebformCompositeBase {

  /**
   * {@inheritdoc}
   */
  protected function formatHtmlItemValue(array $element, WebformSubmissionInterface $webform_submission, array $options = []) {
    return $this->formatTextItemValue($element, $webform_submission, $options);
  }

  /**
   * {@inheritdoc}
   */
  protected function formatTextItemValue(array $element, WebformSubmissionInterface $webform_submission, array $options = []) {
    $value = $this->getValue($element, $webform_submission, $options);

    // TODO: This is unfinished!
    $lines = [];
    $lines[] = ($value['ip_item_part_label'] ? $value['ip_item_part_label'] : '') .
      ($value['ip_call_number'] ? ' ' . $value['ip_call_number'] : '') .
      ($value['ip_temporary_id'] ? ' ' . $value['ip_temporary_id'] : '') .
      ($value['ip_relation_type'] ? ' ' . $value['ip_relation_type'] : '') .
      ($value['ip_price_bundle'] ? ' (' . $value['ip_price_bundle'] . ')' : '');
    return $lines;
  }
}
