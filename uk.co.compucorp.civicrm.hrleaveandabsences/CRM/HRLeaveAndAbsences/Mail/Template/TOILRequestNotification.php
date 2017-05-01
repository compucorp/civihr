<?php

class CRM_HRLeaveAndAbsences_Mail_Template_TOILRequestNotification
  extends CRM_HRLeaveAndAbsences_Mail_Template_BaseRequestNotification {

  /**
   * {@inheritdoc}
   */
  public function getTemplateID() {
    $result = civicrm_api3('MessageTemplate', 'get', [
      'msg_title' => 'CiviHR TOIL Request Notification',
      'is_default' => 1
    ]);

    return isset($result['id']) ? $result['id'] : '';
  }
}
