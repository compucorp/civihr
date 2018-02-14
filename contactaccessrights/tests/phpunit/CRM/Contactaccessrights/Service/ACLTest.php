<?php

use CRM_Contactaccessrights_Service_ACL as ACLService;

/**
 * Class CRM_Contactaccessrights_Service_ACLTest
 *
 * @group headless
 */
class CRM_Contactaccessrights_Service_ACLTest extends BaseHeadlessTest {

  private $aclService;

  public function setUp() {
    $this->apiKernel = \Civi::service('civi_api_kernel');
    $this->adhocProvider = new \Civi\API\Provider\AdhocProvider(3, 'GroupContact');
    $this->apiKernel->registerApiProvider($this->adhocProvider);
    $this->aclService = new ACLService();
  }

  public function testGetACLGroupsWhenContactBelongsToAnACLGroup() {
    $contactID = 3;
    $group = $this->createACLGroup();

    //API call mock to assign Contact to a group
    $this->adhocProvider->addAction('get', 'access CiviCRM', function ($apiRequest) use($group, $contactID) {
      return $this->getGroupContactAPIReturnValue($group, $contactID);
    });


    $result = $this->aclService->getACLGroupsForContact($contactID);
    $this->assertEquals([$group['id'] => $group['title']], $result);
  }

  public function testGetACLGroupsWhenContactDoesNotBelongToAnyACLGroup() {
    $contactID = 3;
    $group = $this->createNonACLGroup();

    //API call mock to assign Contact to a group
    $this->adhocProvider->addAction('get', 'access CiviCRM', function ($apiRequest) use($group, $contactID) {
      return $this->getGroupContactAPIReturnValue($group, $contactID);
    });


    $result = $this->aclService->getACLGroupsForContact($contactID);
    $this->assertEquals([], $result);
  }

  private function createACLGroup() {
    return $this->createGroup();
  }

  private function createNonACLGroup() {
    return $this->createGroup(FALSE);
  }

  private function createGroup($isACLGroup = TRUE) {
    $params = [
      'sequential' => 1,
      'title' => 'Test',
      'group_type' => 'Access Control'
    ];

    if (!$isACLGroup) {
      $params['group_type'] = 'Mailing List';
    }
    $result = civicrm_api3('Group', 'create', $params);

    return $result['values'][0];
  }

  private function getGroupContactAPIReturnValue($group, $contactID) {
    return [
      'is_error' => 0,
      'count' => 1,
      'id' => 1,
      'values' => [
        0 => [
          'id' => 1,
          'group_id' => $group['id'],
          'contact_id' => $contactID,
        ]
      ]
    ];
  }
}
