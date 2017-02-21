<?php

use CRM_HRComments_BAO_Comment as Comment;
use CRM_HRComments_Test_Fabricator_Comment as CommentFabricator;

/**
 * Class CRM_HRComments_BAO_CommentTest
 *
 * @group headless
 */
class CRM_HRComments_BAO_CommentTest extends BaseHeadlessTest  {

  public function setUp() {
    CRM_Core_DAO::executeQuery("SET foreign_key_checks = 0;");
  }

  public function tearDown() {
    CRM_Core_DAO::executeQuery("SET foreign_key_checks = 1;");
  }
  /**
   * @expectedException CRM_HRComments_Exception_InvalidCommentException
   * @expectedExceptionMessage Comment should have associated entity ID
   */
  public function testValidateCommentWhenEntityIdIsNotPresent() {
    Comment::validateParams([
      'entity_name' => 'LeaveRequest',
      'text' => 'This is a sample comment',
      'contact_id' => 1,
    ]);
  }

  /**
   * @expectedException CRM_HRComments_Exception_InvalidCommentException
   * @expectedExceptionMessage Comment should have associated entity name
   */
  public function testValidateCommentWhenEntityNameIsNotPresent() {
    Comment::validateParams([
      'entity_id' => 1,
      'text' => 'This is a sample comment',
      'contact_id' => 1,
    ]);
  }

  /**
   * @expectedException CRM_HRComments_Exception_InvalidCommentException
   * @expectedExceptionMessage Comment should have text
   */
  public function testValidateCommentWhenTextIsNotPresent() {
    Comment::validateParams([
      'entity_id' => 1,
      'entity_name' => 'LeaveRequest',
      'contact_id' => 1,
    ]);
  }

  /**
   * @expectedException CRM_HRComments_Exception_InvalidCommentException
   * @expectedExceptionMessage Contact who made the comment should not be empty
   */
  public function testValidateCommentWhenContactIdIsNotPresent() {
    Comment::validateParams([
      'entity_id' => 1,
      'entity_name' => 'LeaveRequest',
      'text' => 'This is a sample comment',
    ]);
  }

  /**
   * @expectedException CRM_HRComments_Exception_InvalidCommentException
   * @expectedExceptionMessage Contact who made the comment should not be empty
   */
  public function testValidateParamsIsCalledOnCreate() {
    Comment::create([
      'entity_id' => 1,
      'entity_name' => 'LeaveRequest',
      'text' => 'This is a sample comment',
    ]);
  }

  public function testTryingToSoftDeleteCommentOnUpdateThrowsException() {
    $comment1 = Comment::create([
      'entity_id' => 1,
      'entity_name' => 'LeaveRequest',
      'text' => 'This is a sample comment',
      'contact_id' => 1,
    ]);

    $this->setExpectedException('CRM_HRComments_Exception_InvalidCommentException', 'Comment can not be soft deleted during an update, use the delete method instead!');
    Comment::create([
      'id' => $comment1->id,
      'entity_id' => 1,
      'entity_name' => 'LeaveRequest',
      'text' => 'This is a sample comment',
      'contact_id' => 1,
      'is_deleted' => 1
    ]);
  }

  public function testCreatedDateIsInsertedAsTheCurrentDateWhenNotPassedAsAParameter() {
    $comment = Comment::create([
      'entity_id' => 1,
      'entity_name' => 'LeaveRequest',
      'text' => 'This is a sample comment',
      'contact_id' => 1
    ]);

    //the current timestamp is gotten here and asserted
    //that it is within 10 seconds delta compared to the created_date of the comment entity
    $createdDateTimestamp = new DateTime($comment->created_at);
    $timestampNow = new DateTime('now');

    $this->assertEquals($timestampNow, $createdDateTimestamp, '', 10);
  }

  public function testCreatedAtIsInsertedCorrectlyWhenPassedAsAParameter() {
    $created_at = new DateTime('2016-01-01 09:10:10');
    $comment = Comment::create([
      'entity_id' => 1,
      'entity_name' => 'LeaveRequest',
      'text' => 'This is a sample comment',
      'contact_id' => 1,
      'created_at' => $created_at->format('YmdHis')
    ]);

    $commentCreatedDate = new DateTime($comment->created_at);
    $this->assertEquals($created_at, $commentCreatedDate);
  }

