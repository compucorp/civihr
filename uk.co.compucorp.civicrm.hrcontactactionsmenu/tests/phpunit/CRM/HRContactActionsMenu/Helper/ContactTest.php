<?php

use CRM_HRContactActionsMenu_Helper_Contact as ContactHelper;
use CRM_HRCore_Test_Fabricator_Contact as ContactFabricator;

/**
 * Class CRM_HRContactActionsMenu_Helper_ContactTest
 *
 * @group headless
 */
class CRM_HRContactActionsMenu_Helper_ContactTest extends BaseHeadlessTest {

  public function setUp() {
    $this->apiKernel = \Civi::service('civi_api_kernel');
    $this->adhocProvider = new \Civi\API\Provider\AdhocProvider(3, 'User');
    $this->apiKernel->registerApiProvider($this->adhocProvider);
  }

  public function testGetUserInformationAddsTheContactIDParameterWhenContactHasNoCMSUser() {
    $contactID = 3;

    //We need to mock this API call so that we won't have to deal with creating a CMS user for the contact
    //which might require bootstrapping the CMS.
    $this->adhocProvider->addAction('getsingle', 'access CiviCRM', function ($apiRequest) use($contactID) {
      return
        [
          'count' => 0
        ];
    });

    $result = ContactHelper::getUserInformation($contactID);
    $this->assertEquals($contactID, $result['contact_id']);
  }

  public function testGetUserInformationAddsTheCMSIDParameterWhenContactHasCMSUser() {
    $userID = 3;
    $contactID = 5;

    //We need to mock this API call so that we won't have to deal with creating a CMS user for the contact
    //which might require bootstrapping the CMS.
    $this->adhocProvider->addAction('getsingle', 'access CiviCRM', function ($apiRequest) use($userID, $contactID) {
      return
        [
          'id' => $userID,
          'contact_id' => $contactID,
          'count' => 1,
        ];
    });

    $result = ContactHelper::getUserInformation($contactID);
    $this->assertEquals($userID, $result['cmsId']);
  }

  public function testIsContactDeletedReturnsTrueWhenContactHasBeenSoftDeleted() {
    $contact = ContactFabricator::fabricate(['is_deleted' => 1]);
    $this->assertTrue(ContactHelper::isContactDeleted($contact['id']));
  }

  public function testIsContactDeletedReturnsFalseWhenContactIsNotSoftDeleted() {
    $contact = ContactFabricator::fabricate(['is_deleted' => 0]);
    $this->assertFalse(ContactHelper::isContactDeleted($contact['id']));
  }
}
