<?php

class CRM_HRLeaveAndAbsences_Mail_Template_LeaveRequestNotificationTemplate
  extends CRM_HRLeaveAndAbsences_Mail_Template_BaseRequestNotificationTemplate {

  /**
   * {@inheritdoc}
   */
  public function getTemplateID() {
    $result = civicrm_api3('MessageTemplate', 'get', [
      'msg_title' => 'CiviHR Leave Request Notification',
      'is_default' => 1
    ]);

    return isset($result['id']) ? $result['id'] : '';
  }
}