  /**
   * @expectedException CRM_HRComments_Exception_InvalidCommentException
   * @expectedExceptionMessage You cannot update the created_at date of a comment
   */
  public function testValidateParamsThrowsAnExceptionWhenTryingToChangeTheCreatedAtDateForACommentDuringAnUpdate() {
    $comment = Comment::create([
      'entity_id' => 1,
      'entity_name' => 'LeaveRequest',
      'text' => 'This is a sample comment',
      'contact_id' => 1,
      'created_at' => CRM_Utils_Date::processDate('2016-01-01 10:09:11')
    ]);

    Comment::validateParams([
      'id' => $comment->id,
      'entity_id' => $comment->entity_id,
      'entity_name' => $comment->entity_name,
      'text' => $comment->text,
      'contact_id' => $comment->contact_id,
      'created_at' => CRM_Utils_Date::processDate('2016-01-01 10:09:15'),
    ]);
  }

  public function testSoftDeleteDoesNotDeleteCommentsFromCommentsTableButSetsIsDeletedFlagToOne() {
    $comment1 = CommentFabricator::fabricate([
      'entity_id' => 1,
      'contact_id' => 1,
    ]);

    $comment2 = CommentFabricator::fabricate([
      'entity_id' => 2,
      'contact_id' => 2,
    ]);

    Comment::softDelete($comment1->id);

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

  public function testIsDeletedWillBeZeroIrrespectiveOfTheValuePassedViaIsDeletedParameterOnCreate() {
    Comment::create([
      'entity_id' => 1,
      'entity_name' => 'LeaveRequest',
      'text' => 'This is a sample comment',
      'contact_id' => 1,
      'is_deleted' => 1
    ]);

    $comment = new Comment();
    $comment->find();
    $this->assertEquals($comment->N, 1);
    $comment->fetch();

    $this->assertEquals(0, $comment->is_deleted);
  }

  public function testIsDeletedCannotBeChangedWhenUpdatingExistingComment() {
    $comment1 = Comment::create([
      'entity_id' => 1,
      'entity_name' => 'LeaveRequest',
      'text' => 'This is a sample comment',
      'contact_id' => 1,
    ]);

    Comment::create([
      'id' => $comment1->id,
      'created_at' => CRM_Utils_Date::processDate('2016-01-01'),
      'entity_name' => 'SickRequest',
      'text' => 'This is a random sample comment',
      'is_deleted' => 1
    ], false);

    $comment = new Comment();
    $comment->find();
    $this->assertEquals($comment->N, 1);
    $comment->fetch();

    $this->assertEquals(0, $comment->is_deleted);
    $this->assertEquals('SickRequest', $comment->entity_name);
    $this->assertEquals('This is a random sample comment', $comment->text);
  }

  public function testCommentCanNotBeCreatedWithACreatedDateLessThanTheCreatedDateOfTheLastCommentForThisEntity() {
    $entityName = 'SickRequest';
    $entityID = 1;

    $comment1 = Comment::create([
      'entity_id' => $entityID,
      'entity_name' => $entityName,
      'text' => 'This is a random sample comment',
      'created_at' => CRM_Utils_Date::processDate('2016-01-01'),
      'contact_id' => 1,
    ], false);

    $comment2 = Comment::create([
      'entity_id' => $entityID,
      'entity_name' => $entityName,
      'text' => 'This is a another sample comment',
      'created_at' => CRM_Utils_Date::processDate('2016-01-02 10:10:30'),
      'contact_id' => 1,
    ], false);

    $comment3 = Comment::create([
      'entity_id' => $entityID,
      'entity_name' => $entityName,
      'text' => 'This is yet another sample comment',
      'created_at' => CRM_Utils_Date::processDate('2016-01-03 10:09:20'),
      'contact_id' => 2,
    ], false);

    $this->setExpectedException('CRM_HRComments_Exception_InvalidCommentException', "The created_at date must not be less than the last comment created date for this Entity");

    //contact tries to manipulate the comment date by setting date to a second before the
    //last comment date for the entity
    Comment::create([
      'entity_name' => $entityName,
      'entity_id' => $entityID,
      'text' => 'This is probably another sample comment',
      'created_at' => CRM_Utils_Date::processDate('2016-01-03 10:09:19'),
      'contact_id' => 1,
    ]);
  }
}
