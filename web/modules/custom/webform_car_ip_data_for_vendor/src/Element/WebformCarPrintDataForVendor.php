<?php

namespace Drupal\webform_car_ip_data_for_vendor\Element;

use Drupal\Core\Form\FormState;
use Drupal\webform\Element\WebformCompositeBase;
use Drupal\webform\Entity\WebformOptions;
use Drupal\webform_car_ip_data_for_vendor\Element\WebformCarIpDataForVendor;

/**
 * Provides a 'car_print_data_for_vendor'.
 *
 * Webform composites contain a group of sub-elements.
 *
 *
 * IMPORTANT:
 * Webform composite can not contain multiple value elements (i.e. checkboxes)
 * or composites (i.e. webform_address)
 *
 * @FormElement("car_print_data_for_vendor")
 *
 * @see \Drupal\webform\Element\WebformCompositeBase
 * @see \Drupal\webform_ip_print_data_for_vendor\Element\WebformCarPrintDataForVendor
 */
class WebformCarPrintDataForVendor extends WebformCompositeBase {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    return parent::getInfo() + ['#theme' => 'car_print_data_for_vendor'];
  }

  /**
   * {@inheritdoc}
   */
  public static function getCompositeElements(array $element): array {
    $elements = parent::getCompositeElements($element);

    // For some reason, this gets called sometimes without a populated element...
    if (!empty($element)) {
      $elements['ip_dimensions'] = [
        '#type' => 'fieldset',
        '#title' => t('Ext Dimensions'),
        'ip_ext_dim_length' => [
          '#type' => 'textfield',
          '#size' => 6,
          '#title' => t('Length'),
          '#pattern' => '^(([0-9]*[ ])?[0-9]+\/[0-9]+)|([0-9]+(\.[0-9]*)?)$',
          '#required' => TRUE,
        ],
        'ip_ext_dim_width' => [
          '#type' => 'textfield',
          '#size' => 6,
          '#title' => t('Width'),
          '#pattern' => '^(([0-9]*[ ])?[0-9]+\/[0-9]+)|([0-9]+(\.[0-9]*)?)$',
          '#required' => TRUE,
        ],
        'ip_ext_dim_units' => [
          '#type' => 'select',
          '#options' => ['cm' => 'cm', 'in' => 'in'],
          '#title' => t('Units'),
          '#required' => TRUE,
          '#attributes' => [
            'class' =>[
              'chosen-disabled'
            ],
          ],
        ],
      ];

      $elements['part_count'] = [
        '#type' => 'fieldset',
        '#title' => t('Parts'),
        'ip_extent_count' => [
          '#type' => 'number',
          '#min' => 1,
          '#step' => 1,
          '#title' => t('Number of Parts'),
          '#required' => TRUE,
        ],
        'ip_extent_count_type' => [
          '#type' => 'select',
          '#options' => [
            'disc' => 'Disc',
            'file' => 'File',
            'page' => 'Page',
            'reel' => 'Reel'
          ],
          '#title' => t('Type'),
          '#required' => TRUE,
          '#attributes' => [
            'class' =>[
              'chosen-disabled'
            ],
          ],
        ],
      ];

      $elements['ip_print_format'] = [
        '#type' => 'webform_term_select',
        '#vocabulary' => 'voc_print_format',
        '#title' => t('Format'),
        '#required' => TRUE,
        '#choices' => TRUE,
        '#attributes' => [
          'class' =>[
            'chosen-disabled'
          ],
        ],
      ];

      $elements['ip_print_price_bundle'] = [
        '#type' => 'webform_term_select',
        '#vocabulary' => 'price_bundle',
        // This is the print price bundle vocabulary.
        '#title' => t('Price Bundle'),
        '#element_validate' => [[get_called_class(), 'print_price_bundle_validate']],
        '#choices' => TRUE,
        '#attributes' => [
          'class' =>[
            'chosen-disabled'
          ],
        ],
      ];

      /*
       * TODO: support multivalue?
       * This will be tough to do.
       * Requires overriding \Drupal\webform\Element\WebformCompositeBase::initializeCompositeElementsRecursive
       * It will require serializing the saved data, and unserializing in *every place* that consumes that data.
       * See https://www.drupal.org/project/webform/issues/2872320#comment-12052985
       * Also see https://weareborndigital.teamwork.com/#tasks/24564552?c=11680531
       */
      $elements['special_handling_group'] = [
        '#type' => 'fieldset',
        '#title' => t('Special Handling'),
        'ip_print_special_handling' => [
          '#type' => 'webform_term_select',
          '#vocabulary' => 'special_handling',
          '#title' => t('Special Handling 1'),
          '#title_display' => 'invisible',
          '#choices' => TRUE,
          '#attributes' => [
            'class' =>[
              'chosen-disabled'
            ],
          ],
        ],
        'ip_print_special_handling_2' => [
          '#type' => 'webform_term_select',
          '#vocabulary' => 'special_handling',
          '#title' => t('Special Handling 2'),
          '#title_display' => 'invisible',
          '#choices' => TRUE,
          '#attributes' => [
            'class' =>[
              'chosen-disabled'
            ],
          ],
        ],
        'ip_print_special_handling_3' => [
          '#type' => 'webform_term_select',
          '#vocabulary' => 'special_handling',
          '#title' => t('Special Handling 3'),
          '#title_display' => 'invisible',
          '#choices' => TRUE,
          '#attributes' => [
            'class' =>[
              'chosen-disabled'
            ],
          ],
        ],
        'ip_print_special_handling_4' => [
          '#type' => 'webform_term_select',
          '#vocabulary' => 'special_handling',
          '#title' => t('Special Handling 3'),
          '#title_display' => 'invisible',
          '#choices' => TRUE,
          '#attributes' => [
            'class' =>[
              'chosen-disabled'
            ],
          ],
        ],
      ];
      $elements['ip_generation'] = [
        '#type' => 'select',
        '#options' => ['Original' => 'Original', 'Copy' => 'Copy'],
        '#title' => t('Generation'),
      ];
    }

    return $elements;
  }

  /**
   * Price bundle should be required when workflow state is after nominated.
   *
   * @param $element
   * @param  \Drupal\Core\Form\FormState  $form_state
   */
  public static function print_price_bundle_validate($element, FormState &$form_state) {
    $wfs = $form_state->getValue(['obj_workflow_state']);
    $media_type = WebformCarIpDataForVendor::getMediaTypeInput($form_state);
    $is_av = preg_match('/(Moving Image|Audio|Sound)/', $media_type);
    if (!$is_av && empty($element['#value']) && !empty($wfs) && $wfs != 'car_nominated') {
      $form_state->setError($element, t('Price bundle is required once the workflow state has changed from "Nominated".'));
    }
  }


}
