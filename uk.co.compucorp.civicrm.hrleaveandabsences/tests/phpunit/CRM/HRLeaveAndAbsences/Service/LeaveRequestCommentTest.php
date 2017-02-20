<?php

use CRM_HRCore_Test_Fabricator_Contact as ContactFabricator;
use CRM_HRComments_Test_Fabricator_Comment as CommentFabricator;
use CRM_HRComments_BAO_Comment as Comment;
use CRM_HRLeaveAndAbsences_Test_Fabricator_LeaveRequest as LeaveRequestFabricator;
use CRM_HRLeaveAndAbsences_Service_LeaveRequestComment as LeaveRequestCommentService;

/**
 * Class CRM_HRLeaveAndAbsences_Service_LeaveRequestCommentTest
 *
 * @group headless
 */
class CRM_HRLeaveAndAbsences_Service_LeaveRequestCommentTest extends BaseHeadlessTest {

  use CRM_HRLeaveAndAbsences_LeaveManagerHelpersTrait;
  use CRM_HRLeaveAndAbsences_SessionHelpersTrait;

  private $leaveRequestCommentService;

  public function setUp() {
    CRM_Core_DAO::executeQuery("SET foreign_key_checks = 0;");

    $this->leaveRequestCommentService = new LeaveRequestCommentService();
  }

  public function tearDown() {
    CRM_Core_DAO::executeQuery("SET foreign_key_checks = 1;");
  }

  public function testAddCanCreateCommentForLeaveRequest() {
    $params = [
      'leave_request_id' => 1,
      'text' => 'Random Commenter',
      'contact_id' => 1,
      'sequential' => 1
    ];

    $result = $this->leaveRequestCommentService->add($params);

    $comment = new Comment();
    $comment->find();
    $this->assertEquals(1, $comment->N);
    $comment->fetch();

    $date = new DateTime($comment->created_at);

    $expected = [
      [
        'comment_id' => $comment->id,
        'leave_request_id' => $comment->entity_id,
        'text' => $comment->text,
        'contact_id' => $comment->contact_id,
        "created_at"=> $date->format('YmdHis')
      ]
    ];

    $this->assertEquals($expected, $result['values']);
  }

  public function testAddCanCreateCommentForLeaveRequestWhenCreatedAtIsPartOfTheParametersPassed() {
    $created_at = new DateTime('2016-10-10 09:20:43');
    $params = [
      'leave_request_id' => 1,
      'text' => 'Random Commenter',
      'contact_id' => 1,
      'created_at' => $created_at->format('Y-m-d H:i:s'),
      'sequential' => 1
    ];

    $result = $this->leaveRequestCommentService->add($params);

    $comment = new Comment();
    $comment->find();
    $this->assertEquals(1, $comment->N);
    $comment->fetch();

    $expected = [
      [
        'comment_id' => $comment->id,
        'leave_request_id' => $comment->entity_id,
        'text' => $comment->text,
        'contact_id' => $comment->contact_id,
        'created_at'=> $created_at->format('YmdHis')
      ]
    ];

    $this->assertEquals($expected, $result['values']);
  }

  public function testAddCannotUpdateCommentForLeaveRequest() {
    $params = [
      'leave_request_id' => 1,
      'text' => 'Random Commenter',
      'contact_id' => 1,
      'sequential' => 1
    ];

    $result = $this->leaveRequestCommentService->add($params);

    $comment = new Comment();
    $comment->find();
    $this->assertEquals(1, $comment->N);
    $comment->fetch();

    $date = new DateTime($comment->created_at);

    $expected = [
      [
        'comment_id' => $comment->id,
        'leave_request_id' => $comment->entity_id,
        'text' => $comment->text,
        'contact_id' => $comment->contact_id,
        "created_at"=> $date->format('YmdHis')
      ]
    ];

    $this->assertEquals($expected, $result['values']);

    //update comment
    $updateParams = [
      'comment_id' => $comment->id,
      'leave_request_id' => 1,
      'text' => 'Test Commenter',
      'contact_id' => 2,
      'sequential' => 1
    ];

    $this->setExpectedException('UnexpectedValueException', 'You cannot update a comment!');
    $this->leaveRequestCommentService->add($updateParams);
  }

  public function testGetReturnsAssociatedCommentsForLeaveRequest() {
    $entityName = 'LeaveRequest';
    $comment1 = CommentFabricator::fabricate([
      'entity_id' => 1,
      'entity_name' => $entityName,
      'contact_id' => 1,
    ]);

    $comment2 = CommentFabricator::fabricate([
      'entity_id' => 1,
      'entity_name' => $entityName,
      'contact_id' => 1,
    ]);

    $comment3 = CommentFabricator::fabricate([
      'entity_id' => 3,
      'entity_name' => $entityName,
      'contact_id' => 1,
    ]);

    $result = $this->leaveRequestCommentService->get(['leave_request_id' => 1, 'sequential' => 1]);

    $comment1Date = new DateTime($comment1->created_at);
    $comment2Date = new DateTime($comment2->created_at);

    $expected1 = [
      [
        'comment_id' => $comment1->id,
        'leave_request_id' => $comment1->entity_id,
        'text' => $comment1->text,
        'contact_id' => $comment1->contact_id,
        'created_at' => $comment1Date->format('Y-m-d H:i:s')
      ],
      [
        'comment_id' => $comment2->id,
        'leave_request_id' => $comment2->entity_id,
        'text' => $comment2->text,
        'contact_id' => $comment2->contact_id,
        'created_at' => $comment2Date->format('Y-m-d H:i:s')
      ]
    ];

    $this->assertEquals($expected1, $result['values']);
  }

