<?php

use CRM_HRComments_BAO_Comment as Comment;

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

  public function testCreatedDateIsInsertedCorrectly() {
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

  public function testCreatedAtWillBeTheCurrentDateTimeIrrespectiveOfTheDateTimeValuePassedViaCreatedAtParameter() {
    $comment = Comment::create([
      'entity_id' => 1,
      'entity_name' => 'LeaveRequest',
      'text' => 'This is a sample comment',
      'contact_id' => 1,
      'created_at' => CRM_Utils_Date::processDate('2016-01-01')
    ]);

    //the current timestamp is gotten here and asserted
    //that it is within 10 seconds delta compared to the created_date of the comment entity
    $createdDateTimestamp = new DateTime($comment->created_at);
    $timestampNow = new DateTime('now');

    $this->assertEquals($timestampNow, $createdDateTimestamp, '', 10);
  }

  public function testCreatedAtCannotBeChangedWhenUpdatingExistingComment() {
    $comment = Comment::create([
      'entity_id' => 1,
      'entity_name' => 'LeaveRequest',
      'text' => 'This is a sample comment',
      'contact_id' => 1,
    ]);

    $comment2 = Comment::create([
      'id' => $comment->id,
      'created_at' => CRM_Utils_Date::processDate('2016-01-01'),
    ], false);

    $createdDateTimestamp = new DateTime($comment2->created_at);
    $timestampNow = new DateTime('now');

    $this->assertEquals($timestampNow, $createdDateTimestamp, '', 10);
  }
}
