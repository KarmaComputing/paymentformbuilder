<?php

namespace Drupal\webform_product_element\Element;

use Drupal\Core\Render\Element;
use Drupal\Core\Render\Element\FormElement;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a 'webform_product_element'.
 *
 * Webform elements are just wrappers around form elements, therefore every
 * webform element must have correspond FormElement.
 *
 * Below is the definition for a custom 'webform_product_element' which just
 * renders a simple text field.
 *
 * @FormElement("webform_product_element")
 *
 * @see \Drupal\Core\Render\Element\FormElement
 * @see https://api.drupal.org/api/drupal/core%21lib%21Drupal%21Core%21Render%21Element%21FormElement.php/class/FormElement
 * @see \Drupal\Core\Render\Element\RenderElement
 * @see https://api.drupal.org/api/drupal/namespace/Drupal%21Core%21Render%21Element
 * @see \Drupal\webform_product_element\Element\WebformExampleElement
 */
class WebformProductElement extends FormElement {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);
    return [
      '#input' => TRUE,
      '#size' => 60,
      '#default_value' => "cheese",
      '#autocomplete_route_name' => "system.entity_autocomplete",
      '#autocomplete_route_parameters' => array('target_type' => 'node'),
      '#process' => [
        [$class, 'processWebformElementProduct'],
        [$class, 'processAjaxForm'],
      ],
      '#element_validate' => [
        [$class, 'validateWebformProductElement'],
      ],
      '#pre_render' => [
        [$class, 'preRenderWebformProductElement'],
      ],
      '#theme' => 'input__webform_product_element',
      '#theme_wrappers' => ['form_element'],
    ];
  }

  /**
   * Processes a 'webform_product_element' element.
   */
  public static function processWebformElementProduct(&$element, FormStateInterface $form_state, &$complete_form) {
    // Here you can add and manipulate your element's properties and callbacks.
    return $element;
  }

  /**
   * Webform element validation handler for #type 'webform_product_element'.
   */
  public static function validateWebformProductElement(&$element, FormStateInterface $form_state, &$complete_form) {
    // Here you can add custom validation logic.
  }

  /**
   * Prepares a #type 'email_multiple' render element for theme_element().
   *
   * @param array $element
   *   An associative array containing the properties of the element.
   *   Properties used: #title, #value, #description, #size, #maxlength,
   *   #placeholder, #required, #attributes.
   *
   * @return array
   *   The $element with prepared variables ready for theme_element().
   */
  public static function preRenderWebformProductElement(array $element) {
    $element['#attributes']['type'] = 'text';
    Element::setAttributes($element, ['id', 'name', 'value', 'size', 'maxlength', 'placeholder']);
    static::setAttributes($element, ['form-text', 'webform-product-element']);
    return $element;
  }

}