  /**
   * @expectedException UnexpectedValueException
   * @expectedExceptionMessage You must either be an L&A admin or an approver to this leave request to be able to delete the comment
   */
  public function testDeleteShouldThrowAnExceptionWhenLoggedInUserIsNotAnAdminOrLeaveApprover() {
    $contact = ContactFabricator::fabricate();
    $leaveContact = ContactFabricator::fabricate();

    // Register contact in session and make sure that no permission is set
    $this->registerCurrentLoggedInContactInSession($contact['id']);
    CRM_Core_Config::singleton()->userPermissionClass->permissions = [];

    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => $leaveContact['id'],
      'type_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'to_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'from_date_type' => 1,
      'to_date_type' => 1
    ]);

    $comment = CommentFabricator::fabricate([
      'entity_id' => $leaveRequest->id,
      'entity_name' => 'LeaveRequest',
      'contact_id' => $leaveRequest->contact_id,
    ]);

    $this->leaveRequestCommentService->delete(['comment_id' => $comment->id]);
  }

  public function testDeleteShouldDeleteCommentWhenLoggedInUserIsAnAdmin() {
    $contact = ContactFabricator::fabricate();
    $leaveContact = ContactFabricator::fabricate();

    // Register contact in session and set permission to admin
    $this->registerCurrentLoggedInContactInSession($contact['id']);
    CRM_Core_Config::singleton()->userPermissionClass->permissions = ['administer leave and absences'];

    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => $leaveContact['id'],
      'type_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'to_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'from_date_type' => 1,
      'to_date_type' => 1
    ]);

    $comment = CommentFabricator::fabricate([
      'entity_id' => $leaveRequest->id,
      'entity_name' => 'LeaveRequest',
      'contact_id' => $leaveRequest->contact_id,
    ]);

    $service = new CRM_HRLeaveAndAbsences_Service_LeaveRequestComment([
      'comment_id' => $comment->id
    ]);

    $result = $this->leaveRequestCommentService->delete(['comment_id' => $comment->id]);
    $expected = [
      'is_error' => 0,
      'version' => 3,
      'count' => 1,
      'values' => 1,
    ];

    $this->assertEquals($expected, $result);
  }

  public function testDeleteShouldDeleteCommentWhenLoggedInUserIsTheLeaveApprover() {
    $manager = ContactFabricator::fabricate();
    $leaveContact = ContactFabricator::fabricate();

    // Set logged in user as manager of Contact who requested leave
    $this->registerCurrentLoggedInContactInSession($manager['id']);
    $this->setContactAsLeaveApproverOf($manager, $leaveContact);
    CRM_Core_Config::singleton()->userPermissionClass->permissions = [];

    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => $leaveContact['id'],
      'type_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'to_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'from_date_type' => 1,
      'to_date_type' => 1
    ]);

    $comment = CommentFabricator::fabricate([
      'entity_id' => $leaveRequest->id,
      'entity_name' => 'LeaveRequest',
      'contact_id' => $leaveRequest->contact_id,
    ]);

    $result = $this->leaveRequestCommentService->delete(['comment_id' => $comment->id]);

    $expected = [
      'is_error' => 0,
      'version' => 3,
      'count' => 1,
      'values' => 1,
    ];

    $this->assertEquals($expected, $result);
  }

  public function testDeleteShouldThrowAnExceptionWhenCommentHasBeenDeletedBefore() {
    $contact = ContactFabricator::fabricate();
    $leaveContact = ContactFabricator::fabricate();

    // Register contact in session and set permission to admin
    $this->registerCurrentLoggedInContactInSession($contact['id']);
    CRM_Core_Config::singleton()->userPermissionClass->permissions = ['administer leave and absences'];

    $leaveRequest = LeaveRequestFabricator::fabricateWithoutValidation([
      'contact_id' => $leaveContact['id'],
      'type_id' => 1,
      'from_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'to_date' => CRM_Utils_Date::processDate('2016-01-01'),
      'from_date_type' => 1,
      'to_date_type' => 1
    ]);

    $comment = CommentFabricator::fabricate([
      'entity_id' => $leaveRequest->id,
      'entity_name' => 'LeaveRequest',
      'contact_id' => $leaveRequest->contact_id,
    ]);

    $this->leaveRequestCommentService->delete(['comment_id' => $comment->id]);
    //try delete comment again
    $this->setExpectedException('InvalidArgumentException', 'Comment does not exist or has been deleted already!');
    $this->leaveRequestCommentService->delete(['comment_id' => $comment->id]);
  }

  /**
   * @expectedException InvalidArgumentException
   * @expectedExceptionMessage Comment does not exist or has been deleted already!
   */
  public function testDeleteShouldThrowAnExceptionWhenCommentDoesNotExist() {
    $contact = ContactFabricator::fabricate();

    // Register contact in session and set permission to admin
    $this->registerCurrentLoggedInContactInSession($contact['id']);
    CRM_Core_Config::singleton()->userPermissionClass->permissions = ['administer leave and absences'];

    $this->leaveRequestCommentService->delete(['comment_id' => 12]);
  }
}
