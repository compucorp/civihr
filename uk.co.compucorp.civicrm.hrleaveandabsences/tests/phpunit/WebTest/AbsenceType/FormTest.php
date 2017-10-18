<?php

use Civi\Test\HeadlessInterface;

/**
 * Class WebTest_AbsenceType_FormTest
 *
 * @group headless
 */
class WebTest_AbsenceType_FormTest extends CiviSeleniumTestCase implements HeadlessInterface {

    private $formUrl = 'admin/leaveandabsences/types';
    private $addUrlParams = 'action=add&reset=1';
    private $editUrlParams = 'action=update&reset=1';

    public function setUpHeadless() {
      return \Civi\Test::headless()->installMe(__DIR__)->apply();
    }

    private function loginAsAdmin()
    {
        if(is_null($this->loggedInAs)) {
            $this->webtestLogin('admin');
        }
    }

    public function testToilFieldsVisibility()
    {
        $this->loginAsAdmin();
        $this->openAddForm();

        // Allow acrruals request should not be checked and all toil fields
        // should not be visible
        $this->assertFalse($this->isChecked('allow_accruals_request'));
        $this->assertFalse($this->isVisible('max_leave_accrual'));
        $this->assertFalse($this->isVisible('allow_accrue_in_the_past'));
        $this->assertFalse($this->isVisible('accrual_expiration_duration'));
        $this->assertFalse($this->isVisible('s2id_accrual_expiration_unit'));

        // When allow accruals request is checked, some fields become visible
        $this->click('allow_accruals_request');
        $this->assertTrue($this->isVisible('max_leave_accrual'));
        $this->assertTrue($this->isVisible('allow_accrue_in_the_past'));
        $this->assertFalse($this->isVisible('accrual_expiration_duration'));
        $this->assertFalse($this->isVisible('s2id_accrual_expiration_unit'));
        $this->assertTrue($this->isChecked('accrual_never_expire'));

        // When Never expire is not checked, the expiration fields become visible
        $this->click('accrual_never_expire');
        $this->assertTrue($this->isVisible('max_leave_accrual'));
        $this->assertTrue($this->isVisible('allow_accrue_in_the_past'));
        $this->assertTrue($this->isVisible('accrual_expiration_duration'));
        $this->assertTrue($this->isVisible('s2id_accrual_expiration_unit'));
        $this->assertElementValueEquals('accrual_expiration_duration', '');
        $this->select('accrual_expiration_unit', "value=1");

        // When Never expire is checked again, the expiration fields become hidden
        $this->click('accrual_never_expire');
        $this->assertTrue($this->isVisible('max_leave_accrual'));
        $this->assertTrue($this->isVisible('allow_accrue_in_the_past'));
        $this->assertFalse($this->isVisible('accrual_expiration_duration'));
        $this->assertFalse($this->isVisible('s2id_accrual_expiration_unit'));

        // If we submit the form with only the expire duration not empty,
        // both duration fields should still be visible after the page reloads
        // (it will reload because the other required fields have not been filled)
        $this->click('accrual_never_expire');
        $this->type('accrual_expiration_duration', 10);
        $this->submitAndWait('AbsenceType');
        $this->assertTrue($this->isVisible('accrual_expiration_duration'));
        $this->assertTrue($this->isVisible('s2id_accrual_expiration_unit'));
        $this->assertFalse($this->isChecked('accrual_never_expire'));

        // If we submit the form with only the expire unit not empty,
        // both duration fields should still be visible after the page reloads
        // (it will reload because the other required fields have not been filled)
        $this->type('accrual_expiration_duration', '');
        $this->select('accrual_expiration_unit', 'label=Months');
        $this->submitAndWait('AbsenceType');
        $this->assertTrue($this->isVisible('accrual_expiration_duration'));
        $this->assertTrue($this->isVisible('s2id_accrual_expiration_unit'));
        $this->assertFalse($this->isChecked('accrual_never_expire'));

        // If we submit the form with both the expire duration and unit not empty,
        // the duration fields should still be visible after the page reloads
        // (it will reload because the other required fields have not been filled)
        $this->type('accrual_expiration_duration', 15);
        $this->select('accrual_expiration_unit', 'label=Months');
        $this->submitAndWait('AbsenceType');
        $this->assertTrue($this->isVisible('accrual_expiration_duration'));
        $this->assertTrue($this->isVisible('s2id_accrual_expiration_unit'));
        $this->assertFalse($this->isChecked('accrual_never_expire'));
    }

