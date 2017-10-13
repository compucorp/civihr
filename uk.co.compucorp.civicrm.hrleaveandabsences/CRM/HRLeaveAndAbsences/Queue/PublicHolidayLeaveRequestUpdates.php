<?php

class CRM_HRLeaveAndAbsences_Queue_PublicHolidayLeaveRequestUpdates {

  const QUEUE_NAME = 'hrleaveandabsences.publicholidayleaverequestupdates';

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

  /**
   * Adds a new tasks to the queue
   *
   * @param object $task
   * @param array $options
   */
  public static function createItem($task, $options = []) {
    self::getQueue()->createItem($task, $options);
  }
}
