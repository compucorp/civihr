<?php

use CRM_HRCore_CMSData_CMSPathsFactory as CMSPathsFactory;

class CRM_HRCore_Page_UserMenu extends CRM_Core_Page {

  /**
   * The contact data used to build the menu
   *
   * @var array
   */
  private $contactData;

  /**
   * An instance of a class implementing the CRM_HRCore_CMSData_PathsInterface
   *
   * @var CRM_HRCore_CMSData_PathsInterface
   */
  private $cmsPaths;

  /**
   * {@inheritdoc}
   */
  public function run() {
    $this->getContactData();
    $this->instantiateCmsPaths();

    $this->assign('username', $this->contactData['display_name']);
    $this->assign('image', $this->getUserImagePath());
    $this->assign('editLink', $this->cmsPaths->getEditAccountPath());
    $this->assign('logoutLink', $this->cmsPaths->getLogoutPath());

    return parent::run();
  }

  /**
   * Gets the currently logged in contact's data, including the
   * her user id in the CMS
   *
   * @return array
   */
  private function getContactData() {
    $rawContactData = civicrm_api('Contact', 'getsingle', array(
      'version' => 3,
      'return' => array('id', 'display_name', 'image_URL'),
      'id' => CRM_Core_Session::getLoggedInContactID(),
      'api.User.getsingle' => array('contact_id' => "\$value.contact_id")
    ));

    $this->contactData = $this->normalizeContactDataAPIResponse($rawContactData);
  }

  /**
   * Returns the path of the user's image, falling back to the CMS's default
   * image if the user doesn't have one
   *
   * @return string
   */
  private function getUserImagePath() {
    $defaultPath = $this->cmsPaths->getDefaultImagePath();

    if (isset($this->contactData['image_URL']) && !empty($this->contactData['image_URL'])) {
      return $this->contactData['image_URL'];
    } else {
      return $defaultPath;
    }
  }

  /**
   * Instantiates the paths class of the current CMS
   */
  private function instantiateCmsPaths() {
    $cmsName = CRM_Core_Config::singleton()->userFramework;

    $this->cmsPaths = CMSPathsFactory::create($cmsName, $this->contactData);
  }

  /**
   * Normalizes the given contact data, removing any odd structure
   * related to the API response
   *
   * @param array $rawData
   *
   * @return array
   */
  private function normalizeContactDataAPIResponse($rawData) {
    $rawData['cmsId'] = $rawData['api.User.getsingle']['id'];
    unset($rawData['api.User.getsingle']);

    return $rawData;
  }
}