    public function testCarryForwardFieldsVisibility()
    {
        $this->loginAsAdmin();
        $this->openAddForm();

        // Allow carry forward should not be checked and all the options fields
        // should not be visible
        $this->assertFalse($this->isChecked('allow_carry_forward'));
        $this->assertFalse($this->isVisible('max_number_of_days_to_carry_forward'));
        $this->assertFalse($this->isVisible('carry_forward_expiration_duration'));
        $this->assertFalse($this->isVisible('s2id_carry_forward_expiration_unit'));

        // When carry forward is checked, some fields become visible
        $this->click('allow_carry_forward');
        $this->assertTrue($this->isVisible('max_number_of_days_to_carry_forward'));
        $this->assertFalse($this->isVisible('carry_forward_expiration_duration'));
        $this->assertFalse($this->isVisible('s2id_carry_forward_expiration_unit'));
        $this->assertTrue($this->isChecked('carry_forward_never_expire'));
        $this->assertFalse($this->isChecked('carry_forward_expire_after_duration'));

        // When expire after a certain duration is checked, the duration fields become visible
        $this->click('carry_forward_expire_after_duration');
        $this->assertTrue($this->isVisible('max_number_of_days_to_carry_forward'));
        $this->assertTrue($this->isVisible('carry_forward_expiration_duration'));
        $this->assertTrue($this->isVisible('s2id_carry_forward_expiration_unit'));
        $this->assertFalse($this->isChecked('carry_forward_never_expire'));
        $this->assertTrue($this->isChecked('carry_forward_expire_after_duration'));

        // When Never expire is checked, the duration fields become hidden
        $this->click('carry_forward_never_expire');
        $this->assertTrue($this->isVisible('max_number_of_days_to_carry_forward'));
        $this->assertFalse($this->isVisible('carry_forward_expiration_duration'));
        $this->assertFalse($this->isVisible('s2id_carry_forward_expiration_unit'));
        $this->assertTrue($this->isChecked('carry_forward_never_expire'));
        $this->assertFalse($this->isChecked('carry_forward_expire_after_duration'));

        // If we submit the form with only the expire duration not empty,
        // both duration fields should still be visible after the page reloads
        // (it will reload because the other required fields have not been filled)
        $this->click('carry_forward_expire_after_duration');
        $this->type('carry_forward_expiration_duration', 10);
        $this->submitAndWait('AbsenceType');
        $this->assertTrue($this->isVisible('carry_forward_expiration_duration'));
        $this->assertTrue($this->isVisible('s2id_carry_forward_expiration_unit'));
        $this->assertFalse($this->isChecked('carry_forward_never_expire'));
        $this->assertTrue($this->isChecked('carry_forward_expire_after_duration'));

        // If we submit the form with only the expire unit not empty,
        // both duration fields should still be visible after the page reloads
        // (it will reload because the other required fields have not been filled)
        $this->click('carry_forward_expire_after_duration');
        $this->type('carry_forward_expiration_duration', '');
        $this->select('carry_forward_expiration_unit', 'label=Months');
        $this->submitAndWait('AbsenceType');
        $this->assertTrue($this->isVisible('carry_forward_expiration_duration'));
        $this->assertTrue($this->isVisible('s2id_carry_forward_expiration_unit'));
        $this->assertFalse($this->isChecked('carry_forward_never_expire'));
        $this->assertTrue($this->isChecked('carry_forward_expire_after_duration'));

        // If we submit the form with both the expire duration and unit not empty,
        // the duration fields should still be visible after the page reloads
        // (it will reload because the other required fields have not been filled)
        $this->click('carry_forward_expire_after_duration');
        $this->type('carry_forward_expiration_duration', 15);
        $this->select('carry_forward_expiration_unit', 'label=Months');
        $this->submitAndWait('AbsenceType');
        $this->assertTrue($this->isVisible('carry_forward_expiration_duration'));
        $this->assertTrue($this->isVisible('s2id_carry_forward_expiration_unit'));
        $this->assertFalse($this->isChecked('carry_forward_never_expire'));
        $this->assertTrue($this->isChecked('carry_forward_expire_after_duration'));
    }

