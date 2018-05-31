<?php

use CRM_HRCore_Test_Fabricator_Contact as ContactFabricator;
use CRM_Hrjobcontract_Test_Fabricator_HRJobContract as HRJobContractFabricator;
use CRM_Hrjobroles_BAO_HrJobRoles as HrJobRoles;
use CRM_Hrjobroles_Test_Fabricator_HrJobRoles as HRJobRolesFabricator;

/**
 * Class api_v3_ContactHRJobRoleTest
 *
 * @group headless
 */
class api_v3_ContactHRJobRoleTest extends CRM_Hrjobroles_Test_BaseHeadlessTest {

  private $entity = 'ContactHrJobRoles';
  private $action = 'get';

  public function testAccessAjaxPermissionIsRequiredToAccessTheGetAction() {
    $contactID = 1;
    $this->registerCurrentLoggedInContactInSession($contactID);

    CRM_Core_Config::singleton()->userPermissionClass->permissions = [];

    $this->setExpectedApiPermissionException('access AJAX API');
    civicrm_api3($this->entity, $this->action, ['check_permissions' => true]);
  }

  public function testTheGetActionReturnsOnlyTheAllowedFields() {
    $departments = HrJobRoles::buildOptions('department', 'validate');
    $locations = HrJobRoles::buildOptions('location', 'validate');
    $levelTypes = HrJobRoles::buildOptions('level_type', 'validate');

    $contact = ContactFabricator::fabricate();
    $contract = HRJobContractFabricator::fabricate(['contact_id' => $contact['id']]);
    $jobRole = HRJobRolesFabricator::fabricate([
      'title' => 'Title',
      'description' => 'Description',
      'start_date' => date('Y-m-d'),
      'funder' => 'asddsa',
      'job_contract_id' => $contract['id'],
      'department' => $departments['IT'],
      'location' => $locations['Home'],
      'level_type' => $levelTypes['Senior Manager']
    ]);

    $contactJobRoles = civicrm_api3($this->entity, $this->action)['values'];

    // Even though the description, start_date and funder were set,
    // they won't be returned here, as they are not part of the allowed fields
    $expected = [
      $jobRole['id'] => [
        'id' => $jobRole['id'],
        'title' => $jobRole['title'],
        'department' => $jobRole['department'],
        'location' => $jobRole['location'],
        'level_type' => $jobRole['level_type'],
        'contact_id' => $contact['id'],
      ]
    ];
    $this->assertEquals($expected, $contactJobRoles);
  }

  public function testTheGetActionDoesntReturnsNotAllowedFieldsEvenWhenSpecified() {
    $contact = ContactFabricator::fabricate();
    $contract = HRJobContractFabricator::fabricate(['contact_id' => $contact['id']]);
    $jobRole = HRJobRolesFabricator::fabricate([
      'title' => 'Title',
      'description' => 'Description',
      'start_date' => date('Y-m-d'),
      'funder' => 'asddsa',
      'job_contract_id' => $contract['id'],
    ]);

    $contactJobRoles = civicrm_api3(
      $this->entity,
      $this->action,
      [
        'return' => [
          'description',
          'start_date',
          'funder',
          'cost_center'
        ]
      ]
    )['values'];

    // Since all the fields we asked are not allowed, the response will be empty,
    // except for the ID, which is always returned
    $expected = [
      $jobRole['id'] => [
        'id' => $jobRole['id'],
      ]
    ];
    $this->assertEquals($expected, $contactJobRoles);
  }

  public function testTheGetActionReturnsMultipleJobRoles() {
    $contact1 = ContactFabricator::fabricate();
    $contact2 = ContactFabricator::fabricate();
    $contract1 = HRJobContractFabricator::fabricate(['contact_id' => $contact1['id']]);
    $contract2 = HRJobContractFabricator::fabricate(['contact_id' => $contact2['id']]);
    $jobRole1 = HRJobRolesFabricator::fabricate(['job_contract_id' => $contract1['id'],]);
    $jobRole2 = HRJobRolesFabricator::fabricate(['job_contract_id' => $contract1['id'],]);
    $jobRole3 = HRJobRolesFabricator::fabricate(['job_contract_id' => $contract2['id'],]);

    $contactJobRoles = civicrm_api3($this->entity, $this->action)['values'];

    $this->assertCount(3, $contactJobRoles);

    // The CiviCRM only returns non-empty fields. Since we only set the job
    // contract id, only the ID and the contact_id fields will be returned
    $expectedJobRole1 = ['id' => $jobRole1['id'], 'contact_id' => $contact1['id']];
    $expectedJobRole2 = ['id' => $jobRole2['id'], 'contact_id' => $contact1['id']];
    $expectedJobRole3 = ['id' => $jobRole3['id'], 'contact_id' => $contact2['id']];

    $this->assertEquals($expectedJobRole1, $contactJobRoles[$jobRole1['id']]);
    $this->assertEquals($expectedJobRole2, $contactJobRoles[$jobRole2['id']]);
    $this->assertEquals($expectedJobRole3, $contactJobRoles[$jobRole3['id']]);
  }

  private function setExpectedApiPermissionException($permission) {
    $message = "API permission check failed for {$this->entity}/{$this->action} call; insufficient permission: require {$permission}";
    $this->setExpectedException('CiviCRM_API3_Exception', $message);
  }

  private function registerCurrentLoggedInContactInSession($contactID) {
    $session = CRM_Core_Session::singleton();
    $session->set('userID', $contactID);
  }
}
