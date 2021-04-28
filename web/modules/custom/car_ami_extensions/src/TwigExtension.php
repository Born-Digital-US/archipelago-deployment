<?php

namespace Drupal\car_ami_extensions;

use Drupal\webform\Entity\Webform;
use Drupal\webform\Entity\WebformOptions;


/**
 * Class TwigExtension.
 *
 * @package Drupal\car_ami_extensions
 */
class TwigExtension extends \Twig_Extension {

  /**
   * @inheritDoc
   */
  public function getFunctions() {
    return [
      new \Twig_SimpleFunction('car_ami_print_dimensions',
        [$this, 'getPrintDimensions']),
      new \Twig_SimpleFunction('car_ami_page_count_parts',
        [$this, 'getPageCountAndType']),
      new \Twig_SimpleFunction('car_ami_get_variable_variables',
        [$this, 'getVariableVariables']),
      new \Twig_SimpleFunction('car_ami_get_values',
        [$this, 'getValues']),
    ];
  }

  /**
   * Given a string representing print page dimensions, return width, length, and units.
   *
   * @param  string  $dimensions_string
   *
   * @return array An array keyed by `length`, `lunits` (if present), `width`, and `units`.
   */
  public function getPrintDimensions(
    string $dimensions_string
  ): array {
    $matches = [];
    if(is_string($dimensions_string) && $dimensions_string) {
      // Do a preg_match with fancy regex with named capture groups for
      // width, length, and units.
      $pattern = '/(?<length>\d+(( *\.\d*| +\d+\/\d+))?)(?<lunits> *(in|cm|\")\.?)?(( *[xX])? *(?<width>\d+((\.\d+| +\d+\/\d+))?))?( *(?<units>in|cm|mm|\"|inches|px|pixels|Pixels)\.?)?/';
      preg_match($pattern, $dimensions_string, $matches);

    }
    // Return only named capture groups.
    return array_filter($matches, "is_string", ARRAY_FILTER_USE_KEY);
  }

  /**
   * Given a string representing print page dimensions, return width, length, and units.
   *
   * This is intended for converting e.g. "4 Tapes of 4"
   *
   * @param  string  $page_count_string
   *
   * @return array of capture group values if matched: `count`, `type`, `total`.
   */
  public function getPageCountAndType(
    string $page_count_string
  ): array {
    $matches = [];
    if(is_string($page_count_string) && $page_count_string) {
      // Do a preg_match with fancy regex with named capture groups for
      // width, length, and units.
      $pattern = '/(?<count>\d+) *(?<type>[a-zA-Z]+?)(s\b|\b)( of)?( (?<total>\d+))?/';
      preg_match($pattern, $page_count_string, $matches);
    }
    // Return only named capture groups.
    return array_filter($matches, "is_string", ARRAY_FILTER_USE_KEY);
  }

  /**
   * Workaround for lack of variable variables in TWIG.
   * Also handles splitting of values on a delimiter.
   *
   * {# Example where the name label values are pipe "|" delimited: #}
   * {% set roles = ['plumber', 'baker'] %}
   * {% set creator_name_labels = car_ami_get_variable_variables(roles, "obj_creator__name_label_@var_role", data, "|") %}
   * {% for role, names in creator_name_labels %}
   *  {% for name in names %}
   *   {{ name }} is a {{ role }}
   *  {% endfor %}
   * {% endfor %}
   *
   * @param  array  $vars
   * @param $pattern
   * @param $data
   * @param  string  $split
   *   default is ";"
   *
   * @return array
   *  An array of arrays keyed by the $vars where values were found in the $data,
   *   with each key having one or more values generated by exploding using the $split string.
   */
  public function getVariableVariables(array $vars, $pattern, $data, $split = ';') {
    $return = [];
    foreach($vars as $var) {
      $key = (string) t($pattern, ['@var' => $var]);
      // Make sure we don't return false positives.
      if(isset($data[$key]) &&  !empty(trim($data[$key]))) {
        $this_vars = array_map('trim', explode($split, $data[$key]));
        if(!empty($this_vars)) {
          $return[$var] = $this_vars;
        }
      }
    }
    return array_filter($return);
  }

  /**
   * Convert a value into an array of values, split by delimiter.
   *
   * {# Example where the values are pipe "|" delimited: #}
   * {% set creator_name_labels = car_ami_get_variable_variables(roles, "obj_creator__name_label_@var_role", data, "|") %}
   * {% for value in car_ami_get_values(data.some_field, '|') %}
   *   value is {{ value }}
   * {% endfor %}
   *
   * @param  array|string  $values
   * @param  string  $split
   *   default is ";"
   *
   * @return array
   *  An array of strings.
   */
  public function getValues($values, $split = ';') {
    // Cast the $values argument as an array, even if it was just a string.
    $values = (array) $values;
    $return = [];
    foreach($values as $value) {
      $return = array_merge($return, array_map('trim', explode($split, $value)));
    }
    return array_filter($return);
  }
}
