<?php

use CRM_HRCore_String_TimeUnitApplier as TimeUnitApplier;

/**
 * @group headless
 */
class CRM_HRCore_String_TimeUnitApplierTest extends CRM_HRCore_Test_BaseHeadlessTest {

  /**
   * @dataProvider nonNumericValues
   *
   * @expectedException InvalidArgumentException
   * @expectedExceptionMessage Value should be a valid number
   */
  public function testThrowsAnExceptionWhenTheValueIsNotNumeric($value) {
    TimeUnitApplier::apply($value, TimeUnitApplier::UNIT_HOURS);
  }

  /**
   * @expectedException InvalidArgumentException
   * @expectedExceptionMessage Invalid unit
   */
  public function testThrowsAnExceptionWhenTheUnitIsInvalid() {
    TimeUnitApplier::apply(1, 'blablabla');
  }

  public function testAppliesUnitsForDays() {
    $this->assertEquals('-2d', TimeUnitApplier::apply(-2, 'days'));
    $this->assertEquals('0d', TimeUnitApplier::apply(0, 'days'));
    $this->assertEquals('0.3d', TimeUnitApplier::apply(0.3, 'days'));
    $this->assertEquals('0.3d', TimeUnitApplier::apply('0.3', 'days'));
    $this->assertEquals('0.3d', TimeUnitApplier::apply('.3', 'days'));
    $this->assertEquals('0.99d', TimeUnitApplier::apply(0.99, 'days'));
    $this->assertEquals('1d', TimeUnitApplier::apply(1, 'days'));
    $this->assertEquals('5d', TimeUnitApplier::apply(5, 'days'));
    $this->assertEquals('7.78d', TimeUnitApplier::apply(7.78, 'days'));
  }

  public function testAppliesUnitsForHours() {
    $this->assertEquals('-2h', TimeUnitApplier::apply(-2, 'hours'));
    $this->assertEquals('-30m', TimeUnitApplier::apply(-0.5, 'hours'));
    $this->assertEquals('-15m', TimeUnitApplier::apply(-0.01, 'hours'));
    $this->assertEquals('0h', TimeUnitApplier::apply(0, 'hours'));
    $this->assertEquals('15m', TimeUnitApplier::apply(0.01, 'hours'));
    $this->assertEquals('30m', TimeUnitApplier::apply(0.3, 'hours'));
    $this->assertEquals('30m', TimeUnitApplier::apply('0.3', 'hours'));
    $this->assertEquals('30m', TimeUnitApplier::apply('.3', 'hours'));
    $this->assertEquals('1h', TimeUnitApplier::apply(0.874, 'hours'));
    $this->assertEquals('1h', TimeUnitApplier::apply(0.875, 'hours'));
    $this->assertEquals('1h', TimeUnitApplier::apply(0.876, 'hours'));
    $this->assertEquals('1h', TimeUnitApplier::apply(0.99, 'hours'));
    $this->assertEquals('1h', TimeUnitApplier::apply(1, 'hours'));
    $this->assertEquals('1h 15m', TimeUnitApplier::apply(1.01, 'hours'));
    $this->assertEquals('1h 15m', TimeUnitApplier::apply(1.124, 'hours'));
    $this->assertEquals('1h 15m', TimeUnitApplier::apply(1.125, 'hours'));
    $this->assertEquals('1h 15m', TimeUnitApplier::apply(1.126, 'hours'));
    $this->assertEquals('5h', TimeUnitApplier::apply(5, 'hours'));
    $this->assertEquals('8h', TimeUnitApplier::apply(7.78, 'hours'));
    $this->assertEquals('20h 45m', TimeUnitApplier::apply(20.74, 'hours'));
    $this->assertEquals('20h 45m', TimeUnitApplier::apply(20.75, 'hours'));
    $this->assertEquals('21h', TimeUnitApplier::apply(20.76, 'hours'));
  }

  public function nonNumericValues() {
    return [
      [null],
      [false],
      [true],
      [''],
      ['string'],
      [' '],
      [[]],
      [new stdClass()]
    ];
  }

}