    public function testAddAnEmptyType()
    {
        $this->loginAsAdmin();
        $this->openAddForm();
        $this->submitAndWait('AbsenceType', 5);
        $this->assertTrue($this->isTextPresent('Title is a required field.'));
        $this->assertTrue($this->isTextPresent('Calendar Colour is a required field.'));
        $this->assertTrue($this->isTextPresent('Default entitlement is a required field.'));
    }

    public function testCanAddTypeWithMinimumRequiredFields()
    {
        $this->loginAsAdmin();
        $title = $this->addAbsenceTypeWithMinimumRequiredFields();
        $firstTdOfLastRow = 'xpath=//div[@class="form-item"]/table/tbody/tr[last()]/td[1]';
        $this->assertElementContainsText($firstTdOfLastRow, $title);
    }

    public function testDeleteButtonIsNotAvailableOnAdd()
    {
        $this->loginAsAdmin();
        $this->openAddForm();
        $this->assertEquals(0, $this->getXpathCount("id('_qf_AbsenceType_delete-bottom')"));
    }

    public function testDeleteButtonIsAvailableOnEdit()
    {
        $this->loginAsAdmin();
        $this->addAbsenceTypeWithMinimumRequiredFields();
        $this->editLastInsertedAbsenceType();
        $this->assertEquals(1, $this->getXpathCount("id('_qf_AbsenceType_delete-bottom')"));
    }

    public function testDeleteButtonIsNotAvailableWhileEditingReservedTypes()
    {
        $this->loginAsAdmin();
        $reservedTypesIds = [1, 2, 3];
        foreach($reservedTypesIds as $id) {
            $this->openEditFormForId($id);
            $this->assertEquals(0, $this->getXpathCount("id('_qf_AbsenceType_delete-bottom')"));
        }
    }

    public function testCanDeleteNotReservedType()
    {
        $this->loginAsAdmin();
        $title = $this->addAbsenceTypeWithMinimumRequiredFields();
        $this->editLastInsertedAbsenceType();
        $this->click("xpath=id('_qf_AbsenceType_delete-bottom')");
        $confirmationDialog = "xpath=//div[contains(@class, 'crm-confirm-dialog')]";
        $this->waitForElementPresent($confirmationDialog);
        $this->assertElementContainsText($confirmationDialog, 'Are you sure you want to delete this leave/absence type?');
        $confirmationDialogYesButton = "xpath=//div[@class='ui-dialog-buttonset']/button[1]";
        $this->clickAndWait($confirmationDialogYesButton);
        $firstTdOfLastRow = 'xpath=//div[@class="form-item"]/table/tbody/tr[last()]/td[1]';
        $this->assertElementNotContainsText($firstTdOfLastRow, $title);
    }

    public function testIsReservedIsNotAvailableOnAdd()
    {
        $this->loginAsAdmin();
        $this->openAddForm();
        $this->assertEquals(0, $this->getXpathCount("id('is_reserved')"));
    }

