<?php

/**
 * This class provide a method to apply a time unit to any given numerical
 * value.
 */
class CRM_HRCore_String_TimeUnitApplier {

  const UNIT_HOURS = 'hours';
  const UNIT_DAYS = 'days';

  /**
   * The amount to which the minutes are rounds, when the unit is 'hours'.
   * In this case, we round it up to the nearest quarter of hour, hence 0.25.
   *
   * @var float
   */
  private static $roundMinutes = 0.25;

  /**
   * Applies the given time unit to the given value.
   *
   * The supported time units are 'days' and 'hours'. For days, the unit will be
   * applied by simply adding the 'd' suffix to the given value. Examples:
   * - 2.5 will become 2.5d
   * - 4 will become 4d
   * - 0.3 will become 0.3d
   *
   * For hours, the 'h' and 'm' suffixes will be used. Examples:
   * - 1 will become 1h
   * - 2.25 will become 2h 15m
   * - 0.5 will become 30m
   *
   * Note that, for hours, some rounding will be also applied, and the value
   * will be rounded up to the nearest quarter of an hour: 1.35 will become 1h 30m
   *
   * @param int|float $value
   * @param string $unit
   *
   * @return string
   *   The value with the time unit applied to it
   *
   * @throws \InvalidArgumentException
   *   If either the given $value is not a numerical value or $unit is not a
   *   valid unit
   */
  public static function apply($value, $unit) {
    if(!is_numeric($value)) {
      throw new InvalidArgumentException('Value should be a valid number');
    }

    $value = (float)$value;

    switch ($unit) {
      case self::UNIT_DAYS:
        return self::applyDaysUnit($value);
      case self::UNIT_HOURS:
        return self::applyHoursUnit($value);
      default:
        throw new InvalidArgumentException('Invalid unit');
    }
  }

  /**
   * Apply the days unit by adding the d suffix to the given value
   *
   * @param int|float $value
   *
   * @return string
   */
  private static function applyDaysUnit($value) {
    return "{$value}d";
  }

  /**
   * Applies the hours unit by adding the h and m suffixes to the given value
   * and rounding it up to the nearest quarter of an hour.
   *
   * @param int|float $value
   *
   * @return string
   */
  private static function applyHoursUnit($value) {
    if ($value == 0 ) {
      return '0h';
    }

    $sign = $value < 0 ? '-' : '';
    $value = abs($value);
    $hours = self::getHoursPart($value);
    $remainder = fmod($value, 1);
    $minutes = self::getMinutesPart($remainder);

    return $sign . $hours . ($hours && $minutes ? ' ' : '') . $minutes;
  }

  /**
   * Builds the hours part of the time unit. For real numbers, it will consider
   * only the integer part of it (i.e. for 1.3 it will return 1h)
   *
   * @param int|float $value
   *
   * @return string
   */
  private static function getHoursPart($value) {
    if ($value >= 1 - self::$roundMinutes) {
      return floor(ceil($value / self::$roundMinutes) * self::$roundMinutes) . 'h';
    }

    return '';
  }

  /**
   * Builds the minutes part of the time unit. Here, $value is expected not to
   * be the whole value we wan't to apply the unit to, but only the decimal part,
   * that represents the minutes.
   *
   * @param int|float $value
   *
   * @return string
   */
  private static function getMinutesPart($value) {
    if($value && $value <= 1 - self::$roundMinutes && $value >= 0) {
      return ceil($value / self::$roundMinutes) * self::$roundMinutes * 60 . 'm';
    }

    return '';
  }
}
