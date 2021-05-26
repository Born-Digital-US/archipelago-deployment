<?php

namespace Drupal\webform_car_ip_data_for_vendor\Element;

use Drupal\Core\Form\FormState;
use Drupal\webform\Element\WebformCompositeBase;
use Drupal\webform\Entity\WebformOptions;
use Drupal\webform_car_ip_data_for_vendor\Element\WebformCarIpDataForVendor;

/**
 * Provides a 'car_related_material'.
 *
 * Webform composites contain a group of sub-elements.
 *
 *
 * IMPORTANT:
 * Webform composite can not contain multiple value elements (i.e. checkboxes)
 * or composites (i.e. webform_address)
 *
 * @FormElement("car_related_material")
 *
 * @see \Drupal\webform\Element\WebformCompositeBase
 * @see \Drupal\webform_ip_data_for_vendor\Element\CarRelatedMaterial
 */
class CarRelatedMaterial extends WebformCompositeBase {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    return parent::getInfo() + ['#theme' => 'car_related_material'];
  }

  /**
   * {@inheritdoc}
   */
  public static function getCompositeElements(array $element): array {
    $elements = parent::getCompositeElements($element);

    $elements['related_material_reference'] = [
      '#title' => t('ADO'),
      '#type' => 'entity_autocomplete',
      '#description' => t('Other digital objects that are related to this one.<br>Begin typing the title and select from the list.'),
      '#attributes' => [
      ],
      '#target_type' => 'node',
      '#selection_handler' => 'default:node',
      '#selection_settings' => [
        '#target_bundles' => [
          'digital_object',
        ],
      ],
      '#required' => TRUE,
    ];

    $elements['related_material_relation'] = [
      '#type' => 'select',
      '#options' => 'dublin_core_relators',
      '#title' => t('Relation'),
      '#required' => TRUE,
      '#attributes' => [
        'title' => t('Dublin Core relation'),
      ],
    ];
    $elements['related_material_relation']['#options'] = WebformOptions::getElementOptions($elements['related_material_relation']);


    return $elements;
  }


}
