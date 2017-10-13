<?php

use CRM_HRLeaveAndAbsences_Factory_PublicHolidayLeaveRequestService as PublicHolidayLeaveRequestServiceFactory;

/**
 * This is the Queue Task which will be executed by the whenever the
 * PublicHolidayLeaveRequestUpdates queue is processed.
 *
 * Basically, it uses the PublicHolidayLeaveRequest service to update all leave
 * requests for public holidays in the future
 */
class CRM_HRLeaveAndAbsences_Queue_Task_UpdateAllFuturePublicHolidayLeaveRequests {

  public static function run(CRM_Queue_TaskContext $ctx) {
    $service = PublicHolidayLeaveRequestServiceFactory::create();
    $service->updateAllInTheFuture();
  }

}
