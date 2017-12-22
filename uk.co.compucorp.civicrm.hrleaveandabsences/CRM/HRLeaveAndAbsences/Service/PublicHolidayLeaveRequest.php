<?php

use CRM_HRLeaveAndAbsences_BAO_PublicHoliday as PublicHoliday;
use CRM_HRLeaveAndAbsences_Service_PublicHolidayLeaveRequestCreation as CreationLogic;
use CRM_HRLeaveAndAbsences_Service_PublicHolidayLeaveRequestDeletion as DeletionLogic;
use CRM_HRLeaveAndAbsences_BAO_AbsencePeriod as AbsencePeriod;

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
   * @param array $contactID
   *
   * @see CRM_HRLeaveAndAbsences_Service_PublicHolidayLeaveRequestDeletion::deleteAllInTheFuture()
   * @see CRM_HRLeaveAndAbsences_Service_PublicHolidayLeaveRequestCreation::createForAllInTheFuture()
   */
  public function updateAllInTheFuture(array $contactID = []) {
    $this->deletionLogic->deleteAllInTheFuture($contactID);
    $this->creationLogic->createAllInTheFuture($contactID);
  }

  /**
   * Creates/Updates all the Leave Requests for Public Holidays in all absence periods.
   * Those already created in the past are left untouched.
   *
   * In a situation where a public holiday was added in the past when there is no
   * absence type with MTPHL = Yes, when the absence type's MTPHL is set to Yes,
   * leave requests will be created for all public holidays including those in the
   * past with no leave requests associated or those that does not have leave requests
   * created for all qualified contacts.
   *
   * Basically, this uses the Deletion Logic to delete all the leave requests
   * for public holidays in the future and then uses the Creation Logic to
   * create leave requests for all public holidays.
   *
   * @see CRM_HRLeaveAndAbsences_Service_PublicHolidayLeaveRequestDeletion::deleteAllInTheFuture()
   * @see CRM_HRLeaveAndAbsences_Service_PublicHolidayLeaveRequestCreation::createForAll()
   */
  public function updateAll() {
    $this->deletionLogic->deleteAllInTheFuture();
    $this->creationLogic->createAll();
  }

  /**
   * Updates all the Leave Requests for Public Holidays between
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

  /**
   * Updates all the leave requests for Public Holidays for the absence
   * period for the contacts with contracts during the period.
   *
   * If contactID is present, it will update only for the contacts in the
   * array.
   *
   * @param int $absencePeriodID
   * @param array $contactID
   *
   * @see CRM_HRLeaveAndAbsences_Service_PublicHolidayLeaveRequestDeletion::deleteAllForAbsencePeriod()
   * @see CRM_HRLeaveAndAbsences_Service_PublicHolidayLeaveRequestCreation::createForAllinAbsencePeriod()
   */
  public function updateAllForAbsencePeriod($absencePeriodID, array $contactID = []) {
    $absencePeriod = AbsencePeriod::findById($absencePeriodID);
    $this->deletionLogic->deleteAllForAbsencePeriod($absencePeriod, $contactID);
    $this->creationLogic->createAllForAbsencePeriod($absencePeriod, $contactID);
  }
}
