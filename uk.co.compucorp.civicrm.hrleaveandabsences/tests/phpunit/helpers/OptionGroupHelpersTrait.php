<?php

/**
 * This is a helper that loads Option Values from XML files instead of the
 * database.
 *
 * Why do we need this? Before running any actual code, PHPUnit reads all the
 * test files looking for test methods and data providers available. For data
 * providers, it also calls the method in order to cache the provided data. At
 * the moment this happens, Civi hasn't been initialized yet, and Option Groups
 * created during the extension's installation won't be available in the
 * database, effectively meaning that data providers cannot use such Option
 * Groups. To work around this problem, the helper methods in this trait can
 * be used to load the option values directly from the XML files used during
 * the installation.
 */
trait CRM_HRLeaveAndAbsences_OptionGroupHelpersTrait {

  public function getLeaveRequestStatusesFromXML() {
    return $this->getOptionValuesFromXML('leave_request_status_install.xml');
  }

  public function getLeaveDayTypesFromXML() {
    return $this->getOptionValuesFromXML('leave_request_day_type_install.xml');
  }

  public function getWorkDayTypesFromXML() {
    return $this->getOptionValuesFromXML('work_day_type_install.xml');
  }

  /**
   * This methods parses the given Option Group XML and returns a list of
   * Option Values contained in it.
   *
   * Important note: It assumes that the XML represents a single Option Group,
   * and that all the Option Values in belong to the same Group.
   *
   * @param $xml
   *   The name of a file inside the xml/option_groups folder
   *
   * @return array
   *   An array following the same OptionValues structure of the given XML
   */
  public function getOptionValuesFromXML($xml) {
    $mapper = CRM_Extension_System::singleton()->getManager()->mapper;
    $extPath = $mapper->keyToBasePath('uk.co.compucorp.civicrm.hrleaveandabsences');
    $xmlFullPath = "{$extPath}/xml/option_groups/{$xml}";

    $optionValues = [];

    $xml = new DOMDocument();
    $xml->load($xmlFullPath);
    $xmlOptionValues = $xml->getElementsByTagName('OptionValue');
    foreach($xmlOptionValues as $xmlOptionValue) {
      $optionValue = [];

      foreach($xmlOptionValue->childNodes as $node) {
        if($node->nodeType == XML_ELEMENT_NODE) {
          $optionValue[$node->nodeName] = $node->nodeValue;
        }
      }

      if(!empty($optionValue)) {
        $optionValues[] = $optionValue;
      }
    }

    return $optionValues;
  }

}
