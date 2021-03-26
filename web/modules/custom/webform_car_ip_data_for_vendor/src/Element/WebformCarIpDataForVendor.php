<?php

namespace Drupal\webform_car_ip_data_for_vendor\Element;

use Drupal\Core\Form\FormStateInterface;
use Drupal\webform\Element\WebformCompositeBase;
use Drupal\webform\Entity\WebformOptions;

/**
 * Provides a 'webform_car_ip_data_for_vendor'.
 *
 * Webform composites contain a group of sub-elements.
 *
 *
 * IMPORTANT:
 * Webform composite can not contain multiple value elements (i.e. checkboxes)
 * or composites (i.e. webform_address)
 *
 * @FormElement("webform_car_ip_data_for_vendor")
 *
 * @see \Drupal\webform\Element\WebformCompositeBase
 * @see \Drupal\webform_car_ip_data_for_vendor\Element\WebformCarIpDataForVendor
 */
class WebformCarIpDataForVendor extends WebformCompositeBase {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    return parent::getInfo() + ['#theme' => 'webform_car_ip_data_for_vendor'];
  }

  /**
   * {@inheritdoc}
   */
  public static function getCompositeElements(array $element): array {
    $elements = parent::getCompositeElements($element);

    // Use the #multiple property to determine whether to show av fields or non-av fields.
    $is_av = !empty($element['#multiple']);

    $elements['ip_item_part_label'] = [
      '#type' => 'textfield',
      '#title' => t('Item Identifier/Label'),
    ];
    $elements['ip_call_number'] = [
      '#type' => 'textfield',
      '#title' => t('Item Call Number'),
      '#after_build' => [[get_called_class(), 'afterBuild']],
    ];

    $elements['ip_identifiers_delimiter'] = [
      '#type' => 'item',
      '#markup' => t(' -OR- '),
      '#after_build' => [[get_called_class(), 'afterBuild']],
    ];

    $elements['ip_temporary_id'] = [
      '#type' => 'textfield',
      '#title' => t('Item Temporary Identifier'),
      '#after_build' => [[get_called_class(), 'afterBuild']],
    ];

    $elements['ip_relation_type'] = [
      '#type' => 'select',
      '#options' => 'pbcorerelationtype',
      '#title' => t('Item Relation Type'),
    ];
    $elements['ip_relation_type']['#options'] = WebformOptions::getElementOptions($elements['ip_relation_type']);

    $elements['ip_av_part_type'] = [
      '#type' => 'select',
      '#options' => 'pbcore_asset_type',
      '#title' => t('AV Part Type'),
    ];
    $elements['ip_av_part_type']['#options'] = WebformOptions::getElementOptions($elements['ip_av_part_type']);

    $elements['ip_base_type'] = [
      '#type' => 'select',
      '#options' => 'ip_av_film_base_type',
      '#title' => t('Item Base Type'),
    ];
    $elements['ip_base_type']['#options'] = WebformOptions::getElementOptions($elements['ip_base_type']);

    $elements['ip_sides_parts'] = [
      '#type' => 'number',
      '#min' => 1,
      '#step' => 1,
      '#title' => t('Item Number of Sides/Parts'),
    ];

    $elements['ip_condition'] = [
      '#type' => 'select',
      '#options' => 'sl_condition',
      '#title' => t('Item Condition'),
    ];
    $elements['ip_condition']['#options'] = WebformOptions::getElementOptions($elements['ip_condition']);

    $elements['ip_condition_notes'] = [
      '#type' => 'textarea',
      '#rows' => 4,
      '#resizeable' => 'vertical',
      '#title' => t('Item Condition Notes'),
    ];

    $elements['ip_av_physical_asset_type'] = [
      '#type' => 'select',
      '#options' => ['Audio' => 'Audio', 'Film' => 'Film', 'Video' => 'Video'],
      '#title' => t('AV Physical Asset Type'),
    ];

    $elements['ip_gauge_and_format_audio'] = [
      '#type' => 'select',
      '#options' => 'instantiationphysical_audio',
      '#title' => t('Audio Format'),
      // Use #after_build to add #states.
      '#after_build' => [[get_called_class(), 'afterBuild']],
    ];
    $elements['ip_gauge_and_format_audio']['#options'] = WebformOptions::getElementOptions($elements['ip_gauge_and_format_audio']);

    $elements['ip_gauge_and_format_film'] = [
      '#type' => 'select',
      '#options' => 'instantiationphysical_film',
      '#title' => t('Film Format'),
      // Use #after_build to add #states.
      '#after_build' => [[get_called_class(), 'afterBuild']],
    ];
    $elements['ip_gauge_and_format_audio']['#options'] = WebformOptions::getElementOptions($elements['ip_gauge_and_format_audio']);

    $elements['ip_gauge_and_format_video'] = [
      '#type' => 'select',
      '#options' => 'instantiationphysical_video',
      '#title' => t('Video Format'),
      // Use #after_build to add #states.
      '#after_build' => [[get_called_class(), 'afterBuild']],
    ];
    $elements['ip_gauge_and_format_video']['#options'] = WebformOptions::getElementOptions($elements['ip_gauge_and_format_video']);

    $elements['ip_gauge_and_format'] = [
      '#type' => 'select',
      '#options' => 'av_format',
      '#title' => t('Item Gauge and Format'),
    ];
    $elements['ip_gauge_and_format']['#options'] = WebformOptions::getElementOptions($elements['ip_gauge_and_format']);

    $elements['ip_generation'] = [
      '#type' => 'select',
      '#options' => 'pbcore_instantiationgenerations',
      '#title' => t('Item Generation'),
    ];
    $elements['ip_generation']['#options'] = WebformOptions::getElementOptions($elements['ip_generation']);

    $elements['ip_media_type'] = [
      '#type' => 'select',
      '#options' => 'pbcore_instantiationmediatype',
      '#title' => t('Item Media Type'),
    ];
    $elements['ip_media_type']['#options'] = WebformOptions::getElementOptions($elements['ip_media_type']);

    $elements['ip_language_of_material'] = [
      '#type' => 'select',
      '#options' => 'languages_iso_639_2',
      '#title' => t('Language of Material'),
    ];
    $elements['ip_language_of_material']['#options'] = WebformOptions::getElementOptions($elements['ip_language_of_material']);

    $elements['ip_additional_technical_notes'] = [
      '#type' => 'textarea',
      '#rows' => 4,
      '#resizeable' => 'vertical',
      '#title' => t('Additional Technical Notes'),
    ];

    $elements['ip_price_bundle'] = [
      '#type' => 'webform_term_select',
      '#vocabulary' => 'price_bundle',
      '#title' => t('Price Bundle'),
    ];

    return $elements;
  }

  /**
   * Performs the after_build callback.
   *
   * @param  array  $element
   * @param  \Drupal\Core\Form\FormStateInterface  $form_state
   *
   * @return array
   */
  public static function afterBuild(array $element, FormStateInterface $form_state): array {
    // Add #states targeting the specific element and table row.
    preg_match('/^(.+)\[([^]]+)]$/', $element['#name'], $match);
    $composite_name = $match[1];
    $element_name = $match[2];
    switch ($element_name) {
      // Audio formats.
      case 'ip_gauge_and_format_audio':
        // Show this element only when the appropriate value is selected in the physical asset type.
        $element['#states']['visible'] = [
          [':input[name="' . $composite_name . '[ip_av_physical_asset_type]"]' => ['value' => 'Audio']],
        ];
        // Prevent value from being saved if a different physical asset type is selected by setting a
        // conditional disaled state.
        $element['#states']['disabled'] = [
          [':input[name="' . $composite_name . '[ip_av_physical_asset_type]"]' => ['!value' => 'Audio']],
        ];
        break;
      // Film formats.
      case 'ip_gauge_and_format_film':
        $element['#states']['visible'] = [
          [':input[name="' . $composite_name . '[ip_av_physical_asset_type]"]' => ['value' => 'Film']],
        ];
        $element['#states']['disabled'] = [
          [':input[name="' . $composite_name . '[ip_av_physical_asset_type]"]' => ['!value' => 'Film']],
        ];
        break;
      // Video formats.
      case 'ip_gauge_and_format_video':
        $element['#states']['visible'] = [
          [':input[name="' . $composite_name . '[ip_av_physical_asset_type]"]' => ['value' => 'Video']],
        ];
        $element['#states']['disabled'] = [
          [':input[name="' . $composite_name . '[ip_av_physical_asset_type]"]' => ['!value' => 'Video']],
        ];
        break;

        // Identifiers. Disable one if the other has a value.
      case 'ip_call_number':
        $element['#states']['visible'] = [
          [':input[name="' . $composite_name . '[ip_temporary_id]"]' => ['value' => '' ]],
        ];
        $element['#states']['disabled'] = [
          [':input[name="' . $composite_name . '[ip_temporary_id]"]' => ['!value' => '' ]],
        ];
        break;
      case'ip_temporary_id':
        $element['#states']['visible'] = [
          [':input[name="' . $composite_name . '[ip_call_number]"]' => ['value' => '' ]],
        ];
        $element['#states']['disabled'] = [
          [':input[name="' . $composite_name . '[ip_call_number]"]' => ['!value' => '' ]],
        ];
        break;
        // Show "-or" between the two
      case 'ip_identifiers_delimiter':
        $element['#states']['visible'] = [
          [':input[name="' . $composite_name . '[ip_call_number]"]' => ['value' => '' ]],
          'and',
          [':input[name="' . $composite_name . '[ip_temporary_id]"]' => ['value' => '' ]],
        ];
        $element['#states']['invisible'] = [
          [':input[name="' . $composite_name . '[ip_call_number]"]' => ['!value' => '' ]],
          'or',
          [':input[name="' . $composite_name . '[ip_temporary_id]"]' => ['!value' => '' ]],
        ];

        break;
    }
    // Add .js-form-wrapper to wrapper (ie td) to prevent #states API from
    // disabling the entire table row when this element is disabled.
    $element['#wrapper_attributes']['class'][] = 'js-form-wrapper';
    return $element;
  }

}
