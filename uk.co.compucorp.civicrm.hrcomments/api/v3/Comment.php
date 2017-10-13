<?php

/**
 * Comment.create API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_comment_create_spec(&$spec) {
  // $spec['some_parameter']['api.required'] = 1;
}

/**
 * Comment.create API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_comment_create($params) {
  $results =  _civicrm_api3_basic_create(_civicrm_api3_get_BAO(__FUNCTION__), $params);

  if($results['count'] > 0){
    array_walk($results['values'], function (&$item) {
      unset($item['is_deleted']);
    });
  }

  return $results;
}

/**
 * Comment.delete API
 *
 * @param array $params
 * @return array API result descriptor
 * @throws API_Exception
 */
function civicrm_api3_comment_delete($params) {
  civicrm_api3_verify_mandatory($params, NULL, ['id']);
  CRM_HRComments_BAO_Comment::softDelete($params['id']);
  return civicrm_api3_create_success();
}

/**
 * Comment.get API
 * This API returns comments that are not deleted
 * i.e comments with is_deleted flag false.
 *
 * @param array $params
 *
 * @return array API result descriptor
 *
 * @throws API_Exception
 */
function civicrm_api3_comment_get($params) {
  $query = new CRM_HRComments_API_Query_CommentSelect($params);
  return civicrm_api3_create_success($query->run(), $params, '', 'get');
}

