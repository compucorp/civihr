<?php

trait CRM_HRLeaveAndAbsences_MailHelpersTrait {

  public function createRequestNotificationTemplateFactoryMock($leaveTemplateMock) {
    $templateFactory = $this->getMockBuilder(CRM_HRLeaveAndAbsences_Factory_RequestNotificationTemplate::class)
      ->setMethods(['create'])
      ->getMock();

    $templateFactory->expects($this->once())
      ->method('create')
      ->will($this->returnValue($leaveTemplateMock));

    return $templateFactory;
  }

  private function createLeaveTemplateMock($expectedTemplateParameters = [], $expectedTemplateID = null) {
    $leaveTemplate = $this->getMockBuilder(CRM_HRLeaveAndAbsences_Mail_Template_LeaveRequestNotification::class)
      ->setMethods(['getTemplateParameters', 'getTemplateID'])
      ->setConstructorArgs([new CRM_HRLeaveAndAbsences_Service_LeaveRequestComment()])
      ->getMock();

    $leaveTemplate->expects($this->any())
      ->method('getTemplateParameters')
      ->with($this->isInstanceOf(CRM_HRLeaveAndAbsences_BAO_LeaveRequest::class))
      ->will($this->returnValue($expectedTemplateParameters));

    $leaveTemplate->expects($this->any())
      ->method('getTemplateID')
      ->will($this->returnValue($expectedTemplateID));

    return $leaveTemplate;
  }

  public function getEmailNotificationsFromDatabase($emails) {
    $emails = "'" . implode("','", $emails) . "'";
    $messageSpoolTable = CRM_Mailing_BAO_Spool::getTableName();
    $query = "SELECT * FROM {$messageSpoolTable} WHERE recipient_email 
              IN($emails)";

    return CRM_Core_DAO::executeQuery($query);
  }

  public function deleteEmailNotificationsInDatabase() {
    $messageSpoolTable = CRM_Mailing_BAO_Spool::getTableName();
    CRM_Core_DAO::executeQuery("DELETE FROM {$messageSpoolTable}");
  }

  public function getTemplateDetails($params) {
    $defaultParams = ['is_default' => 1, 'sequential' => 1];
    $params = array_merge($defaultParams, $params);
    $result = civicrm_api3('MessageTemplate', 'get', $params);

    return isset($result['values'][0]) ? $result['values'][0] : '';
  }
}
