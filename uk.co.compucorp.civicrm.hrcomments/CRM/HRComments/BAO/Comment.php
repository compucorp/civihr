<?php

use CRM_HRComments_Exception_InvalidCommentException as InvalidCommentException;

class CRM_HRComments_BAO_Comment extends CRM_HRComments_DAO_Comment {

  /**
   * Create a new Comment based on array-data
   *
   * @param array $params key-value pairs
   * @return CRM_HRComments_DAO_Comment|NULL
   */
  public static function create($params, $validate = true) {
    $entityName = 'Comment';
    $hook = empty($params['id']) ? 'create' : 'edit';

    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);

    if($validate){
      self::validateParams($params);
    }
    unset($params['created_at']);

    $instance = new self();
    $instance->copyValues($params);

    if ($hook == 'create') {
      $instance->created_at = CRM_Utils_Date::processDate('now');
    }

    $instance->save();
    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

    return $instance;
  }

  /**
   * A method for validating the params passed to the Comment create method
   *
   * @param array $params
   *   The params array received by the create method
   *
   * @throws \CRM_HRComments_Exception_InvalidCommentException
   */
  public static function validateParams($params) {
    self::validateMandatory($params);
  }

  /**
   * A method for validating the mandatory fields in the params
   * passed to the Comment create method
   *
   * @param array $params
   *   The params array received by the create method
   *
   * @throws \CRM_HRComments_Exception_InvalidCommentException
   */
  private static function validateMandatory($params) {
    if (empty($params['entity_id'])) {
      throw new InvalidCommentException(
        'Comment should have associated entity ID',
        'comment_empty_entity_id',
        'entity_id'
      );
    }

    if (empty($params['entity_name'])) {
      throw new InvalidCommentException(
        'Comment should have associated entity name',
        'comment_empty_entity_name',
        'entity_name'
      );
    }

    if (empty($params['text'])) {
      throw new InvalidCommentException(
        'Comment should have text',
        'comment_empty_text',
        'text'
      );
    }

    if (empty($params['contact_id'])) {
      throw new InvalidCommentException(
        'Contact who made the comment should not be empty',
        'comment_empty_contact_id',
        'contact_id'
      );
    }
  }
}
