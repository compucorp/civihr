<?php

/**
 * Applies the given time unit to the given value
 *
 * Usage:
 *
 * @code
 * {$proposedEntitlement|timeUnitApplier:'hours'}
 * {$balanceChange|timeUnitApplier:'days'}
 * @endcode
 *
 * @param number $value
 *   The value to apply the time unit to. It must be a numerical value
 * @param string $unit
 *   The unit to be applied. Valid values are 'days' and 'hours'
 *
 * @return string
 */
function smarty_modifier_timeUnitApplier($value, $unit) {
  return CRM_HRCore_String_TimeUnitApplier::apply($value, $unit);
}
