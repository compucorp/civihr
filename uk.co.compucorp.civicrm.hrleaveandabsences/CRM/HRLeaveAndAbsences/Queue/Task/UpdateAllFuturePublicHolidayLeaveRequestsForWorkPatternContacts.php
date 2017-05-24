<?php

use CRM_HRLeaveAndAbsences_Factory_PublicHolidayLeaveRequestService as PublicHolidayLeaveRequestServiceFactory;

/**
 * This is the Queue Task which will be executed by the whenever the
 * PublicHolidayLeaveRequestUpdates queue is processed.
 *
 * Basically, it uses the PublicHolidayLeaveRequest service to update all leave
 * requests for public holidays in the future for contacts using the Work Pattern
 */
class CRM_HRLeaveAndAbsences_Queue_Task_UpdateAllFuturePublicHolidayLeaveRequestsForWorkPatternContacts {

  /**
   * @param \CRM_Queue_TaskContext $ctx
   * @param int $workPatternID
   */
  public static function run(CRM_Queue_TaskContext $ctx, $workPatternID) {
    $service = PublicHolidayLeaveRequestServiceFactory::create();
    $service->updateAllInTheFutureForWorkPatternContacts($workPatternID);
  }
}
