<?php

namespace Drupal\Tests\webform_car_ip_data_for_vendor\Functional;

use Drupal\webform\Entity\Webform;
use Drupal\webform\Entity\WebformSubmission;
use Drupal\Tests\webform\Functional\WebformBrowserTestBase;

/**
 * Tests for item part data for vendor.
 *
 * @group webform_car_ip_data_for_vendor
 */
class WebformCarIpDataForVendorTest extends WebformBrowserTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['webform_car_ip_data_for_vendor'];

  /**
   * Tests webform example element.
   */
  public function testWebformExampleComposite() {
    $webform = Webform::load('webform_car_ip_data_for_vendor');

    // Check form element rendering.
    $this->drupalGet('/webform/webform_car_ip_data_for_vendor');
    // NOTE:
    // This is a very lazy but easy way to check that the element is rendering
    // as expected.
    $this->assertRaw('<label for="edit-webform-example-composite-first-name">First name</label>');
    $this->assertFieldById('edit-webform-example-composite-first-name');
    $this->assertRaw('<label for="edit-webform-example-composite-last-name">Last name</label>');
    $this->assertFieldById('edit-webform-example-composite-last-name');
    $this->assertRaw('<label for="edit-webform-example-composite-date-of-birth">Date of birth</label>');
    $this->assertFieldById('edit-webform-example-composite-date-of-birth');
    $this->assertRaw('<label for="edit-webform-example-composite-gender">Gender</label>');
    $this->assertFieldById('edit-webform-example-composite-gender');

    // Check webform element submission.
    $edit = [
      'webform_car_ip_data_for_vendor[first_name]' => 'John',
      'webform_car_ip_data_for_vendor[last_name]' => 'Smith',
      'webform_car_ip_data_for_vendor[gender]' => 'Male',
      'webform_car_ip_data_for_vendor[date_of_birth]' => '1910-01-01',
      'webform_car_ip_data_for_vendor_multiple[items][0][first_name]' => 'Jane',
      'webform_car_ip_data_for_vendor_multiple[items][0][last_name]' => 'Doe',
      'webform_car_ip_data_for_vendor_multiple[items][0][gender]' => 'Female',
      'webform_car_ip_data_for_vendor_multiple[items][0][date_of_birth]' => '1920-12-01',
    ];
    $sid = $this->postSubmission($webform, $edit);
    $webform_submission = WebformSubmission::load($sid);
    $this->assertEqual($webform_submission->getElementData('webform_car_ip_data_for_vendor'), [
      'first_name' => 'John',
      'last_name' => 'Smith',
      'gender' => 'Male',
      'date_of_birth' => '1910-01-01',
    ]);
    $this->assertEqual($webform_submission->getElementData('webform_car_ip_data_for_vendor_multiple'), [
      [
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'gender' => 'Female',
        'date_of_birth' => '1920-12-01',
      ],
    ]);
  }

}
