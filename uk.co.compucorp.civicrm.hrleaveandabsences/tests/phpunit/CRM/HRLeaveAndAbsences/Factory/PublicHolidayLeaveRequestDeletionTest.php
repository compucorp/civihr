<?php

use CRM_HRLeaveAndAbsences_Service_PublicHolidayLeaveRequestDeletion as PublicHolidayLeaveRequestDeletion;
use CRM_HRLeaveAndAbsences_Factory_PublicHolidayLeaveRequestDeletion as PublicHolidayLeaveRequestDeletionFactory;

/**
 * CRM_HRLeaveAndAbsences_Factory_PublicHolidayLeaveRequestDeletionTes
 *
 * @group headless
 */
class CRM_HRLeaveAndAbsences_Factory_PublicHolidayLeaveRequestDeletionTest extends BaseHeadlessTest  {

  public function testCreate() {
    $service = PublicHolidayLeaveRequestDeletionFactory::create();

    $this->assertInstanceOf(PublicHolidayLeaveRequestDeletion::class, $service);
  }
}
