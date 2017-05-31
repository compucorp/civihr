<?php

use CRM_HRLeaveAndAbsences_BAO_PublicHoliday as PublicHoliday;
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
  public function updateAllInTheFuture() {
    $this->deletionLogic->deleteAllInTheFuture();
    $this->creationLogic->createForAllInTheFuture();
  }

  /**
   * Updates all the Leave Requests for Public Holidays in the future between
   * the start and end dates of the given contract.
   *
   * @param int $contractID
   *
   * @see CRM_HRLeaveAndAbsences_Service_PublicHolidayLeaveRequestDeletion::deleteAllForContract()
   * @see CRM_HRLeaveAndAbsences_Service_PublicHolidayLeaveRequestCreation::createAllForContract()
   */
  public function updateAllForContract($contractID) {
    $this->deletionLogic->deleteAllForContract($contractID);
    $this->creationLogic->createAllForContract($contractID);
  }

  /**
   * Creates Leave Requests for all the contacts with contracts overlapping the
   * date of the given Public Holiday
   *
   * @param PublicHoliday $publicHoliday
   */
  public function createForAllContacts(PublicHoliday $publicHoliday) {
    $this->creationLogic->createForAllContacts($publicHoliday);
  }

  /**
   * Deletes Leave Requests for the given Public Holiday from all the contacts
   * with contracts overlapping the date of the given Public Holiday
   *
   * @param PublicHoliday $publicHoliday
   */
  public function deleteForAllContacts(PublicHoliday $publicHoliday) {
    $this->deletionLogic->deleteForAllContacts($publicHoliday);
  }

  /**
   * Updates all the Leave Requests for Public Holidays in the future for the
   * contacts using given WorkPattern. If it is the default Work Pattern, It updates for all
   * contacts.
   *
   * @param int $workPatternID
   *
   * @see CRM_HRLeaveAndAbsences_Service_PublicHolidayLeaveRequestDeletion::deleteAllInTheFutureForWorkPatternContacts()
   * @see CRM_HRLeaveAndAbsences_Service_PublicHolidayLeaveRequestCreation::createAllInFutureForWorkPatternContacts()
   */
  public function updateAllInTheFutureForWorkPatternContacts($workPatternID) {
    $this->deletionLogic->deleteAllInTheFutureForWorkPatternContacts($workPatternID);
    $this->creationLogic->createAllInFutureForWorkPatternContacts($workPatternID);
  }

}
