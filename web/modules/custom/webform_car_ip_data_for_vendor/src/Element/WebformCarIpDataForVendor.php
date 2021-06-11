<?php

namespace Drupal\webform_car_ip_data_for_vendor\Element;

use Drupal\Core\Form\FormState;
use Drupal\Core\Form\FormStateInterface;
use Drupal\taxonomy\Entity\Term;
use Drupal\webform\Element\WebformCompositeBase;
use Drupal\webform\Element\WebformMultiple;
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

    // For some reason, this gets called sometimes without a populated element...
    if(!empty($element)) {

      $elements['ip_item_part_label'] = [
        '#type' => 'textfield',
        '#title' => t('Part ID'),
        '#attributes' => ['title' => t('Item Part Identifier/Label. E.g. "r01" for reel #1, or "t04" for the fourth tape')],
      ];

      $elements['ip_call_number'] = [
        '#type' => 'textfield',
        '#title' => t('<span class="form-required">Call Number</span>'),
        '#after_build' => [[get_called_class(), 'afterBuild']],
        '#element_validate' => [[get_called_class(), 'ip_id_validate']],
      ];

      $elements['ip_identifiers_delimiter'] = [
        '#type' => 'item',
        '#markup' => t(' -OR- '),
        '#after_build' => [[get_called_class(), 'afterBuild']],
      ];

      $elements['ip_temporary_id'] = [
        '#type' => 'textfield',
        '#title' => t('<span class="form-required">Temp Identifier</span>'),
        '#after_build' => [[get_called_class(), 'afterBuild']],
        '#element_validate' => [[get_called_class(), 'ip_id_validate']],
        '#attributes' => ['title' => t('Temporary Identifier')],
      ];

      $elements['ip_container_item_annotations'] = [
        '#type' => 'textarea',
        '#rows' => 4,
        '#resizeable' => 'vertical',
        '#title' => t('Container/Annots'),
        '#attributes' => ['title' => t('Container/Annotations')],
      ];

      $elements['ip_media_type'] = [
        '#type' => 'webform_term_select',
        '#vocabulary' => 'media_type',
        '#title' => t('Media Type'),
        '#required' => TRUE,
        '#after_build' => [[get_called_class(), 'filterAVMediaTypes']],
        '#choices' => TRUE,
        '#attributes' => [
          'class' =>[
            'chosen-disabled'
          ],
        ],
      ];

      $elements['ip_gauge_and_format'] = [
        '#type' => 'webform_term_select',
        '#vocabulary' => 'voc_guage_and_format',
        '#title' => t('Gauge/Format'),
        '#attributes' => [
          'title' => t('Gauge and Format'),
          'class' =>[
            'chosen-disabled'
          ],
        ],
        '#choices' => TRUE,
      ];

      $elements['ip_aspect_ratio'] = [
        '#type' => 'webform_term_select',
        '#vocabulary' => 'aspect_ratio',
        '#title' => t('Aspect Ratio'),
        '#attributes' => [
          'title' => t('Aspect Ratio'),
          'class' =>[
            'chosen-disabled'
          ],
        ],
        '#after_build' => [[get_called_class(), 'afterBuild']],
        '#choices' => TRUE,
      ];

      $elements['ip_running_speed'] = [
        '#type' => 'webform_term_select',
        '#vocabulary' => 'running_speed',
        '#title' => t('Running Speed'),
        '#attributes' => [
          'title' => t('Running Speed'),
          'class' =>[
            'chosen-disabled'
          ],
        ],
        '#choices' => TRUE,
      ];


      $elements['ip_generation'] = [
        '#type' => 'webform_term_select',
        '#vocabulary' => 'voc_av_generation',
        '#title' => t('Generation'),
        '#attributes' => [
          'title' => t('Generation'),
          'class' =>[
            'chosen-disabled'
          ],
        ],
        '#choices' => TRUE,
      ];

      $elements['ip_sides'] = [
        '#type' => 'number',
        '#min' => 1,
        '#step' => 1,
        '#title' => t('Sides'),
        '#attributes' => ['title' => t('Number of Sides (minimum 1)')],
        '#required' => TRUE,
      ];

      $elements['parts_wrapper'] = [
//        '#type' => 'fieldset',
//        '#title' => t('<span class="form-required">Parts</span>'),
//        'ip_parts_number' => [
//          '#type' => 'number',
//          '#min' => 1,
//          '#step' => 1,
//          '#attributes' => ['placeholder' => t('Number'), 'title' => t('Number of Parts (minimum 1)')],
//          '#title_display' => 'invisible',
//          '#required' => TRUE,
//        ],
        'ip_parts_type' => [
          '#type' => 'select',
//          '#title_display' => 'invisible',
          '#attributes' => [
            'title' => t('Part type (required)'),
            'class' =>[
              'chosen-disabled'
            ],
          ],
          '#empty_option' => t("-Type-"),
          '#options' => [
            'cylinder' => 'Cylinder',
            'disc' => 'Disc',
            'file' => 'File',
            'tape' => 'Tape',
            'reel' => 'Reel',
            'unknown' => 'Unknown',
          ],
          '#title' => t('Part Type'),
          '#required' => TRUE,
        ],
      ];

        $elements['ip_condition_notes'] = [
          '#type' => 'textarea',
          '#rows' => 4,
          '#resizeable' => 'vertical',
          '#title' => t('Condition Notes'),
        ];

        $elements['ip_base_type'] = [
          '#type' => 'select',
          '#options' => 'ip_av_film_base_type',
          '#title' => t('Base Type'),
          '#choices' => TRUE,
          '#attributes' => [
            'title' => t('The film base'),
            'class' =>[
              'chosen-disabled'
            ],
          ],
        ];
        $elements['ip_base_type']['#options'] = WebformOptions::getElementOptions($elements['ip_base_type']);

        $elements['ip_additional_technical_notes'] = [
          '#type' => 'textarea',
          '#rows' => 4,
          '#resizeable' => 'vertical',
          '#title' => t('Notes'),
          '#attributes' => ['title' => t('Notes')],
        ];

        $elements['ip_price_bundle'] = [
          '#type' => 'webform_term_select',
          '#vocabulary' => 'av_price_bundle',
          '#title' => t('Price Bundle'),
          '#attributes' => [
            'title' => t('Price Bundle'),
            'class' =>[
              'chosen-disabled'
            ],
          ],
          //      '#after_build' => [[get_called_class(), 'afterBuild']], // TODO Setting required doesn't work.
          '#element_validate' => [[get_called_class(), 'av_price_bundle_validate']],
          '#choices' => TRUE,
        ];

      $elements['ip_special_handling'] = [
        '#type' => 'webform_term_select',
        '#vocabulary' => 'voc_av_special_handling',
        '#title' => t('Special Handling'),
        '#choices' => TRUE,
        '#attributes' => [
          'title' => t('Special Handling'),
          'class' =>[
            'chosen-disabled'
          ],
        ],
      ];
    }

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
        // Identifiers. Disable one if the other has a value.
      case 'ip_call_number':
        $element['#states']['visible'] = [
          [':input[name="' . $composite_name . '[ip_temporary_id]"]' => ['filled' => FALSE ]],
        ];
        // TODO: required/optional is not working. Required a custom validate function.
