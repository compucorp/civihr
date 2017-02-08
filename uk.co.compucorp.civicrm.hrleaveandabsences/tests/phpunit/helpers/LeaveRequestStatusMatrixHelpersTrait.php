<?php


trait CRM_HRLeaveAndAbsences_LeaveRequestStatusMatrixHelpersTrait {

  public function createLeaveRequestStatusMatrixServiceMock($canTransitionTo = false) {
    $leaveRequestStatusMatrixService = $this->getMockBuilder(CRM_HRLeaveAndAbsences_Service_LeaveRequestStatusMatrix::class)
                                             ->setConstructorArgs([new CRM_HRLeaveAndAbsences_Service_LeaveManager()])
                                             ->setMethods(['canTransitionTo'])
                                             ->getMock();

    $leaveRequestStatusMatrixService->expects($this->any())
                                    ->method('canTransitionTo')
                                    ->will($this->returnValue($canTransitionTo));


    return $leaveRequestStatusMatrixService;
  }
}
