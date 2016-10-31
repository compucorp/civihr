<?php

/**
 * Class CRM_HRLeaveAndAbsences_Validator_DateTest
 *
 * @group headless
 */
class CRM_HRLeaveAndAbsences_BAO_DateTest extends BaseHeadlessTest {

  /**
   * @dataProvider datesDataProvider
   */
  public function testCanValidateDateInDefaultFormat($date, $expectedResult)
  {
    $valid = CRM_HRLeaveAndAbsences_Validator_Date::isValid($date);
    $this->assertEquals($expectedResult, $valid);
  }

  /**
   * @dataProvider datesDataProviderInDifferentFormats
   */
  public function testCanValidateDateInDifferentFormats($date, $format)
  {
    $valid = CRM_HRLeaveAndAbsences_Validator_Date::isValid($date, $format);
    $this->assertTrue($valid);
  }

  public function datesDataProvider()
  {
    return [
      [date('Y'), false],
      [date('Ym'), false],
      [date('Ymd'), false],
      [date('YmdH'), false],
      [date('YmdHi'), false],
      [date('YmdHis'), true],
      [CRM_Utils_Date::processDate(date('Y-m-d')), true],
      [CRM_Utils_Date::processDate(date('Y-m')), true],
      [CRM_Utils_Date::processDate(date('Y')), true],
      [CRM_Utils_Date::processDate(date('m')), true],
      [date('Y-m-d'), false],
      [date('Y-m'), false],
      [date('Y'), false],
      [date('m'), false],
      ['reqwreqwfdssafdsa', false],
      [12312321321, false],
      [null, false],
      ['April 1st', false],
      ['2016-02-30', false],
      ['2013-04-31', false],
      ['2009-07-51', false],
      ['2051-13-01', false],
    ];
  }

  public function datesDataProviderInDifferentFormats()
  {
    return [
      [date('d/m/Y'), 'd/m/Y'],
      [date('Y-m'),'Y-m'],
      [date('Y'), 'Y'],
      [date('m'), 'm'],
    ];
  }
}