//        $element['#states']['optional'] = [
//          [':input[name="' . $composite_name . '[ip_temporary_id]"]' => ['filled' => TRUE ]],
//        ];
//        $element['#states']['required'] = [
//          [':input[name="' . $composite_name . '[ip_temporary_id]"]' => ['filled' => FALSE ]],
//        ];
        $element['#states']['disabled'] = [
          [':input[name="' . $composite_name . '[ip_temporary_id]"]' => ['filled' => TRUE ]],
        ];
        break;
      case'ip_temporary_id':
        $element['#states']['visible'] = [
          [':input[name="' . $composite_name . '[ip_call_number]"]' => ['filled' => FALSE ]],
        ];
        // TODO: required/optional is not working. Required a custom validate function.
//        $element['#states']['optional'] = [
//          [':input[name="' . $composite_name . '[ip_call_number]"]' => ['filled' => TRUE ]],
//        ];
//        $element['#states']['required'] = [
//          [':input[name="' . $composite_name . '[ip_call_number]"]' => ['filled' => FALSE ]],
//        ];
        $element['#states']['disabled'] = [
          [':input[name="' . $composite_name . '[ip_call_number]"]' => ['filled' => TRUE ]],
        ];
        break;
      case'ip_aspect_ratio':
        $element['#states']['visible'] = [
          [':input[name="' . $composite_name . '[ip_media_type]"]' => ['value' => "http://pbcore.org/pbcore-controlled-vocabularies/titletype-vocabulary/#MovingImage" ]],
        ];
        $element['#states']['disabled'] = [
          [':input[name="' . $composite_name . '[ip_media_type]"]' => ['!value' => "http://pbcore.org/pbcore-controlled-vocabularies/titletype-vocabulary/#MovingImage" ]],
        ];
        break;
        // TODO: This doesn't work.
