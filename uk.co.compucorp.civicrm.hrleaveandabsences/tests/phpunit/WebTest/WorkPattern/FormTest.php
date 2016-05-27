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

    public function testIsEnabledIsSelectedOnAdd()
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
        $this->clickOnTheCalendarTab();
        $this->assertTrue($this->isTextPresent('Please inform the Time From'));
        $this->assertTrue($this->isTextPresent('Please inform the Time To'));
        $this->assertTrue($this->isTextPresent('Please inform the Break'));
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

    public function testOnlyTheDetailsTabsIsVisibleWhenOpeningTheForm()
    {
      $this->loginAsAdmin();
      $this->openAddForm();
      $this->assertTrue($this->isVisible('work-pattern-details'));
      $this->assertFalse($this->isVisible('work-pattern-calendar'));
    }

    public function testTheCalendarHasOnlyOneVisibleWeekWithFiveWorkingDaysOnAdd()
    {
      $this->loginAsAdmin();
      $this->openAddForm();
      $this->clickOnTheCalendarTab();
      $this->assertIsSelected('number_of_weeks', 1);
      $this->assertEquals(1, $this->getNumberOfVisibleWeeks());
      $this->assertWeekIsInInitialState(0);
    }

    public function testCanChangeTheNumberOfVisibleWeeksOnTheCalendarTab()
    {
      $this->loginAsAdmin();
      $this->openAddForm();
      $this->clickOnTheCalendarTab();
      $this->assertIsSelected('number_of_weeks', 1);
      $this->assertEquals(1, $this->getNumberOfVisibleWeeks());

      $this->setNumberOfVisibleWeeks(2);
      $this->assertEquals(2, $this->getNumberOfVisibleWeeks());

      $this->setNumberOfVisibleWeeks(3);
      $this->assertEquals(3, $this->getNumberOfVisibleWeeks());

      $this->setNumberOfVisibleWeeks(4);
      $this->assertEquals(4, $this->getNumberOfVisibleWeeks());

      $this->setNumberOfVisibleWeeks(5);
      $this->assertEquals(5, $this->getNumberOfVisibleWeeks());

      $this->setNumberOfVisibleWeeks(4);
      $this->assertEquals(4, $this->getNumberOfVisibleWeeks());

      $this->setNumberOfVisibleWeeks(3);
      $this->assertEquals(3, $this->getNumberOfVisibleWeeks());

      $this->setNumberOfVisibleWeeks(2);
      $this->assertEquals(2, $this->getNumberOfVisibleWeeks());

      $this->setNumberOfVisibleWeeks(1);
      $this->assertEquals(1, $this->getNumberOfVisibleWeeks());
    }

    public function testDaysValuesAreResetWhenWeekIsHidden()
    {
      $this->loginAsAdmin();
      $this->openAddForm();
      $this->clickOnTheCalendarTab();
      $this->setNumberOfVisibleWeeks(2);
      $this->assertWeekIsInInitialState(1);
      $this->fillDay(0, 0, [
        'time_from' => '11:00',
        'time_to' => '13:00',
        'break' => '1',
        'leave_days' => '1'
      ]);
      $this->setNumberOfVisibleWeeks(1);
      $this->setNumberOfVisibleWeeks(2);
      $this->assertWeekIsInInitialState(1);
    }

    public function testFieldsAreErasedAndDisabledWhenWorkDayTypeChangesToNonWorkingDay()
    {
      $this->setUpBasicCalendarTest();
      $this->fillDay(0, 0, [
        'time_from' => '11:00',
        'time_to' => '13:00',
        'break' => '1',
        'leave_days' => '1'
      ]);
      $this->select("weeks_0_days_0_type", "No");
      $this->assertDayIsDisabled(0, 0);
      $this->assertDayIsEmpty(0, 0);

      $this->select("weeks_0_days_0_type", "Yes");
      $this->fillDay(0, 0, [
        'time_from' => '11:00',
        'time_to' => '13:00',
        'break' => '1',
        'leave_days' => '1'
      ]);
      $this->select("weeks_0_days_0_type", "Weekend");
      $this->assertDayIsDisabled(0, 0);
      $this->assertDayIsEmpty(0, 0);
    }

    public function testCannotSavePatternWithInvalidHoursAndBreak()
    {
      $this->setUpBasicCalendarTest();

      $this->fillDay(0, 0, [
        'time_from' => 'dsafdasf',
      ]);
      $this->submitAndWait('WorkPattern');
      $prefix = $this->getDayPrefix(0, 0);
      $timeFromError = "xpath=//input[@id='{$prefix}time_from']/following-sibling::span";
      $this->assertElementContainsText($timeFromError, 'Invalid hour');

      $this->fillDay(0, 0, [
        'time_from' => '09:00',
        'time_to' => 'ewqewqewq',
      ]);
      $this->submitAndWait('WorkPattern');
      $timeToError = "xpath=//input[@id='{$prefix}time_to']/following-sibling::span";
      $this->assertElementContainsText($timeToError, 'Invalid hour');

      $this->fillDay(0, 0, [
        'time_from' => '09:00',
        'time_to' => '15:00',
        'break' => 'dasdsdas'
      ]);
      $this->submitAndWait('WorkPattern');
      $breakError = "xpath=//input[@id='{$prefix}break']/following-sibling::span";
      $this->assertElementContainsText($breakError, 'Break should be a valid number');
    }

    public function testCannotSavePatternWithTimeFromGreaterThanTimeTo()
    {
      $this->setUpBasicCalendarTest();
      $this->fillDay(0, 0, [
        'time_from' => '13:00',
        'time_to' => '08:00'
      ]);
      $this->submitAndWait('WorkPattern');
      $prefix = $this->getDayPrefix(0, 0);
      $timeFromError = "xpath=//input[@id='{$prefix}time_from']/following-sibling::span";
      $this->assertElementContainsText($timeFromError, 'Time From should be less than Time To');
    }

    public function testCannotSavePatternWithBreakHoursGreaterThanWorkingHours()
    {
      $this->setUpBasicCalendarTest();
      $this->fillDay(0, 0, [
        'time_from' => '10:00',
        'time_to' => '11:00',
        'break' => '2'
      ]);
      $this->submitAndWait('WorkPattern');
      $prefix = $this->getDayPrefix(0, 0);
      $breakError = "xpath=//input[@id='{$prefix}break']/following-sibling::span";
      $this->assertElementContainsText($breakError, 'Break should be less than the number of hours between Time From and Time To');
    }

    public function testWhenEditingAPatternWithMoreThanOneWeekAllOfItsWeeksAreVisible()
    {
      $this->loginAsAdmin();
      $label = $this->addWorkPatternWithTwoWeeks();
      $firstTdOfLastRow = 'xpath=//div[@class="form-item"]/table/tbody/tr[last()]/td[1]';
      $this->assertElementContainsText($firstTdOfLastRow, $label);
      $this->editLastInsertedWorkPattern();
      $this->clickOnTheCalendarTab();
      $this->assertEquals(2, $this->getNumberOfVisibleWeeks());
      $this->assertElementValueEquals('number_of_weeks', 2);
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
        $this->clickOnTheCalendarTab();
        $this->assertWeekIsInInitialState(0);
        $this->fillDay(0, 0, [
          'time_from' => '09:00',
          'time_to' => '18:00',
          'break' => '1',
          'leave_days' => '1'
        ]);
        $this->fillDay(0, 1, [
          'time_from' => '09:00',
          'time_to' => '18:00',
          'break' => '1',
          'leave_days' => '1'
        ]);
        $this->fillDay(0, 2, [
          'time_from' => '09:00',
          'time_to' => '18:00',
          'break' => '1',
          'leave_days' => '1'
        ]);
        $this->fillDay(0, 3, [
          'time_from' => '09:00',
          'time_to' => '18:00',
          'break' => '1',
          'leave_days' => '1'
        ]);
        $this->fillDay(0, 4, [
          'time_from' => '09:00',
          'time_to' => '18:00',
          'break' => '1',
          'leave_days' => '1'
        ]);
        $this->submitAndWait('WorkPattern');

        return $label;
    }

    private function editLastInsertedWorkPattern()
    {
        $editLinkOfLastRow = 'xpath=//table/tbody/tr[last()]/td[last()]/span/a[1]';
        $this->clickLink($editLinkOfLastRow);
    }

    private function clickOnTheCalendarTab()
    {
      $this->click('tab_calendar');
    }

    private function getNumberOfVisibleWeeks()
    {
      return $this->getXpathCount('//div[@class="work-pattern-week"]');
    }

    private function assertWeekIsInInitialState($weekIndex)
    {
      $this->assertIsSelected("weeks_{$weekIndex}_days_0_type", 2);
      $this->assertIsSelected("weeks_{$weekIndex}_days_1_type", 2);
      $this->assertIsSelected("weeks_{$weekIndex}_days_2_type", 2);
      $this->assertIsSelected("weeks_{$weekIndex}_days_3_type", 2);
      $this->assertIsSelected("weeks_{$weekIndex}_days_4_type", 2);
      $this->assertIsSelected("weeks_{$weekIndex}_days_5_type", 3);
      $this->assertIsSelected("weeks_{$weekIndex}_days_6_type", 3);

      $this->assertElementValueEquals("weeks_{$weekIndex}_days_0_time_from", '');
      $this->assertElementValueEquals("weeks_{$weekIndex}_days_1_time_from", '');
      $this->assertElementValueEquals("weeks_{$weekIndex}_days_2_time_from", '');
      $this->assertElementValueEquals("weeks_{$weekIndex}_days_3_time_from", '');
      $this->assertElementValueEquals("weeks_{$weekIndex}_days_4_time_from", '');
      $this->assertElementValueEquals("weeks_{$weekIndex}_days_5_time_from", '');
      $this->assertElementValueEquals("weeks_{$weekIndex}_days_6_time_from", '');

      $this->assertElementValueEquals("weeks_{$weekIndex}_days_0_time_to", '');
      $this->assertElementValueEquals("weeks_{$weekIndex}_days_1_time_to", '');
      $this->assertElementValueEquals("weeks_{$weekIndex}_days_2_time_to", '');
      $this->assertElementValueEquals("weeks_{$weekIndex}_days_3_time_to", '');
      $this->assertElementValueEquals("weeks_{$weekIndex}_days_4_time_to", '');
      $this->assertElementValueEquals("weeks_{$weekIndex}_days_5_time_to", '');
      $this->assertElementValueEquals("weeks_{$weekIndex}_days_6_time_to", '');

      $this->assertElementValueEquals("weeks_{$weekIndex}_days_0_break", '');
      $this->assertElementValueEquals("weeks_{$weekIndex}_days_1_break", '');
      $this->assertElementValueEquals("weeks_{$weekIndex}_days_2_break", '');
      $this->assertElementValueEquals("weeks_{$weekIndex}_days_3_break", '');
      $this->assertElementValueEquals("weeks_{$weekIndex}_days_4_break", '');
      $this->assertElementValueEquals("weeks_{$weekIndex}_days_5_break", '');
      $this->assertElementValueEquals("weeks_{$weekIndex}_days_6_break", '');

      $this->assertElementValueEquals("weeks_{$weekIndex}_days_0_number_of_hours", '');
      $this->assertElementValueEquals("weeks_{$weekIndex}_days_1_number_of_hours", '');
      $this->assertElementValueEquals("weeks_{$weekIndex}_days_2_number_of_hours", '');
      $this->assertElementValueEquals("weeks_{$weekIndex}_days_3_number_of_hours", '');
      $this->assertElementValueEquals("weeks_{$weekIndex}_days_4_number_of_hours", '');
      $this->assertElementValueEquals("weeks_{$weekIndex}_days_5_number_of_hours", '');
      $this->assertElementValueEquals("weeks_{$weekIndex}_days_6_number_of_hours", '');

      $this->assertIsSelected("weeks_{$weekIndex}_days_0_leave_days", 0);
      $this->assertIsSelected("weeks_{$weekIndex}_days_1_leave_days", 0);
      $this->assertIsSelected("weeks_{$weekIndex}_days_2_leave_days", 0);
      $this->assertIsSelected("weeks_{$weekIndex}_days_3_leave_days", 0);
      $this->assertIsSelected("weeks_{$weekIndex}_days_4_leave_days", 0);
      $this->assertIsSelected("weeks_{$weekIndex}_days_5_leave_days", 0);
      $this->assertIsSelected("weeks_{$weekIndex}_days_6_leave_days", 0);
    }

    private function setNumberOfVisibleWeeks($number)
    {
      $this->select('number_of_weeks', "value=$number");
    }

    private function fillDay($weekIndex, $dayIndex, $params)
    {
      $prefix = $this->getDayPrefix($weekIndex, $dayIndex);
      if(array_key_exists('type', $params)) {
        $this->select("{$prefix}type", "value={$params['type']}");
      }

      if(array_key_exists('time_from', $params)) {
        $this->type("{$prefix}time_from", $params['time_from']);
      }

      if(array_key_exists('time_to', $params)) {
        $this->type("{$prefix}time_to", $params['time_to']);
      }

      if(array_key_exists('break', $params)) {
        $this->type("{$prefix}break", $params['break']);
      }

      if(array_key_exists('leave_days', $params)) {
        $this->select("{$prefix}leave_days", "value={$params['leave_days']}");
      }
    }

    private function assertDayIsDisabled($weekIndex, $dayIndex)
    {
      $prefix = $this->getDayPrefix($weekIndex, $dayIndex);
      $this->assertElementIsDisabled("{$prefix}time_from");
      $this->assertElementIsDisabled("{$prefix}time_to");
      $this->assertElementIsDisabled("{$prefix}break");
      $this->assertElementIsDisabled("{$prefix}leave_days");
    }

    private function assertDayIsEmpty($weekIndex, $dayIndex)
    {
      $prefix = $this->getDayPrefix($weekIndex, $dayIndex);
      $this->assertElementValueEquals("{$prefix}time_from", '');
      $this->assertElementValueEquals("{$prefix}time_to", '');
      $this->assertElementValueEquals("{$prefix}break", '');
      $this->assertElementValueEquals("{$prefix}number_of_hours", '');
      $this->assertElementValueEquals("{$prefix}number_of_hours", '');
      $this->assertIsSelected("{$prefix}leave_days", 0);
    }

    private function getDayPrefix($weekIndex, $dayIndex)
    {
      return "weeks_{$weekIndex}_days_{$dayIndex}_";
    }

    private function assertElementIsDisabled($elementId)
    {
      $xpathSelector = "//*[@id='$elementId' and @disabled]";
      $this->assertEquals(1, $this->getXpathCount($xpathSelector));
    }

    private function setUpBasicCalendarTest()
    {
      $this->loginAsAdmin();
      $this->openAddForm();
      $this->clickOnTheCalendarTab();
      $this->assertWeekIsInInitialState(0);
    }

    private function addWorkPatternWithTwoWeeks()
    {
      $this->openAddForm();
      $label = 'Label ' . microtime();
      $this->type('label', $label);
      $this->clickOnTheCalendarTab();
      $this->setNumberOfVisibleWeeks(2);
      $this->assertWeekIsInInitialState(0);
      $this->assertWeekIsInInitialState(1);

      $this->fillDay(0, 0, [
        'time_from' => '09:00',
        'time_to' => '18:00',
        'break' => '1',
        'leave_days' => '1'
      ]);
      $this->fillDay(0, 1, [
        'time_from' => '09:00',
        'time_to' => '18:00',
        'break' => '1',
        'leave_days' => '1'
      ]);
      $this->fillDay(0, 2, [
        'time_from' => '09:00',
        'time_to' => '18:00',
        'break' => '1',
        'leave_days' => '1'
      ]);
      $this->fillDay(0, 3, [
        'time_from' => '09:00',
        'time_to' => '18:00',
        'break' => '1',
        'leave_days' => '1'
      ]);
      $this->fillDay(0, 4, [
        'time_from' => '09:00',
        'time_to' => '18:00',
        'break' => '1',
        'leave_days' => '1'
      ]);

      $this->fillDay(1, 0, [
        'time_from' => '09:00',
        'time_to' => '18:00',
        'break' => '1',
        'leave_days' => '1'
      ]);
      $this->fillDay(1, 1, [
        'time_from' => '09:00',
        'time_to' => '18:00',
        'break' => '1',
        'leave_days' => '1'
      ]);
      $this->fillDay(1, 2, [
        'time_from' => '09:00',
        'time_to' => '18:00',
        'break' => '1',
        'leave_days' => '1'
      ]);
      $this->fillDay(1, 3, [
        'time_from' => '09:00',
        'time_to' => '18:00',
        'break' => '1',
        'leave_days' => '1'
      ]);
      $this->fillDay(1, 4, [
        'time_from' => '09:00',
        'time_to' => '18:00',
        'break' => '1',
        'leave_days' => '1'
      ]);

      $this->submitAndWait('WorkPattern');

      return $label;
    }

}
