<?php

use CRM_Contactaccessrights_Helper_ContactActionsMenu_Contact as ContactHelper;

/**
 * Class CRM_Contactaccessrights_Helper_ContactTest
 *
 * @group headless
 */
class CRM_Contactaccessrights_Helper_ContactTest extends BaseHeadlessTest {
  public function setUp() {
    $this->apiKernel = \Civi::service('civi_api_kernel');
    $this->adhocProvider = new \Civi\API\Provider\AdhocProvider(3, 'GroupContact');
    $this->apiKernel->registerApiProvider($this->adhocProvider);
    CRM_Core_DAO::executeQuery('SET foreign_key_checks = 0;');
  }

  public function testGetACLGroupsWhenContactBelongsToAnACLGroup() {
    $contactID = 3;
    $group = $this->createACLGroup();

    //API call mock to assign Contact to a group
    $this->adhocProvider->addAction('get', 'access CiviCRM', function ($apiRequest) use($group, $contactID) {
      return $this->getGroupContactAPIReturnValue($group, $contactID);
    });


    $result = ContactHelper::getACLGroups($contactID);
    $this->assertEquals([$group['id'] => $group['title']], $result);
  }

  public function testGetACLGroupsWhenContactDoesNotBelongToAnyACLGroup() {
    $contactID = 3;
    $group = $this->createNonACLGroup();

    //API call mock to assign Contact to a group
    $this->adhocProvider->addAction('get', 'access CiviCRM', function ($apiRequest) use($group, $contactID) {
      return $this->getGroupContactAPIReturnValue($group, $contactID);
    });


    $result = ContactHelper::getACLGroups($contactID);
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
