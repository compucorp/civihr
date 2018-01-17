<?php

use CRM_HRContactActionsMenu_Component_UserInformationLinkItem as UserInformationLinkItem;
use CRM_HRCore_CMSData_Paths_PathsInterface as CMSUserPath;

/**
 * Class CRM_HRContactActionsMenu_Component_UserInformationLinkItemTest
 *
 * @group headless
 */
class CRM_HRContactActionsMenu_Component_UserInformationLinkItemTest extends BaseHeadlessTest {

  public function testRender() {
    $contactData = ['cmsId' => 1, 'name' => 'Test User'];
    $cmsUserPath = $this->prophesize(CMSUserPath::class);
    $cmsUserPath->getEditAccountPath()->willReturn(sprintf('/user/%s/edit', $contactData['cmsId']));
    $cmsUserPath = $cmsUserPath->reveal();
    $userInformationItem = new UserInformationLinkItem($cmsUserPath, $contactData);

    $userInformationMarkup = '
      <p><span class="crm_contact_action_menu__bold_text">User: </span> 
        <a href="%s" class="text-primary">%s</a>
      </p>';

    $expectedResult = sprintf(
      $userInformationMarkup,
      $cmsUserPath->getEditAccountPath(),
      $contactData['cmsId'] . ' ' . $contactData['name']
    );

    $this->assertEquals($expectedResult, $userInformationItem->render());
  }
}
