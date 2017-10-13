<?php

abstract class CRM_HRLeaveAndAbsences_Test_Fabricator_SequentialTitle {

  protected static $sequenceNumber = 1;

  protected static function nextSequentialTitle() {
    $title = static::getEntityTitle() . ' ' . static::$sequenceNumber;
    static::$sequenceNumber++;

    return $title;
  }

  protected static function getEntityTitle() {
    $namespaceParts = explode('_', static::class);
    return end($namespaceParts);
  }
}
