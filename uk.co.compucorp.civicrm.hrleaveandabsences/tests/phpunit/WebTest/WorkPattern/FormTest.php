<?php

require_once 'CiviTest/CiviSeleniumTestCase.php';

class WebTest_WorkPattern_FormTest extends CiviSeleniumTestCase {

    private $formUrl = 'admin/leaveandabsences/work_patterns';
    private $addUrlParams = 'action=add&reset=1';

    private function loginAsAdmin()
    {
        if(is_null($this->loggedInAs)) {
            $this->webtestLogin('admin');
        }
    }

    public function testIsEnabledIsSelectedWhenOnAdd()
    {
        $this->loginAsAdmin();
        $this->openAddForm();
        $this->assertTrue($this->isChecked('is_active'));
    }

    public function testAddAnEmptyWorkPattern()
    {
        $this->loginAsAdmin();
        $this->openAddForm();
        $this->submitAndWait('WorkPattern');
        $this->assertTrue($this->isTextPresent('Label is a required field.'));
    }

    public function testCanAddTypeWithMinimumRequiredFields()
    {
        $this->loginAsAdmin();
        $label = $this->addWorkPatternWithMinimumRequiredFields();
        $firstTdOfLastRow = 'xpath=//div[@class="form-item"]/table/tbody/tr[last()]/td[1]';
        $this->assertElementContainsText($firstTdOfLastRow, $label);
    }

    public function testDeleteButtonIsNotAvailableOnAdd()
    {
        $this->loginAsAdmin();
        $this->openAddForm();
        $this->assertEquals(0, $this->getXpathCount("id('_qf_WorkPattern_delete-bottom')"));
    }

    public function testDeleteButtonIsAvailableOnEdit()
    {
        $this->loginAsAdmin();
        $this->addWorkPatternWithMinimumRequiredFields();
        $this->editLastInsertedWorkPattern();
        $this->assertEquals(1, $this->getXpathCount("id('_qf_WorkPattern_delete-bottom')"));
    }

    private function openAddForm()
    {
        $this->openCiviPage($this->formUrl, $this->addUrlParams);
    }

    private function addWorkPatternWithMinimumRequiredFields()
    {
        $this->openAddForm();
        $label = 'Label ' . microtime();
        $this->type('label', $label);
        $this->submitAndWait('WorkPattern');

        return $label;
    }

    private function editLastInsertedWorkPattern()
    {
        $editLinkOfLastRow = 'xpath=//table/tbody/tr[last()]/td[last()]/span/a[1]';
        $this->clickLink($editLinkOfLastRow);
    }

}
