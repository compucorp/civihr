<?php

use CRM_HRComments_Test_Fabricator_Comment as CommentFabricator;
use CRM_HRComments_BAO_Comment as Comment;

/**
 * Class api_v3_CommentTest
 *
 * @group headless
 */
class api_v3_CommentTest extends BaseHeadlessTest  {

  public function setUp() {
    CRM_Core_DAO::executeQuery("SET foreign_key_checks = 0;");
  }

  public function tearDown() {
    CRM_Core_DAO::executeQuery("SET foreign_key_checks = 1;");
  }

  public function testDeleteDoesNotDeleteCommentsFromCommentsTableButSetsIsDeletedFlagToOne() {
    $comment1 = CommentFabricator::fabricate([
      'entity_id' => 1,
      'contact_id' => 1,
    ]);

    $comment2 = CommentFabricator::fabricate([
      'entity_id' => 2,
      'contact_id' => 2,
    ]);

    //soft delete the first comment
    civicrm_api3('Comment', 'delete', [
      'id' => $comment1->id,
    ]);

    $comment = new Comment();
    $comment->find();
    $this->assertEquals($comment->N, 2);

    $comment->fetch();
    $this->assertEquals($comment->id, $comment1->id);
    $this->assertEquals(1, $comment->is_deleted);

    $comment->fetch();
    $this->assertEquals($comment->id, $comment2->id);
    $this->assertEquals(0, $comment->is_deleted);
  }

  /**
   * @expectedException CiviCRM_API3_Exception
   * @expectedExceptionMessage Unable to find a CRM_HRComments_BAO_Comment with id 2.
   */
  public function testDeleteThrowsExceptionWhenCommentIdDoesNotExist() {
    //soft delete the first comment
    civicrm_api3('Comment', 'delete', [
      'id' => 2,
    ]);
  }

  public function testGetDoesNotIncludeSoftDeletedCommentsAndIsDeletedColumn() {
    $entityName = 'LeaveRequest';
    $comment1 = CommentFabricator::fabricate([
      'entity_id' => 1,
      'entity_name' => $entityName,
      'contact_id' => 1,
    ]);

    $comment2 = CommentFabricator::fabricate([
      'entity_id' => 2,
      'entity_name' => $entityName,
      'contact_id' => 1,
    ]);

    $comment3 = CommentFabricator::fabricate([
      'entity_id' => 3,
      'entity_name' => $entityName,
      'contact_id' => 1,
    ]);

    //soft delete the second comment
    civicrm_api3('Comment', 'delete', [
      'id' => $comment2->id,
    ]);

    $result = civicrm_api3('Comment', 'get', [
      'entity_name' => $entityName,
      'contact_id' => 1,
      'sequential' => 1,
    ]);

    $comment1Date  = new DateTime($comment1->created_at);
    $comment1FormattedDate = $comment1Date->format('Y-m-d H:i:s');

    $comment3Date  = new DateTime($comment3->created_at);
    $comment3FormattedDate = $comment3Date->format('Y-m-d H:i:s');
    $expectedValues = [
      [
        'id' => $comment1->id,
        'entity_name' => $comment1->entity_name,
        'entity_id' => $comment1->entity_id,
        'text' => $comment1->text,
        'contact_id' => $comment1->contact_id,
        'created_at' => $comment1FormattedDate,
      ],
      [
        'id' => $comment3->id,
        'entity_name' => $comment3->entity_name,
        'entity_id' => $comment3->entity_id,
        'text' => $comment3->text,
        'contact_id' => $comment3->contact_id,
        'created_at' => $comment3FormattedDate,
      ],
    ];

    $this->assertEquals($expectedValues, $result['values']);
  }

  public function testGetDoesNotIncludeSoftDeletedCommentsAndIsDeletedColumnWhenReturnArrayIncludesIsDeleted() {
    $entityName = 'LeaveRequest';
    $comment1 = CommentFabricator::fabricate([
      'entity_id' => 1,
      'entity_name' => $entityName,
      'contact_id' => 1,
    ]);

    $comment2 = CommentFabricator::fabricate([
      'entity_id' => 2,
      'entity_name' => $entityName,
      'contact_id' => 1,
    ]);

    $comment3 = CommentFabricator::fabricate([
      'entity_id' => 3,
      'entity_name' => $entityName,
      'contact_id' => 1,
    ]);

    //soft delete the second comment
    civicrm_api3('Comment', 'delete', [
      'id' => $comment2->id,
    ]);

    $result = civicrm_api3('Comment', 'get', [
      'entity_name' => $entityName,
      'contact_id' => 1,
      'sequential' => 1,
      'return' => ['id', 'entity_name', 'entity_id', 'is_deleted']
    ]);

    $expectedValues = [
      [
        'id' => $comment1->id,
        'entity_name' => $comment1->entity_name,
        'entity_id' => $comment1->entity_id,
      ],
      [
        'id' => $comment3->id,
        'entity_name' => $comment3->entity_name,
        'entity_id' => $comment3->entity_id,
      ],
    ];

    $this->assertEquals($expectedValues, $result['values']);
  }

  public function testCreateDoesNotReturnIsDeletedFieldInReturnedResults() {
    $results = civicrm_api3('Comment', 'create', [
      'entity_id' => 1,
      'entity_name' => 'LeaveRequest',
      'text' => 'This is a sample comment',
      'contact_id' => 1,
      'sequential' => 1,
    ]);

    $comment = new Comment();
    $comment->find(true);
    $date = new DateTime($comment->created_at);

    $expectedValues = [
      [
        'id' => $comment->id,
        'entity_name' => $comment->entity_name,
        'entity_id' => $comment->entity_id,
        'text' => $comment->text,
        'contact_id' => $comment->contact_id,
        'created_at' => $date->format('YmdHis')
      ],
    ];
    $this->assertEquals($expectedValues, $results['values']);
  }
}
