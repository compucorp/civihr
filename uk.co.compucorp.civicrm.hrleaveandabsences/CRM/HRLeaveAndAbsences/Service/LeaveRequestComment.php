<?php

use CRM_HRLeaveAndAbsences_BAO_LeaveRequest as LeaveRequest;
use CRM_HRLeaveAndAbsences_Service_LeaveManager as LeaveManagerService;

class CRM_HRLeaveAndAbsences_Service_LeaveRequestComment {

  /**
   * @var string
   */
  private $entityName = 'LeaveRequest';

  /**
   * Uses the API exposed by the Comment entity (Comment.create)
   * to create comments related to a LeaveRequest.
   *
   * @param array $params
   *
   * @throws UnexpectedValueException
   *
   * @return array
   */
  public function add($params) {
    //updates are not allowed for now
    if (!empty($params['comment_id'])){
      throw new UnexpectedValueException('You cannot update a comment!');
    }

    $results = $this->callCommentAPI('create', $params);

    if($results['count'] > 0) {
      $this->filterCommentReturnData($results['values']);
    }

    return $results;
  }

  /**
   * Uses the API exposed by the Comment entity (Comment.get)
   * to fetch comments associated with a LeaveRequest
   *
   * @param array $params
   *
   * @return array
   */
  public function get($params) {
    $results = $this->callCommentAPI('get', $params);

    if($results['count'] > 0) {
      $this->filterCommentReturnData($results['values']);
    }

    return $results;
  }

  /**
   * Uses the API exposed by the Comment entity (Comment.delete)
   * to delete comments associated with a LeaveRequest.
   * This method also implement some checks to ensure that only the LeaveRequest Approver
   * or an Admin can delete a comment
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
    $comments = $this->callCommentAPI('get', $params);

    if ($comments['count'] > 0) {
      $leaveRequest = LeaveRequest::findById($comments['values'][0]['entity_id']);
      $leaveManagerService = new LeaveManagerService();

      if ($leaveManagerService->currentUserIsAdmin() || $leaveManagerService->currentUserIsLeaveManagerOf($leaveRequest->contact_id)) {
        return $this->callCommentAPI('delete', $params);
      }

      throw new UnexpectedValueException('You must either be an L&A admin or an approver to this leave request to be able to delete the comment');
    }

    throw new InvalidArgumentException('Comment does not exist or has been deleted already!');
  }

  /**
   * Helper function used to format the parameters
   * into a format expected by the Comment.create, Comment.delete and Comment.get API
   *
   * @param array $params
   *
   * @return array
   */
  private function prepareParametersForCommentPayload($params) {
    $params['entity_name'] = $this->entityName;

    if (!empty($params['comment_id'])) {
      $params['id'] = $params['comment_id'];
      unset($params['comment_id']);
    }

    if (!empty($params['leave_request_id'])) {
      $params['entity_id'] = $params['leave_request_id'];
      unset($params['leave_request_id']);
    }

    return $params;
  }

  /**
   * Helper function used to process the return values of the Comment.get and Comment.create API
   * into a proper format.
   *
   * @param $values
   */
  private function filterCommentReturnData(&$values) {
    array_walk($values, function(&$item){
      $item = ['comment_id' => $item['id'], 'leave_request_id' => $item['entity_id']] + $item;
      unset($item['entity_id'], $item['id'], $item['entity_name']);
    });
  }

  /**
   * Helper function to make calls to the Comments API.
   *
   * @param string $action
   * @param array $params
   *
   * @return array
   */
  private function callCommentAPI($action, $params) {
    $params = $this->prepareParametersForCommentPayload($params);

    return civicrm_api3('Comment', $action, $params);
  }
}
