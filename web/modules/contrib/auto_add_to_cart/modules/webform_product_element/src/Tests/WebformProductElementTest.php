<?php

namespace Drupal\webform_product_element\Tests;

use Drupal\webform\Entity\Webform;
use Drupal\webform\Entity\WebformSubmission;
use Drupal\webform\Tests\WebformTestBase;

/**
 * Tests for webform product element.
 *
 * @group Webform
 */
class WebformProductElementTest extends WebformTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['webform_product_element'];

  /**
   * Tests webform product element.
   */
  public function testWebformProductElement() {
    $webform = Webform::load('webform_product_element');

    // Check form element rendering.
    $this->drupalGet('webform/webform_product_element');
    // NOTE:
    // This is a very lazy but easy way to check that the element is rendering
    // as expected.
    $this->assertRaw('<div class="js-form-item form-item js-form-type-webform-example-element form-type-webform-example-element js-form-item-webform-example-element form-item-webform-example-element">');
    $this->assertRaw('<label for="edit-webform-example-element">Webform Example Element</label>');
    $this->assertRaw('<input data-drupal-selector="edit-webform-example-element" type="text" id="edit-webform-example-element" name="webform_product_element" value="" size="60" class="form-text webform-example-element" />');

    // Check webform element submission.
    $edit = [
      'webform_product_element' => '{Test}',
      'webform_product_element_multiple[items][0][_item_]' => '{Test 01}',
    ];
    $sid = $this->postSubmission($webform, $edit);
    $webform_submission = WebformSubmission::load($sid);
    $this->assertEqual($webform_submission->getElementData('webform_product_element'), '{Test}');
    $this->assertEqual($webform_submission->getElementData('webform_product_element_multiple'), ['{Test 01}']);
  }

}
