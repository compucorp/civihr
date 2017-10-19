<?php

use CRM_HRCore_CMSData_PathsFactory as PathsFactory;

class CRM_HRCore_Page_UserMenu extends CRM_Core_Page {

  private $contactData;
  private $cmsPaths;

  public function run() {
    $this->getContactData();
    $this->instantiateCmsPaths();

    $this->assign('username', $this->contactData['display_name']);
    $this->assign('image', $this->getUserImagePath());
    $this->assign('editLink', $this->cmsPaths->getEditAccountPath());
    $this->assign('logoutLink', $this->cmsPaths->getLogoutPath());

    return parent::run();
  }

  private function getContactData() {
    $rawContactData = civicrm_api('Contact', 'getsingle', array(
      'version' => 3,
      'return' => array('id', 'display_name', 'image_URL'),
      'id' => CRM_Core_Session::getLoggedInContactID(),
      'api.User.getsingle' => array('contact_id' => "\$value.contact_id")
    ));

    $this->contactData = $this->normalizeContactData($rawContactData);
  }

  private function getUserImagePath() {
    $defaultPath = $this->cmsPaths->getDefaultImagePath();

    if (isset($this->contactData['image_URL']) && !empty($this->contactData['image_URL'])) {
      return $this->contactData['image_URL'];
    } else {
      return $defaultPath;
    }
  }

  private function instantiateCmsPaths() {
    $cmsName = CRM_Core_Config::singleton()->userFramework;

    $this->cmsPaths = PathsFactory::create($cmsName, $this->contactData);
  }

  private function normalizeContactData($rawData) {
    $rawData['cmsId'] = $rawData['api.User.getsingle']['id'];
    unset($rawData['api.User.getsingle']);

    return $rawData;
  }
}