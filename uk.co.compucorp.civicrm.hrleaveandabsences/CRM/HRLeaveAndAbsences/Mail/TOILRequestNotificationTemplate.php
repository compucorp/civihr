<?php

class CRM_HRLeaveAndAbsences_Mail_TOILRequestNotificationTemplate
  extends CRM_HRLeaveAndAbsences_Mail_BaseRequestNotificationTemplate {

  /**
   * {@inheritdoc}
   */
  public function getTemplate() {
    $result = civicrm_api3('MessageTemplate', 'get', [
      'msg_title' => 'CiviHR TOIL Request Notification',
      'is_default' => 1,
      'sequential' => 1
    ]);

    return isset($result['values'][0]) ? $result['values'][0] : '';
  }
}