//      case'ip_price_bundle':
//        $element['#states']['required'] = [
//          [':input[name="obj_workflow_state"]' => ['filled' => TRUE ]],
//          'and',
//          [':input[name="obj_workflow_state"]' => ['!value' => 'car_nominated' ]],
//        ];
//        break;
        // Show "-OR" between the two
      // TODO: this is not working reliably.
      // TODO: When deleting a previously saved value for ip_temporary_id, it does not appear.
      case 'ip_identifiers_delimiter':
        $element['#states']['visible'] = [
          [':input[name="' . $composite_name . '[ip_call_number]"]' => ['filled' => FALSE ]],
          'and',
          [':input[name="' . $composite_name . '[ip_temporary_id]"]' => ['filled' => FALSE ]],
        ];
        $element['#states']['invisible'] = [
          [':input[name="' . $composite_name . '[ip_call_number]"]' => ['filled' => TRUE ]],
          'or',
          [':input[name="' . $composite_name . '[ip_temporary_id]"]' => ['filled' => TRUE ]],
        ];

        break;
    }
    // Add .js-form-wrapper to wrapper (ie td) to prevent #states API from
    // disabling the entire table row when this element is disabled.
    $element['#wrapper_attributes']['class'][] = 'js-form-wrapper';
    return $element;
  }

  /**
   * @param $element
   * @param  \Drupal\Core\Form\FormState  $form_state
   */
  public static function ip_id_validate($element, FormState &$form_state) {
    // TODO some day - do this as a webform handler:
    // https://www.drupal.org/docs/8/modules/webform/webform-cookbook/how-to-add-custom-validation-to-a-webform-element#s-method-2-using-webform-handlers
    // Because this runs for each element, we don't want to run it twice.
    $delta = $element['#parents'][2];
    $has_run = &drupal_static(__FUNCTION__ . $delta);
    if(!isset($has_run)) {
      $has_run = TRUE;

      $media_type = self::getMediaTypeInput($form_state);
      $is_av = preg_match('/(Moving Image|Audio|Sound)/', $media_type);
      if($is_av) {
        $parent = $element['#parents'];
        array_pop($parent);
        $call_number = $form_state->getValue(array_merge($parent, ['ip_call_number']));
        $temporary_id = $form_state->getValue(array_merge($parent, ['ip_temporary_id']));

        if ( !(empty($call_number) XOR empty($temporary_id))) {
          if (empty($temporary_id)) {
            $form_state->setErrorByName(implode('][', array_merge($parent, ['ip_call_number'])), t('A call number or a temporary ID must be provided.'));
            $form_state->setErrorByName(implode('][', array_merge($parent, ['ip_temporary_id'])));
          }
          else {
            $form_state->setErrorByName(implode('][', array_merge($parent, ['ip_call_number'])),
              t('You provided both a call number and a temporary ID. You must provide one or the other - you cannot provide both.'));
            $form_state->setErrorByName(implode('][', array_merge($parent, ['ip_temporary_id'])));
          }
        }
      }
    }
  }

  /**
   * Filter the media type vocabulary options list to just Audio and Moving Image
   *
   * @param  array  $element
   * @param  \Drupal\Core\Form\FormStateInterface  $form_state
   *
   * @return array
   */
  public static function filterAVMediaTypes(array $element, FormStateInterface $form_state): array {
    $keep = ['Audio', 'Sound', 'Moving Image'];
    foreach($element['#options'] as $key => $value) {
      if(is_string($value) && !in_array($value, $keep)) {
        unset($element['#options'][$key]);
      }
    }
    return $element;
  }

  /**
   * Price bundle should be required when workflow state is after nominated.
   *
   * @param $element
   * @param  \Drupal\Core\Form\FormState  $form_state
   */
  public static function av_price_bundle_validate($element, FormState &$form_state) {
    $wfs = $form_state->getValue(['obj_workflow_state']);
    $media_type = self::getMediaTypeInput($form_state);
    $is_av = preg_match('/(Moving Image|Audio|Sound)/', $media_type);
    if ($is_av && empty($element['#value']) && !empty($wfs) && $wfs != 'car_nominated') {
      $form_state->setError($element, t('Price bundle is required once the workflow state has changed from "Nominated".'));
    }
  }

  /**
   * @param  \Drupal\Core\Form\FormState  $form_state
   *
   * @return int|mixed|string
   */
  public static function getMediaTypeInput(FormState $form_state) {
    $media_type = &drupal_static(__FUNCTION__);
    if(!isset($media_type)) {
      $media_type = $form_state->getValue(['obj_media_type']);
      // Handle if we're using a term reference field.
      if(is_numeric($media_type)) {
        $term = Term::load($media_type);
        if(!empty($term)) {
          $media_type = $term->getName();
        }
      }
    }
    return $media_type;
  }

  public static function addCloneButton(array $element, FormStateInterface $form_state): array {
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public static function processWebformComposite(&$element, FormStateInterface $form_state, &$complete_form) {
    parent::processWebformComposite($element,  $form_state, $complete_form);
//    WebformMultiple::processWebformMultiple($element, $form_state,$complete_form);

      $element['#after_build'][] = [get_called_class(), 'addCloneButton'];
    return $element;
  }

}
