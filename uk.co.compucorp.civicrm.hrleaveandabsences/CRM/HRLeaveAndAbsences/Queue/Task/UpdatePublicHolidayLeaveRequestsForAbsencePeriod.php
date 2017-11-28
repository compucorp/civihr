<?php

use CRM_HRLeaveAndAbsences_Factory_PublicHolidayLeaveRequestService as PublicHolidayLeaveRequestServiceFactory;

/**
 * This is the Queue Task which will be executed by the whenever the
 * PublicHolidayLeaveRequestUpdates queue is processed.
 *
 * Basically, it uses the PublicHolidayLeaveRequest service to update all leave
 * requests for public holidays in the absence period for contacts having contracts
 * within the period.
 * If contactID is not empty, the update is performed for these contacts only.
 */
class CRM_HRLeaveAndAbsences_Queue_Task_UpdatePublicHolidayLeaveRequestsForAbsencePeriod {

  /**
   * @param \CRM_Queue_TaskContext $ctx
   * @param int $absencePeriodID
   * @param array $contactID
   */
  public static function run(CRM_Queue_TaskContext $ctx, $absencePeriodID, array $contactID = []) {
    $service = PublicHolidayLeaveRequestServiceFactory::create();
    $service->updateAllForAbsencePeriod($absencePeriodID, $contactID);
  }
}
