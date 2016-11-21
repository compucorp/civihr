<?php

use CRM_HRLeaveAndAbsences_Service_PublicHolidayLeaveRequest as PublicHolidayLeaveRequestService;
use CRM_HRLeaveAndAbsences_Service_PublicHolidayLeaveRequestCreation as PublicHolidayLeaveRequestCreation;
use CRM_HRLeaveAndAbsences_Service_PublicHolidayLeaveRequestDeletion as PublicHolidayLeaveRequestDeletion;

/**
* Class CRM_HRLeaveAndAbsences_Service_PublicHolidayLeaveRequestTest
*
* @group headless
*/
class CRM_HRLeaveAndAbsences_Service_PublicHolidayLeaveRequestTest extends BaseHeadlessTest {

  public function testUpdateAllLeaveRequestsInTheFuture() {
    $deletionLogicMock = $this->getMockBuilder(PublicHolidayLeaveRequestDeletion::class)
                              ->disableOriginalConstructor()
                              ->setMethods(['deleteAllInTheFuture'])
                              ->getMock();

    $deletionLogicMock->expects($this->once())
                      ->method('deleteAllInTheFuture');

    $creationLogicMock = $this->getMockBuilder(PublicHolidayLeaveRequestCreation::class)
                              ->disableOriginalConstructor()
                              ->setMethods(['createForAllInTheFuture'])
                              ->getMock();

    $creationLogicMock->expects($this->once())
                      ->method('createForAllInTheFuture');

    $service = new PublicHolidayLeaveRequestService($creationLogicMock, $deletionLogicMock);
    $service->updateAllLeaveRequestsInTheFuture();
  }

  public function testUpdateAllInTheFutureForContract() {
    $contactID = 10;

    $deletionLogicMock = $this->getMockBuilder(PublicHolidayLeaveRequestDeletion::class)
                              ->disableOriginalConstructor()
                              ->setMethods(['deleteAllForContract'])
                              ->getMock();

    $deletionLogicMock->expects($this->once())
                      ->method('deleteAllForContract')
                      ->with($this->identicalTo($contactID));

    $creationLogicMock = $this->getMockBuilder(PublicHolidayLeaveRequestCreation::class)
                              ->disableOriginalConstructor()
                              ->setMethods(['createAllForContract'])
                              ->getMock();

    $creationLogicMock->expects($this->once())
                      ->method('createAllForContract')
                      ->with($this->identicalTo($contactID));

    $service = new PublicHolidayLeaveRequestService($creationLogicMock, $deletionLogicMock);
    $service->updateAllInTheFutureForContract($contactID);
  }
}
