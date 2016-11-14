<?php

class CRM_HRAbsence_Queue_EntitlementRecalculation {

  const QUEUE_NAME = 'org.civicrm.hrabsence.queue.entitlementrecalculation';

  private static $queue;

  /**
   * Returns the Queue object wrapped by this class
   *
   * @return \CRM_Queue_Queue
   */
  public static function getQueue() {
    if(!self::$queue) {
      self::$queue = CRM_Queue_Service::singleton()->create([
        'type' => 'Sql',
        'name' => self::QUEUE_NAME,
        'reset' => false,
      ]);
    }

    return self::$queue;
  }
}