    public function testCannotSetIncompleteToilExpiration()
    {
        $this->loginAsAdmin();
        $this->openAddForm();
        $this->click('allow_accruals_request');
        $this->click('accrual_never_expire');

        // First we try it without informing the expiration unit
        $this->type('accrual_expiration_duration', 10);
        $this->submitAndWait('AbsenceType');
        $expirationUnitError = "xpath=//select[@id='accrual_expiration_unit']/following-sibling::span";
        $this->assertElementContainsText($expirationUnitError, 'You must also set the expiration unit');

        // Now we try without informing the expiration duration
        $this->type('accrual_expiration_duration', '');
        $this->select('accrual_expiration_unit', 'label=Months');
        $this->submitAndWait('AbsenceType');
        $expirationUnitError = "xpath=//input[@id='accrual_expiration_duration']/following-sibling::span";
        $this->assertElementContainsText($expirationUnitError, 'You must also set the expiration duration');
    }

    public function testCannotSetIncompleteCarryForwardExpirationDuration()
    {
        $this->loginAsAdmin();
        $this->openAddForm();
        $this->click('allow_carry_forward');
        $this->click('carry_forward_expire_after_duration');

        // First we try it without informing the expiration unit
        $this->type('carry_forward_expiration_duration', 10);
        $this->submitAndWait('AbsenceType');
        $expirationUnitError = "xpath=//select[@id='carry_forward_expiration_unit']/following-sibling::span";
        $this->assertElementContainsText($expirationUnitError, 'You must also set the expiration unit');

        // Now we try without informing the expiration duration
        $this->type('carry_forward_expiration_duration', '');
        $this->select('carry_forward_expiration_unit', 'label=Months');
        $this->submitAndWait('AbsenceType');
        $expirationUnitError = "xpath=//input[@id='carry_forward_expiration_duration']/following-sibling::span";
        $this->assertElementContainsText($expirationUnitError, 'You must also set the expiration duration');
    }

    public function testNumericFieldsShouldOnlyAllowPositiveNumbers()
    {
        $numericFields = [
            'default_entitlement',
            'max_consecutive_leave_days',
            'max_leave_accrual',
            'max_number_of_days_to_carry_forward',
            'carry_forward_expiration_duration',
        ];

        $this->loginAsAdmin();
        $this->openAddForm();

        // first we must show the toil and carry forward fields
        $this->click('allow_carry_forward');
        $this->click('carry_forward_expire_after_duration');
        $this->click('allow_accruals_request');
        $this->click('accrual_never_expire');

        foreach($numericFields as $field) {
            $this->type($field, CRM_Utils_String::createRandom(5, 'abcdefghijklmnopqrstuvwxyz'));
        }

        $this->submitAndWait('AbsenceType');
        foreach($numericFields as $field) {
            $elementError = "xpath=//input[@id='$field']/following-sibling::span";
            $this->assertElementContainsText($elementError, 'The value should be a positive number');
        }
    }

    private function openAddForm()
    {
        $this->openCiviPage($this->formUrl, $this->addUrlParams);
    }

    private function openEditFormForId($id)
    {
        $this->openCiviPage($this->formUrl, $this->editUrlParams . "&id=$id");
    }

    /**
     * @return string
     */
    private function addAbsenceTypeWithMinimumRequiredFields()
    {
        $this->openAddForm();
        $title = 'Title ' . microtime();
        $mysqlUnsignedIntergerMaxValue = 4294967295;
        $this->type('title', $title);
        $this->type('default_entitlement', rand(0, $mysqlUnsignedIntergerMaxValue));
        $this->type('color', '#000000');
        $this->submitAndWait('AbsenceType');

        return $title;
    }

    private function editLastInsertedAbsenceType()
    {
        $editLinkOfLastRow = 'xpath=//table/tbody/tr[last()]/td[last()]/span/a[1]';
        $this->clickLink($editLinkOfLastRow);
    }

}
