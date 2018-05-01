<?php

use CRM_HRCore_Test_BaseHeadlessTest as BaseHeadlessTest;
use CRM_HRCore_Hook_BuildForm_OptionEditPathFilter as OptionEditPathFilter;

/**
 * @group headless
 */
class CRM_HRCore_Hook_BuildForm_OptionEditPathFilterTest extends BaseHeadlessTest {

  public function testNothingWillBeChangedIfOptionEditPathNotSet() {
    $filter = new OptionEditPathFilter();
    $form = new CRM_Contact_Form_Contact();
    $original = clone $form;

    $element = $form->add('text', 'bar');
    $cloneElement = clone $element;
    $original->addElement($cloneElement);

    $filter->handle('TestForm', $form);

    $this->assertEquals($original, $form);
  }

  public function testOptionEditPathWillNotBeRemovedIfNotLocked() {
    civicrm_api3('OptionGroup', 'create', ['name' => 'foo']);

    $filter = new OptionEditPathFilter();
    $form = new CRM_Contact_Form_Contact();

    $element = $form->add('text', 'bar');
    $editPath = 'civicrm/admin/options/foo';
    $attrKey = 'data-option-edit-path';
    $element->setAttribute($attrKey, $editPath);

    $filter->handle('TestForm', $form);

    $this->assertEquals($editPath, $element->getAttribute($attrKey));
  }

  public function testOptionEditPathWillBeRemovedIfLocked() {
    civicrm_api3('OptionGroup', 'create', ['name' => 'bar', 'is_locked' => 1]);

    $filter = new OptionEditPathFilter();
    $form = new CRM_Contact_Form_Contact();
    $element = $form->add('text', 'bar');
    $attrKey = 'data-option-edit-path';
    $element->setAttribute($attrKey, 'civicrm/admin/options/bar');

    $filter->handle('TestForm', $form);

    $this->assertNull($element->getAttribute($attrKey));
  }

}
