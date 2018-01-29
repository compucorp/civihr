<?php

use CRM_HRLeaveAndAbsences_Service_JobContract as JobContractService;
use CRM_HRLeaveAndAbsences_Service_PublicHolidayLeaveRequestDeletion as PublicHolidayLeaveRequestDeletion;

/**
 * A factory for the PublicHolidayLeaveRequestDeletion service, which can be used
 * to get instances of this service without having to manually create all of
 * its dependencies
 */
class CRM_HRLeaveAndAbsences_Factory_PublicHolidayLeaveRequestDeletion {

  /**
   * Returns a new instance of a PublicHolidayLeaveRequestDeletion Service
   *
   * @return PublicHolidayLeaveRequestDeletion
   */
  public static function create() {
    $jobContractService = new JobContractService();

    return new PublicHolidayLeaveRequestDeletion($jobContractService);
  }
}
