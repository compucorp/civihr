<?php

class CRM_HRCore_Listener_Page_ContactSummary extends CRM_HRCore_Listener_Page_AbstractPage {

  protected $pageClass = 'CRM_Contact_Page_View_Summary';

  public function onAlterContent(&$content) {
    if (!$this->canHandle()) {
      return;
    }

    $this->updateContactSummaryUI($content);
  }

  public function onPageRun() {
    if (!$this->canHandle()) {
      return;
    }

    CRM_Core_Resources::singleton()->addSetting(array('pageName' => 'viewSummary'));

    //set government field value for individual page
    $contactType = CRM_Contact_BAO_Contact::getContactType(CRM_Utils_Request::retrieve('cid', 'Integer'));

    $isEnabled = $this->isExtensionEnabled('org.civicrm.hrident');

    if ($isEnabled && $contactType == 'Individual') {
      $hideGId = civicrm_api3('CustomField', 'getvalue', array('custom_group_id' => 'Identify', 'name' => 'is_government', 'return' => 'id'));
      CRM_Core_Resources::singleton()
        ->addSetting(array(
          'cid' => CRM_Utils_Request::retrieve('cid', 'Integer'),
          'hideGId' => $hideGId)
        );
    }
  }

  /**
   * Add new information in the contact header as the contact photo,
   * phone, department. All changes are made via Javascript.
   *
   * @return [String] Updated content markup
   */
  private function updateContactSummaryUI(&$content) {
    $departmentsList = $managersList = null;

    $contact_id = CRM_Utils_Request::retrieve( 'cid', 'Positive');

    /* $currentContractDetails contain current contact data including
     * Current ( Position = $currentContractDetails->position ) and
     * ( Normal Place of work =  $currentContractDetails->location )
    */
    $currentContractDetails = CRM_Hrjobcontract_BAO_HRJobContract::getCurrentContract($contact_id);

    // $departmentsList contain current roles departments list separated by comma
    if ($currentContractDetails)  {
      $departmentsArray = CRM_Hrjobroles_BAO_HrJobRoles::getCurrentDepartmentsList($currentContractDetails->contract_id);
      $departmentsList = implode(', ', $departmentsArray);
    }

    // $managersList contain current line managers list separated by comma
    if ($currentContractDetails)  {
      $managersArray = CRM_HRUI_Helper::getLineManagersList($contact_id);
      $managersList = implode(', ', $managersArray);
    }

    try {
      $contactDetails = civicrm_api3('Contact', 'getsingle', [
        'sequential' => 1,
        'return' => array("phone", "email", "image_URL"),
        'id' => $contact_id,
      ]);

      $content .= $this->contactSummaryDOMScript([
        'contact' => $contactDetails,
        'current_contract' => $currentContractDetails,
        'departments' => $departmentsList,
        'managers' => $managersList,
      ]);
    }
    catch (CiviCRM_API3_Exception $e) {
    }
  }

  /**
   * Builds the JS script that will alter the DOM of the contact summary DOM
   *
   * @param  Array $data contains details about the contact, the current contract, the departments and managers
   * @return string
   */
  private function contactSummaryDOMScript($data) {
    $script = '';

    $script .= "<script type=\"text/javascript\">";
    $script .= "CRM.$(function($) {";
    $script .= "$('#contactname-block.crm-summary-block').wrap('<div class=\"crm-summary-block-wrap\" />');";

    if (!empty($data['contact']['image_URL'])) {
      $script .= "$('.crm-summary-contactname-block').prepend('<img class=\"crm-summary-contactphoto\" src=" . $data['contact']['image_URL'] . " />');";
    }

    if (empty($data['current_contract'])) {
      $script .= "$('.crm-summary-contactname-block').addClass('crm-summary-contactname-block-without-contract');";
    }

    $script .= "$('.crm-summary-block-wrap').append(\"<div class='crm-contact-detail-wrap' />\");";
    $script .= "$('.crm-contact-detail-wrap').append(\"" . $this->contactSummaryHeaderHtml($data) . "\");";

    $script .= "});";
    $script .= "</script>";

    return $script;
  }

  /**
   * Builds the custom HTML markup for the contact header section
   *
   * @param  Array $data contains details about the contact, the current contract, the departments and managers
   * @return string
   */
  private function contactSummaryHeaderHtml($data) {
    $html = '';

    if (!empty($data['contact']['phone'])) {
      $html .= "<span class='crm-contact-detail'><strong>Phone:</strong> " . $data['contact']['phone'] . "</span>";
    }

    if (!empty($data['contact']['email'])) {
      $html .= "<span class='crm-contact-detail'><strong>Email:</strong> " . $data['contact']['email'] . "</span>";
    }

    $html .= "<br />";

    if (isset($data['current_contract'])) {
      $position = $location =  '';

      if (!empty($data['current_contract']->position)) {
        $position = "<strong>Position:</strong> " . $data['current_contract']->position;
      }

      if (!empty($data['current_contract']->location)) {
        $location .= "<strong>Normal place of work:</strong> " . $data['current_contract']->location;
      }

      $html .= "<span class='crm-contact-detail crm-contact-detail-position'>{$position}</span>";
      $html .= "<span class='crm-contact-detail crm-contact-detail-location'>{$location}</span>";

      if (!empty($data['departments'])) {
        $html .= "<span class='crm-contact-detail crm-contact-detail-departments'><strong>Department:</strong> " . $data['departments'] . "</span>";
      } else {
        $html .= "<span class='crm-contact-detail crm-contact-detail-departments'></span>";
      }

      if (!empty($data['managers'])) {
        $html .= "<span class='crm-contact-detail'><strong>Manager:</strong> " . $data['managers'] . "</span>";
      }
    }
    else {
      $html .= "<span class='crm-contact-detail crm-contact-detail-position'></span>";
      $html .= "<span class='crm-contact-detail crm-contact-detail-location'></span>";
      $html .= "<span class='crm-contact-detail crm-contact-detail-departments'></span>";
    }

    return $html;
  }

  private function isExtensionEnabled($key) {
    $isEnabled = CRM_Core_DAO::getFieldValue(
      'CRM_Core_DAO_Extension',
      $key,
      'is_active',
      'full_name'
    );

    return !empty($isEnabled) ? true : false;
  }
}
