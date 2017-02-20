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

    unset($params['is_deleted']);

    $instance = new self();
    $instance->copyValues($params);

    $instance->created_at = CRM_Utils_Date::processDate('now');

    if ($hook == 'create') {
      $instance->created_at = isset($params['created_at']) ? $params['created_at'] : CRM_Utils_Date::processDate('now');
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
    self::validateCommentSoftDeleteDuringUpdate($params);
    self::validateCommentCreatedAtDate($params);
  }

  /**
   * A method for validating that a comment cannot be soft deleted
   * during an update on the BAO
   *
   * @param array $params
   *   The params array received by the create method
   *
   * @throws \CRM_HRComments_Exception_InvalidCommentException
   */
  public static function validateCommentSoftDeleteDuringUpdate($params) {
    if (isset($params['id']) && !empty($params['is_deleted'])) {
      throw new InvalidCommentException(
        'Comment can not be soft deleted during an update, use the delete method instead!',
        'comment_cannot_soft_delete_comment',
        'is_deleted'
      );
    }
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

  /**
   * This method validates the created_at date parameter passed
   * when creating a comment. It verifies that the created_at date
   * is less than the created_at date of the last comment for the same entity
   *
   * @param array $params
   *   The params array received by the create method
   *
   * @throws \CRM_HRComments_Exception_InvalidCommentException
   */
  private static function validateCommentCreatedAtDate($params) {
    if (!empty($params['id']) || empty($params['created_at'])) {
      return;
    }

    $lastComment = self::getLastCommentForEntity($params['entity_name'], $params['entity_id']);
    if (!$lastComment) {
      return;
    }

    $createdAt = new DateTime($params['created_at']);
    $lastCommentCreatedAt = new DateTime($lastComment->created_at);

    if ($createdAt < $lastCommentCreatedAt) {
      throw new InvalidCommentException(
        'The created_at date must not be less than the last comment created date for this Entity',
        'comment_created_at_less_than_last_comment_created_at',
        'created_at'
      );
    }
  }

  /**
   * Soft Deletes the comment with the given ID by setting the is_deleted column to 1
   *
   * @param int $id The ID of the comment to be soft deleted
   *
   * @return boolean
   */
  public static function softDelete($id) {
    $comment = self::findById($id);
    $comment->is_deleted = 1;
    $comment->save();
  }

  /**
   * This method fetches the last/latest comment for the given entityName and entityID
   *
   * @param string $entityName
   * @param int $entityID
   *
   * @return \CRM_HRComments_BAO_Comment|null
   */
  private static function getLastCommentForEntity($entityName, $entityID) {
    $comment  = new self();
    $comment->orderBy('created_at DESC');
    $comment->entity_id = $entityID;
    $comment->entity_name = $entityName;
    $comment->find(true);

    if($comment->id) {
      return $comment;
    }

    return null;
  }
}
