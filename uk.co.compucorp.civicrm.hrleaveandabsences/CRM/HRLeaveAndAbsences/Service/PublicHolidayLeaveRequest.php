<?php

use CRM_HRLeaveAndAbsences_Service_PublicHolidayLeaveRequestCreation as CreationLogic;
use CRM_HRLeaveAndAbsences_Service_PublicHolidayLeaveRequestDeletion as DeletionLogic;

class CRM_HRLeaveAndAbsences_Service_PublicHolidayLeaveRequest {

  /**
   * @var \CRM_HRLeaveAndAbsences_Service_PublicHolidayLeaveRequestCreation
   */
  private $creationLogic;

  /**
   * @var \CRM_HRLeaveAndAbsences_Service_PublicHolidayLeaveRequestDeletion
   */
  private $deletionLogic;

  public function __construct(CreationLogic $creationLogic, DeletionLogic $deletionLogic) {
    $this->creationLogic = $creationLogic;
    $this->deletionLogic = $deletionLogic;
  }

  /**
   * Updates all the Leave Requests for Public Holidays in the future.
   *
   * Basically, this uses the Deletion Logic to delete all the leave requests
   * for public holidays in the future and, then, uses the Creation Logic to
   * create leave requests to public holidays in the future.
   *
   * @see CRM_HRLeaveAndAbsences_Service_PublicHolidayLeaveRequestDeletion::deleteAllInTheFuture()
   * @see CRM_HRLeaveAndAbsences_Service_PublicHolidayLeaveRequestCreation::createForAllInTheFuture()
   */
  public function updateAllLeaveRequestsInTheFuture() {
    $this->deletionLogic->deleteAllInTheFuture();
    $this->creationLogic->createForAllInTheFuture();
  }

}
