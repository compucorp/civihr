<?php

use CRM_HRLeaveAndAbsences_Service_LeaveRequestComment as LeaveRequestCommentService;
use CRM_HRLeaveAndAbsences_Service_LeaveRequestAttachment as LeaveRequestAttachmentService;
use CRM_HRLeaveAndAbsences_Service_LeaveRequestMailNotification as LeaveRequestMailNotificationService;

/**
 * A factory for the LeaveRequestMailNotification service, which can be used
 * to get instances of this service without having to manually create all of
 * its dependencies
 */
class CRM_HRLeaveAndAbsences_Factory_LeaveRequestMailNotificationService {

  /**
   * Returns a new instance of a LeaveRequestMailNotification Service
   *
   * @return \CRM_HRLeaveAndAbsences_Service_LeaveRequestMailNotification
   */
  public static function create() {
    $leaveRequestCommentService = new LeaveRequestCommentService();
    $leaveRequestAttachmentService = new LeaveRequestAttachmentService();

    return new LeaveRequestMailNotificationService(
      $leaveRequestCommentService,
      $leaveRequestAttachmentService
    );
  }
}
