<?php

trait WebTest_WorkPattern_FormHelpersTrait {

  protected $formUrl = 'admin/leaveandabsences/work_patterns';
  protected $addUrlParams = 'action=add&reset=1';

  public function clickOnTheCalendarTab() {
    $this->click('tab_calendar');
  }

  public function getNumberOfVisibleWeeks() {
    return $this->getXpathCount('//div[@class="work-pattern-week"]');
  }

  public function setNumberOfVisibleWeeks($number) {
    $this->select('number_of_weeks', "value=$number");
  }

  public function getDayPrefix($weekIndex, $dayIndex) {
    return "weeks_{$weekIndex}_days_{$dayIndex}_";
  }

  public function fillDay($weekIndex, $dayIndex, $params) {
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

  public function openAddForm() {
    $this->openCiviPage($this->formUrl, $this->addUrlParams);
  }

  public function addWorkPatternWithOneWeekAnd40Hours() {
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

  public function addWorkPatternWithTwoWeeks() {
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

  public function assertWeekIsInInitialState($weekIndex) {
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

}
