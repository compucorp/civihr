<?php

use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;
use CRM_HRLeaveAndAbsences_Service_LeaveManager as LeaveManagerService;

class CRM_HRLeaveAndAbsences_Service_LeaveRequestAttachment {

  /**
   * Uses the Attachment API to delete an attachment associated with a LeaveRequest.
   * This method also implement some checks to ensure that only the LeaveRequest Approver
   * or an Admin can delete attachments for a leave request
   *
   * @param array $params
   *
   * @throws UnexpectedValueException
   * @throws InvalidArgumentException
   *
   * @return array
   */
  public function delete($params) {
    $params['sequential'] = 1;
    $attachment = $this->callAttachmentAPI('get', $params);

    if ($attachment['count'] > 0) {
      $leaveRequest = LeaveRequest::findById($attachment['values'][0]['entity_id']);
      $leaveManagerService = new LeaveManagerService();

      if ($leaveManagerService->currentUserIsAdmin() || $leaveManagerService->currentUserIsLeaveManagerOf($leaveRequest->contact_id)) {
        return $this->callAttachmentAPI('delete', $params);
      }

      throw new UnexpectedValueException('You must either be an L&A admin or an approver to this leave request to be able to delete the attachment');
    }

    throw new InvalidArgumentException('Attachment does not exist or has been deleted already!');
  }

  /**
   * Helper function used to format the parameters
   * into a format expected by the Attachment.create, Attachment.delete and Attachment.get API
   *
   * @param array $params
   *
   * @return array
   */
  private function prepareParametersForAttachmentPayload($params) {
    $params['entity_table'] = CRM_HRLeaveAndAbsences_BAO_LeaveRequest::getTableName();

    if (!empty($params['leave_request_id'])) {
      $params['entity_id'] = $params['leave_request_id'];
      unset($params['leave_request_id']);
    }

    if (!empty($params['attachment_id'])) {
      $params['id'] = $params['attachment_id'];
      unset($params['attachment_id']);
    }

    return $params;
  }

  /**
   * Helper function to make calls to the Attachment API.
   *
   * @param string $action
   * @param array $params
   *
   * @return array
   */
  private function callAttachmentAPI($action, $params) {
    $params = $this->prepareParametersForAttachmentPayload($params);

    return civicrm_api3('Attachment', $action, $params);
  }
}
