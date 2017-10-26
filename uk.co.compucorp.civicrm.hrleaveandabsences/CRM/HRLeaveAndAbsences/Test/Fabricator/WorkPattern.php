<?php

use CRM_HRLeaveAndAbsences_BAO_WorkPattern as WorkPattern;
use CRM_HRLeaveAndAbsences_BAO_WorkDay as WorkDay;
use CRM_HRLeaveAndAbsences_Test_Fabricator_SequentialTitle as SequentialTitle;

class CRM_HRLeaveAndAbsences_Test_Fabricator_WorkPattern extends SequentialTitle  {

  public static function fabricate($params = []) {
    $params = array_merge(self::getDefaultParams(), $params);

    return WorkPattern::create($params);
  }

  public static function fabricateWithA40HourWorkWeek($params = []) {
    $params = array_merge(self::getDefaultParams(), $params);
    $params['weeks'] = self::get40HoursWeekParams();

    return WorkPattern::create($params);
  }

  public static function fabricateWithTwoWeeksAnd31AndHalfHours($params = []) {
    $params = array_merge(self::getDefaultParams(), $params);
    $params['weeks'] = self::getTwoWeeksWith31AndHalfHoursParams();

    return WorkPattern::create($params);
  }

  private static function getDefaultParams() {
    return [
      'label'     => static::nextSequentialTitle(),
      'is_active' => 1
    ];
  }

  /**
   * Returns a set of 7 seven days (monday to sunday), with 5 8h working days (
   * monday to friday) and two weekend days (saturday and sunday)
   *
   * @return array
   */
  private static function get40HoursWeekParams() {
    return [[
      'days' => [
        [
          'type' => WorkDay::getWorkingDayTypeValue(),
          'day_of_week' => 1,
          'time_from' => '09:00',
          'time_to' => '18:00',
          'break' => 1,
          'leave_days' => 1,
          'number_of_hours' => 8
        ],
        [
          'type' => WorkDay::getWorkingDayTypeValue(),
          'day_of_week' => 2,
          'time_from' => '09:00',
          'time_to' => '18:00',
          'break' => 1,
          'leave_days' => 1,
          'number_of_hours' => 8
        ],
        [
          'type' => WorkDay::getWorkingDayTypeValue(),
          'day_of_week' => 3,
          'time_from' => '09:00',
          'time_to' => '18:00',
          'break' => 1,
          'leave_days' => 1,
          'number_of_hours' => 8
        ],
        [
          'type' => WorkDay::getWorkingDayTypeValue(),
          'day_of_week' => 4,
          'time_from' => '09:00',
          'time_to' => '18:00',
          'break' => 1,
          'leave_days' => 1,
          'number_of_hours' => 8
        ],
        [
          'type' => WorkDay::getWorkingDayTypeValue(),
          'day_of_week' => 5,
          'time_from' => '09:00',
          'time_to' => '18:00',
          'break' => 1,
          'leave_days' => 1,
          'number_of_hours' => 8
        ],
        [
          'type' => WorkDay::getWeekendTypeValue(),
          'day_of_week' => 6,
          'leave_days' => 0,
        ],
        [
          'type' => WorkDay::getWeekendTypeValue(),
          'day_of_week' => 7,
          'leave_days' => 0
        ],
      ]
    ]];
  }

  /**
   * Returns a set of 2 weeks, with 7 seven days (monday to sunday) each and 31.5 working hours.
   * The first week has 3 working days (mon, wed, fri) and the second, only two (tue, thu).
   *
   * @return array
   */
  private static function getTwoWeeksWith31AndHalfHoursParams() {
    return [
      [
        'days' => [
          [
            'type'            => WorkDay::getWorkingDayTypeValue(),
            'day_of_the_week' => 1,
            'time_from'       => '07:00',
            'time_to'         => '15:30',
            'break'           => 1,
            'leave_days'      => 1,
            'number_of_hours' => 7.5
          ],
          [
            'type'            => WorkDay::getNonWorkingDayTypeValue(),
            'day_of_the_week' => 2,
          ],
          [
            'type'            => WorkDay::getWorkingDayTypeValue(),
            'day_of_the_week' => 3,
            'time_from'       => '07:00',
            'time_to'         => '15:30',
            'break'           => 1,
            'leave_days'      => 1,
            'number_of_hours' => 7.5
          ],
          [
            'type'            => WorkDay::getNonWorkingDayTypeValue(),
            'day_of_the_week' => 4,
          ],
          [
            'type'            => WorkDay::getWorkingDayTypeValue(),
            'day_of_the_week' => 5,
            'time_from'       => '07:00',
            'time_to'         => '15:30',
            'break'           => 1,
            'leave_days'      => 1,
            'number_of_hours' => 7.5
          ],
          [
            'type'            => WorkDay::getWeekendTypeValue(),
            'day_of_the_week' => 6,
          ],
          [
            'type'            => WorkDay::getWeekendTypeValue(),
            'day_of_the_week' => 7,
          ]
        ]
      ],
      [
        'days' => [
          [
            'type'            => WorkDay::getNonWorkingDayTypeValue(),
            'day_of_the_week' => 1,
          ],
          [
            'type'            => WorkDay::getWorkingDayTypeValue(),
            'day_of_the_week' => 2,
            'time_from'       => '07:00',
            'time_to'         => '12:00',
            'break'           => 0.5,
            'leave_days'      => 1,
            'number_of_hours' => 4.5
          ],
          [
            'type'            => WorkDay::getNonWorkingDayTypeValue(),
            'day_of_the_week' => 3,
          ],
          [
            'type'            => WorkDay::getWorkingDayTypeValue(),
            'day_of_the_week' => 4,
            'time_from'       => '07:00',
            'time_to'         => '12:00',
            'break'           => 0.5,
            'leave_days'      => 1,
            'number_of_hours' => 4.5
          ],
          [
            'type'            => WorkDay::getNonWorkingDayTypeValue(),
            'day_of_the_week' => 5,
          ],
          [
            'type'            => WorkDay::getWeekendTypeValue(),
            'day_of_the_week' => 6,
          ],
          [
            'type'            => WorkDay::getWeekendTypeValue(),
            'day_of_the_week' => 7,
          ]
        ]
      ]
    ];
  }

}
