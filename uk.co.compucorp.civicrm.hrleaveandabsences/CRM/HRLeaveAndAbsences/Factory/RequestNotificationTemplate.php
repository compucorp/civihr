<?php

use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;
use CRM_HRLeaveAndAbsences_Service_LeaveRequestComment as LeaveRequestCommentService;
use CRM_HRLeaveAndAbsences_Mail_Template_SicknessRequestNotificationTemplate as SicknessRequestNotificationTemplate;
use CRM_HRLeaveAndAbsences_Mail_Template_TOILRequestNotificationTemplate as TOILRequestNotificationTemplate;
use CRM_HRLeaveAndAbsences_Mail_Template_LeaveRequestNotificationTemplate as LeaveRequestNotificationTemplate;


class CRM_HRLeaveAndAbsences_Factory_RequestNotificationTemplate {

  /**
   * Returns a new instance of a template class extending from the parent
   * BaseRequestNotificationTemplate class based on the Request Type of the given
   * Leave Request.
   *
   * @param \CRM_HRLeaveAndAbsences_BAO_LeaveRequest $leaveRequest
   *
   * @return \CRM_HRLeaveAndAbsences_Mail_Template_BaseRequestNotificationTemplate
   */
  public function create(LeaveRequest $leaveRequest) {
    $leaveRequestCommentService = new LeaveRequestCommentService();

    switch ($leaveRequest->request_type) {
      case LeaveRequest::REQUEST_TYPE_SICKNESS:
        return new SicknessRequestNotificationTemplate($leaveRequestCommentService);
        break;
      case LeaveRequest::REQUEST_TYPE_LEAVE:
        return new LeaveRequestNotificationTemplate($leaveRequestCommentService);
        break;
      case LeaveRequest::REQUEST_TYPE_TOIL:
        return new TOILRequestNotificationTemplate($leaveRequestCommentService);
        break;
    }
  }
}
