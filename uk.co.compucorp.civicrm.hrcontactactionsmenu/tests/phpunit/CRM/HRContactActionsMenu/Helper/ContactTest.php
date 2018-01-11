<?php

use CRM_HRContactActionsMenu_Helper_Contact as ContactHelper;

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

  public function testGetUserInformationCallsThGetSingleMethodOfTheUserAPI() {
    $contactID = 3;

    //We need to mock this API call so that we won't have to deal with creating a CMS user for the contact
    //which might require bootstrapping the CMS.
    $this->adhocProvider->addAction('getsingle', 'access CiviCRM', function ($apiRequest) use($contactID) {
      return civicrm_api3_create_success(
        [
          'id' => 5,
          'contact_id' => $contactID,
          'count' => 1,
        ]
      );
    });

    $result = ContactHelper::getUserInformation($contactID);
    $this->assertEquals($contactID, $result['values']['contact_id']);
  }
}
