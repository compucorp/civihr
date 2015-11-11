<?php

class CRM_Contactsummary_Utils_Aggregate {
  const TYPE_AVERAGE = 'average';
  const TYPE_SUM = 'sum';

  public static function getAverage($total, $count, $precision = 0) {
    return round($total / $count, $precision);
  }
}