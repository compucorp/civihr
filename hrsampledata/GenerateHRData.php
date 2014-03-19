<?php
/*
 +--------------------------------------------------------------------+
 | CiviHR version 1.2                                                 |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2013                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*/

/**
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2013
 * $Id$
 *
 */

/**
 * This class generates sample data for the CiviHR extension from sample_data.xml file
 */

// autoload
require_once 'CRM/Core/ClassLoader.php';
CRM_Core_ClassLoader::singleton()->register();
class GenerateHRData {

  /**
   * Constants
   */

  // Set ADD_TO_DB = FALSE to do a dry run
  CONST ADD_TO_DB = TRUE;

  CONST NUM_CONTACT = 20;
  CONST INDIVIDUAL_PERCENT = 90;
  CONST ORGANIZATION_PERCENT = 10;

  // Location types from the table crm_location_type
  CONST HOME = 1;
  CONST WORK = 2;
  CONST MAIN = 3;
  CONST OTHER = 4;

  /**
   * Class constructor
   */
  function __construct() {
    // initialize all the vars
    $this->numIndividual = self::INDIVIDUAL_PERCENT * self::NUM_CONTACT / 100;
    $this->numOrganization = self::ORGANIZATION_PERCENT * self::NUM_CONTACT / 100;

    // Parse data file
    foreach ((array) simplexml_load_file(dirname(__FILE__) . '/xml/sample_data.xml') as $key => $val) {
      $val = (array) $val;
      $this->sampleData[$key] = (array) $val['item'];
    }
    // Init DB
    $config = CRM_Core_Config::singleton();
  }

  /**
   * Public wrapper for calling private "add" functions
   * Provides user feedback
   */
  public function generate($itemName) {
    echo "Adding $itemName\n";
    $fn = "add$itemName";
    $this->$fn();
  }

  /**
   * this function creates arrays for the following
   *
   * domain id
   * contact id
   * contact_location id
   * contact_contact_location id
   * contact_email uuid
   * contact_phone_uuid
   * contact_instant_message uuid
   * contact_relationship uuid
   * contact_task uuid
   * contact_note uuid
   */
  public function initID() {

    // may use this function in future if needed to get
    // a consistent pattern of random numbers.
    $this->addContact();
    $this->contact = $this->cids;
    shuffle($this->contact);

    // get the individual and organizaton contacts
    $offset = 0;
    $this->Individual = array_slice($this->contact, $offset, $this->numIndividual);
    $offset += $this->numIndividual;
    $this->Organization = array_slice($this->contact, $offset, $this->numOrganization);
  }

  /*********************************
   * private members
   *********************************/

  // enum's from database
  private $preferredCommunicationMethod = array('1', '2', '3', '4', '5');
  private $contactType = array('Individual', 'Organization');

  // customizable enums (foreign keys)
  private $prefix = array(
    // Female
    1 => array(
      1 => 'Mrs.',
      2 => 'Ms.',
      4 => 'Dr.'
    ),
    // Male
    2 => array(
      3 => 'Mr.',
      4 => 'Dr.',
    )
  );
  private $suffix = array(1 => 'Jr.', 2 => 'Sr.', 3 => 'II', 4 => 'III');
  private $gender = array(1 => 'female', 2 => 'male');

  // store contact id's
  private $contact = array();
  private $Individual = array();
  private $Organization = array();

  // store which contacts have a location entity
  // for automatic management of is_primary field
  private $location = array(
    'Email' => array(),
    'Phone' => array(),
    'Address' => array(),
  );

  // sample data in xml format
  private $sampleData = array();

  // private vars
  private $numIndividual = 0;
  private $numOrganization = 0;
  private $states = array();

  /*********************************
   * private methods
   *********************************/

  private function randomChar() {
    return chr(mt_rand(65, 90));
  }

  /**
   * Get a random item from the sample data or any other array
   *
   * @param $items (array or string) - if string, used as key for sample data, if array, used as data source
   *
   * @return mixed (element from array)
   *
   * @private
   */
  private function randomItem($items) {
    if (!is_array($items)) {
      $key = $items;
      $items = $this->sampleData[$key];
    }
    if (!$items) {
      echo "Error: no items found for '$key'\n";
      return;
    }
    return $items[mt_rand(0, count($items) - 1)];
  }

  private function randomIndex($items) {
    return $this->randomItem(array_keys($items));
  }

  private function randomKeyValue($items) {
    $key = $this->randomIndex($items);
    return array($key, $items[$key]);
  }

  private function probability($chance) {
    if (mt_rand(0, 100) < ($chance * 100)) {
      return 1;
    }
    return 0;
  }

  /**
   * Generate a random date.
   *
   *   If both $startDate and $endDate are defined generate
   *   date between them.
   *
   *   If only startDate is specified then date generated is
   *   between startDate + 1 year.
   *
   *   if only endDate is specified then date generated is
   *   between endDate - 1 year.
   *
   *   if none are specified - date is between today - 1year
   *   and today
   *
   * @param  string $startDate   Start Date value, default in 'Ymd' format
   * @param  string $endDate     End Date value, default in 'Ymd' format
   * #param  string $dateFormat  Date format string default in Ymd format
   * @access private
   *
   * @return string randomly generated date in the format "Ymd"
   *
   */
  private function randomDate($startDate = null, $endDate = null, $dateFormat = "Ymd") {

    // number of seconds per year
    $numSecond = 31536000;
    $today = time();

    // both are defined
    if ($startDate && $endDate) {
      return date($dateFormat, rand(strtotime($startDate), strtotime($endDate)));
    }

    // only startDate is defined
    if ($startDate) {
      return date($dateFormat, rand(strtotime($startDate), strtotime($startDate)+$numSecond));
    }

    // only endDate is defined
    if ($startDate) {
      return date($dateFormat, rand(strtotime($endDate)-$numSecond, strtotime($endDate)));
    }

    // none are defined
    return date($dateFormat, rand($today - $numSecond, $today));
  }

  /**
   * Automatically manage the is_primary field by tracking which contacts have each item
   */
  private function isPrimary($cid, $type) {
    if (empty($this->location[$type][$cid])) {
      $this->location[$type][$cid] = TRUE;
      return 1;
    }
    return 0;
  }

  /**
   * Call dao insert method unless we are doing a dry run
   */
  private function _insert(&$dao) {
    if (self::ADD_TO_DB) {
      if (!$dao->insert()) {
        echo "ERROR INSERT: " . mysql_error() . "\n";
        print_r($dao);
        exit(1);
      }
    }
  }

  /**
   * Call dao update method unless we are doing a dry run
   */
  private function _update(&$dao) {
    if (self::ADD_TO_DB) {
      if (!$dao->update()) {
        echo "ERROR UPDATE: " . mysql_error() . "\n";
        print_r($dao);
        exit(1);
      }
    }
  }

  /**
   * Add core DAO object
   */
  private function _addDAO($type, $params) {
    $daoName = "CRM_Core_DAO_$type";
    $obj = new $daoName();
    foreach ($params as $key => $value) {
      $obj->$key = $value;
    }
    if (isset($this->location[$type])) {
      $obj->is_primary = $this->isPrimary($params['contact_id'], $type);
    }
    $this->_insert($obj);
  }

  /**
   * Fetch contact type based on stored mapping
   */
  private function getContactType($id) {
    foreach (array('Individual', 'Organization') as $type) {
      if (in_array($id, $this->$type)) {
        return $type;
      }
    }
  }

  /**
   * This method adds data to the contact table
   *
   * id - from $contact
   * contact_type 'Individual' 'Organization'
   * preferred_communication (random 1 to 3)
   */
  private function addContact() {
    $contact = new CRM_Contact_DAO_Contact();

    for ($id = 1; $id <= self::NUM_CONTACT; $id++) {
      $contact->do_not_phone = $this->probability(.2);
      $contact->do_not_email = $this->probability(.2);
      $contact->do_not_post = $this->probability(.2);
      $contact->do_not_trade = $this->probability(.2);
      $contact->preferred_communication_method = NULL;
      if ($this->probability(.5)) {
        $contact->preferred_communication_method = CRM_Core_DAO::VALUE_SEPARATOR . $this->randomItem($this->preferredCommunicationMethod) . CRM_Core_DAO::VALUE_SEPARATOR;
      }
      $this->_insert($contact);
      $cids[] = $contact->id;
    }
    $this->cids = $cids;
  }

  /**
   * addIndividual()
   *
   * This method adds individual's data to the contact table
   *
   * The following fields are generated and added.
   *
   * contact_uuid - individual
   * contact_rid - latest one
   * first_name 'First Name $contact_uuid'
   * middle_name 'Middle Name $contact_uuid'
   * last_name 'Last Name $contact_uuid'
   * job_title 'Job Title $contact_uuid'
   *
   */
  private function addIndividual() {

    $contact = new CRM_Contact_DAO_Contact();
    $year = 60 * 60 * 24 * 365.25;
    $now = time();

    foreach ($this->Individual as $cid) {
      $contact->contact_type = $this->getContactType($cid);
      $contact->is_deceased = $contact->gender_id = $contact->birth_date = $contact->deceased_date = $email = NULL;
      list($gender_id, $gender) = $this->randomKeyValue($this->gender);
      $birth_date = mt_rand($now - 90 * $year, $now - 10 * $year);

      $contact->last_name = $this->randomItem('last_name');
      $this->_addAddress($cid);
      $contact->first_name = $this->randomItem($gender . '_name');
      $contact->middle_name = $this->probability(.5) ? '' : ucfirst($this->randomChar());
      $age = intval(($now - $birth_date) / $year);

      // Prefix and suffix by gender and age
      $contact->prefix_id = $contact->suffix_id = $prefix = $suffix = NULL;
      if ($this->probability(.5) && $age > 20) {
        list($contact->prefix_id, $prefix) = $this->randomKeyValue($this->prefix[$gender_id]);
        $prefix .= ' ';
      }
      if ($gender == 'male' && $this->probability(.50)) {
        list($contact->suffix_id, $suffix) = $this->randomKeyValue($this->suffix);
        $suffix = ' ' . $suffix;
      }
      if ($this->probability(.7)) {
        $contact->gender_id = $gender_id;
      }
      if ($this->probability(.7)) {
        $contact->birth_date = date("Ymd", $birth_date);
      }

      // Deceased probability based on age
      if ($age > 40) {
        $contact->is_deceased = $this->probability(($age - 30) / 100);
        if ($contact->is_deceased && $this->probability(.7)) {
          $contact->deceased_date = $this->randomDate();
        }
      }

      // Add 0, 1 or 2 email address
      $count = mt_rand(0, 2);
      for ($i = 0; $i < $count; ++$i) {
        $email = $this->_individualEmail($contact);
        $this->_addEmail($cid, $email, self::WORK);
      }

      // Add 0, 1 or 2 phones
      $count = mt_rand(0, 2);
      for ($i = 0; $i < $count; ++$i) {
        $this->_addPhone($cid);
      }

      // Occasionally you get contacts with just an email in the db
      if ($this->probability(.2) && $email) {
        $contact->first_name = $contact->last_name = $contact->middle_name = NULL;
        $contact->is_deceased = $contact->gender_id = $contact->birth_date = $contact->deceased_date = NULL;
        $contact->display_name = $contact->sort_name = $email;
        $contact->postal_greeting_display = $contact->email_greeting_display = "Dear $email";
      }
      else {
        $contact->display_name = $prefix . $contact->first_name . ' ' . $contact->last_name . $suffix;
        $contact->sort_name = $contact->last_name . ', ' . $contact->first_name;
        $contact->postal_greeting_display = $contact->email_greeting_display = 'Dear ' . $contact->first_name;
      }
      $contact->addressee_id = $contact->postal_greeting_id = $contact->email_greeting_id = 1;
      $contact->addressee_display = $contact->display_name;
      $contact->hash = crc32($contact->sort_name);
      $contact->id = $cid;
      $this->_update($contact);

      //if Job(CiviHR) extension is enabled, add the sample data
      $this->addJobData($cid);
      //if Identification (CiviHR) extension is enabled, add the sample data
      $this->addIdentificationData($cid);
      //if Medical and Disability (CiviHR) extension is enabled, add the sample data
      $this->addMedicalData($cid);
      //if Qualifications (CiviHR) extension is enabled, add the sample data
      $this->addQualifications($cid);
      //if Immigration / Visas (CiviHR) extension is enabled, add the sample data
      $this->addVisaDetails($cid);
      //if Emergency Contacts (CiviHR) extension is enabled, add the sample data
      $this->addEmergencyContact($cid);
      //if Career (CiviHR) extension is enabled, add the sample data
      $this->addCareerData($cid);
      //if Absence (CiviHR) extension in enabled, add the sample data
      $this->addAbsenceEntitlements($cid);
      //if Recruitment (CiviHR) extension in enabled, add the sample data
      $this->addVacancies($cid);
    }
    $this->addHolidays($cid);
  }


  /**
   * This method adds organization data to the contact table
   *
   * The following fields are generated and added.
   *
   * contact_uuid - organization
   * contact_rid - latest one
   * organization_name 'organization $contact_uuid'
   * legal_name 'legal  $contact_uuid'
   * nick_name 'nick $contact_uuid'
   * sic_code 'sic $contact_uuid'
   * primary_contact_id - random individual contact uuid
   *
   */
  private function addOrganization() {

    $org = new CRM_Contact_DAO_Contact();
    $employees = $this->Individual;
    shuffle($employees);

    foreach ($this->Organization as $key => $id) {
      $org->primary_contact_id = $website = $email = NULL;
      $org->id = $id;
      $org->contact_type = $this->getContactType($id);
      $address = $this->_addAddress($id, self::MAIN);

      $namePre = $this->randomItem('organization_prefix');
      $nameMid = $this->randomItem('organization_name');
      $namePost = $this->randomItem('organization_suffix');

      // Some orgs are named after their location
      if ($this->probability(.7)) {
        $place = $this->randomItem(array('city', 'street_name', 'state_province'));
        $namePre = $address[$place];
      }
      $org->organization_name = "$namePre $nameMid $namePost";

      // Most orgs have a website and email
      if ($this->probability(.8)) {
        $website = $this->_addWebsite($id, $org->organization_name);
        $url = str_replace('http://', '', $website['url']);
        $email = $this->randomItem('email_address') . '@' . $url;
        $this->_addEmail($id, $email, self::MAIN);
      }

      // current employee
      if ($this->probability(.8)) {
        $indiv = new CRM_Contact_DAO_Contact();
        $org->primary_contact_id = $indiv->id = $employees[$key];
        $indiv->organization_name = $org->organization_name;
        $indiv->employer_id = $id;
        $this->_update($indiv);
        // Share address with employee
        if ($this->probability(.8)) {
          $this->_addAddress($indiv->id, $id);
        }
        // Add work email for employee
        if ($website) {
          $indiv->find(TRUE);
          $email = $this->_individualEmail($indiv, $url);
          $this->_addEmail($indiv->id, $email, self::WORK);
        }
      }

      // need to update the sort name for the main contact table
      $org->display_name = $org->sort_name = $org->organization_name;
      $org->addressee_id = 3;
      $org->addressee_display = $org->display_name;
      $org->hash = crc32($org->sort_name);
      $this->_update($org);
    }

    //if Absence (CiviHR) extension is enabled, add the sample data
    $this->addAbsencePeriods();
  }


  /**
   * Create an address for a contact
   *
   * @param $cid int: contact id
   * @param $masterContactId int: set if this is a shared address
   */
  private function _addAddress($cid, $locationType = self::WORK) {
    $params = array(
      'contact_id' => $cid,
      'location_type_id' => $locationType,
      'street_number' => mt_rand(1, 1000),
      'street_number_suffix' => ucfirst($this->randomChar()),
      'street_name' => $this->randomItem('street_name'),
      'street_type' => $this->randomItem('street_type'),
      'street_number_postdirectional' => $this->randomItem('address_direction'),
      'city' => $this->randomItem('city'),
      'state_province_id' => $this->randomItem('state_province'),
      'country_id' => $this->randomItem('country'),
      'postal_code' => $this->randomItem('postal_code')
    );

    $params['street_address'] = $params['street_number'] . $params['street_number_suffix'] . " " . $params['street_name'] . " " . $params['street_type'] . " " . $params['street_number_postdirectional'];


    if ($params['location_type_id'] == self::WORK) {
      $params['supplemental_address_1'] = $this->randomItem('supplemental_addresses_1');
    }


    $this->_addDAO('Address', $params);
    return $params;
  }

  /**
   * Add a phone number for a contact
   *
   * @param $cid int: contact id
   */
  private function _addPhone($cid) {
    $area = $this->probability(.5) ? '' : mt_rand(201, 899);
    $pre = mt_rand(201, 899);
    $post = mt_rand(1000, 9999);
    $params = array(
      'location_type_id' => $this->getContactType($cid) == 'Organization' ? self::MAIN : self::WORK,
      'contact_id' => $cid,
      'phone' => ($area ? "($area) " : '') . "$pre-$post",
      'phone_numeric' => $area . $pre . $post,
      'phone_type_id' => mt_rand(1, 2),
    );
    $this->_addDAO('Phone', $params);
    return $params;
  }

  /**
   * Add an email for a contact
   *
   * @param $cid int: contact id
   */
  private function _addEmail($cid, $email, $locationType) {
    $params = array(
      'location_type_id' => $locationType,
      'contact_id' => $cid,
      'email' => $email,
    );
    $this->_addDAO('Email', $params);
    return $params;
  }

  /**
   * Add a website based on organization name
   * Using common naming patterns
   *
   * @param $cid int: contact id
   * @param $name str: contact name
   */
  private function _addWebsite($cid, $name) {
    $part = array_pad(explode(' ', strtolower($name)), 3, '');
    if (count($part) > 3) {
      // Abbreviate the place name if it's two words
      $domain = $part[0][0] . $part[1][0] . $part[2] . $part[3];
    }
    else {
      // Common naming patterns
      switch (mt_rand(1, 3)) {
        case 1:
          $domain = $part[0] . $part[1] . $part[2];
          break;
        case 2:
          $domain = $part[0] . $part[1];
          break;
        case 3:
          $domain = $part[0] . $part[2];
          break;
      }
    }
    $params = array(
      'website_type_id' => 1,
      'location_type_id' => self::MAIN,
      'contact_id' => $cid,
      'url' => "http://$domain.org",
    );
    $this->_addDAO('Website', $params);
    return $params;
  }

  /**
   * Create an email address based on a person's name
   * Using common naming patterns
   * @param $contact obj: individual contact record
   * @param $domain str: supply a domain (i.e. for a work address)
   */
  private function _individualEmail($contact, $domain = NULL) {
    $first = $contact->first_name;
    $last = $contact->last_name;
    $f = $first[0];
    $l = $last[0];
    $m = $contact->middle_name ? $contact->middle_name[0] . '.' : '';
    // Common naming patterns
    switch (mt_rand(1, 6)) {
      case 1:
        $email = $first . $last;
        break;
      case 2:
        $email = "$last.$first";
        break;
      case 3:
        $email = $last . $f;
        break;
      case 4:
        $email = $first . $l;
        break;
      case 5:
        $email = "$last.$m$first";
        break;
      case 6:
        $email = "$f$m$last";
        break;
    }
    //to ensure we dont insert
    //invalid characters in email
    $email = preg_replace("([^a-zA-Z0-9_\.-]*)", "", $email);

    // Some people have numbers in their address
    if ($this->probability(.4)) {
      $email .= mt_rand(1, 99);
    }
    // Generate random domain if not specified
    if (!$domain) {
      $domain = $this->randomItem('email_domain') . '.' . $this->randomItem('email_tld');
    }
    return strtolower($email) . '@' . $domain;
  }

  /**
   * This method populates all the HRJob Entity tables
   */
  private function addJobData($cid) {
    if (!CRM_Core_DAO::getFieldValue('CRM_Core_DAO_Extension', 'org.civicrm.hrjob', 'is_active', 'full_name')) {
      return;
    }

    for ($i = 1; $i <= mt_rand(1, 3); $i++) {
      //sample data for HRJob table
      $jobValues = array(
        'contact_id' => $cid,
        'position' => $this->randomItem('position'),
        'title' => $this->randomItem('title'),
        'is_tied_to_funding' => $this->randomItem('is_tied_to_funding'),
        'contract_type' => $this->randomItem('contract_type'),
        'level_type' => $this->randomItem('level_type'),
        'period_type' => $this->randomItem('period_type'),
        'period_start_date' => $this->randomDate('20090101', '20121231'),
        'period_end_date' => $this->randomDate('20130101', '20151231'),
        'notice_amount' => $this->randomItem('notice_amount'),
        'notice_unit' => $this->randomItem('notice_unit'),
        'location' => $this->randomItem('location'),
        'department' => $this->randomItem('department'),
        'manager_contact_id' => $this->randomIndex(array_flip($this->contact)),
        'funding_org_id' => $this->randomIndex(array_flip($this->Organization)),
        'is_primary' => 0,
      );
      if ($i == 1) {
        $jobValues['is_primary'] = 1;
      }
      $hrJob[] = $this->insertJobData('CRM_HRJob_DAO_HRJob', $jobValues);
    }

    //For each HRJob, there may be 0 or 1 records for each of these entity types: HRJobHealth, HRJobHour, HRJobPay, HRJobPension.
    foreach ($hrJob as $key => $hrJobObj) {
      for ($i = 1; $i <= mt_rand(0, 1); $i++) {
        //sample data for HRJob Health table
        $healthValues = array(
          'job_id' => $hrJobObj->id,
          'provider' => NULL,
          'plan_type' => $this->randomItem('plan_type'),
          'description' => $this->randomItem('description'),
          'dependents' => $this->randomItem('dependents'),
          'provider_life_insurance' => NULL,
          'plan_type_life_insurance' => $this->randomItem('plan_type_life_insurance'),
          'description_life_insurance' => $this->randomItem('description_life_insurance'),
          'dependents_life_insurance' => $this->randomItem('dependents_life_insurance'),
        );
        $this->insertJobData('CRM_HRJob_DAO_HRJobHealth', $healthValues);
      }

      for ($i = 1; $i <= mt_rand(0, 1); $i++) {
        //sample data for HRJob Hour table
        $hoursValues = array(
          'job_id' => $hrJobObj->id,
          'hours_type' => $this->randomItem('hours_type'),
          'hours_amount' => $this->randomItem('hours_amount'),
          'hours_unit' => $this->randomItem('hours_unit'),
          'hours_fte' => $this->randomItem('hours_fte'),
        );
        $this->insertJobData('CRM_HRJob_DAO_HRJobHour', $hoursValues);
      }

      for ($i = 1; $i <= mt_rand(0, 1); $i++) {
        //sample data for HRJob Pay table
        $payValues = array(
          'job_id' => $hrJobObj->id,
          'pay_grade' => $this->randomItem('paid_unpaid'),
          'pay_amount' => $this->randomItem('pay_amount'),
          'pay_unit' => $this->randomItem('pay_unit'),
        );
        $this->insertJobData('CRM_HRJob_DAO_HRJobPay', $payValues);
      }

      for ($i = 1; $i <= mt_rand(0, 1); $i++) {
        //sample data for HRJob Pension table
        $pensionValues = array(
          'job_id' => $hrJobObj->id,
          'is_enrolled' => $this->randomItem('is_enrolled'),
          'ee_contrib_pct' => $this->randomItem('contrib_pct'),
          'er_contrib_pct' => $this->randomItem('contrib_pct'),
        );
        $this->insertJobData('CRM_HRJob_DAO_HRJobPension', $pensionValues);
      }

      //sample data for HRJob Leave table. For each HRJob, there would be one HRJobLeave for each leave_type.
      $leaveTypes = array('1', '2', '3');
      foreach ($leaveTypes as $key => $value) {
        $leaveValues = array(
          'job_id' => $hrJobObj->id,
          'leave_type' => $value,
          'leave_amount' => $this->randomItem('leave_amount'),
        );
        $this->insertJobData('CRM_HRJob_DAO_HRJobLeave', $leaveValues);
      }


      //sample data for HRJob Role table. cases with 0, 1, 2, and 3 job roles.
      $count = mt_rand(0, 3);
      for ($i = 1; $i <= $count; $i++) {
        $roleValues = array(
          'job_id' => $hrJobObj->id,
          'title' => $this->randomItem('title'),
          'description' => $this->randomItem('job_role_description'),
          'hours' => $this->randomItem('hours_amount'),
          'region' => $this->randomItem('region'),
          'department' => $this->randomItem('department'),
          'manager_contact_id' => $this->randomIndex(array_flip($this->contact)),
          'functional_area' => $this->randomItem('functional_area'),
          'organization' => $this->randomItem('name_of_organisation'),
          'cost_center' => $this->randomItem('cost_center'),
        );
        $this->insertJobData('CRM_HRJob_DAO_HRJobRole', $roleValues);
      }
    }
  }

  /**
   * This is a common method called to insert the data into HRJob tables
   */
  function insertJobData($className, $values) {
    $dao = new $className();
    foreach ($values as $columnName => $columnValue) {
      $dao->$columnName = $columnValue;
    }
    $dao->save();
    return $dao;
  }

  /**
   * This method populates the Identification Custom Table
   */
  private function addIdentificationData($cid) {
    if (!$gid = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_CustomGroup', 'Identify', 'id', 'name')) {
      return;
    }

    $values = array(
      'entity_id' => $cid,
      'type' => $this->randomItem('type'),
      'number' => $this->randomItem('number'),
      'issue_date' => $this->randomDate('20090101', '20111231'),
      'expire_date' => $this->randomDate('20120101', '20141231'),
      'country' => $this->randomItem('country'),
      'state_province' => $this->randomItem('state_province'),
      'evidence_note' => $this->randomItem('evidence_note'),
    );

    $this->insertCustomData($gid, $values);
  }

  /**
   * This method populates the Medical & Disability Custom Table
   */
  private function addMedicalData($cid) {
    if (!$gid = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_CustomGroup', 'Medical_Disability', 'id', 'name')) {
      return;
    }

    $values = array(
      'entity_id' => $cid,
      'condition' => $this->randomItem('condition'),
      'medical_type' => $this->randomItem('medical_type'),
      'special_requirements' => $this->randomItem('special_requirements'),
      'evidence_note' => $this->randomItem('evidence_note'),
    );

    $this->insertCustomData($gid, $values);
  }

  /**
   * This method populates the Qualifications Custom Table
   */
  private function addQualifications($cid) {
    if (!$gid = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_CustomGroup', 'Qualifications', 'id', 'name')) {
      return;
    }

    $values = array(
      'entity_id' => $cid,
      'name_of_skill' => $this->randomItem('name_of_skill'),
      'category_of_skill' => $this->randomItem('category_of_skill'),
      'level_of_skill' => $this->randomItem('level_of_skill'),
      'certification_acquired' => $this->randomItem('certification_acquired'),
      'name_of_certification' => $this->randomItem('name_of_certification'),
      'certification_authority' => $this->randomItem('certification_authority'),
      'grade_achieved' => $this->randomItem('grade_achieved'),
      'attain_date' => $this->randomDate('20090101', '20111231'),
      'expiry_date' => $this->randomDate('20120101', '20141231'),
      'evidence_note' => $this->randomItem('evidence_note'),
    );

    $this->insertCustomData($gid, $values);
  }

  /**
   * This method populates the Visa/Immigration Custom Table
   */
  private function addVisaDetails($cid) {
    if (!$gid = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_CustomGroup', 'Immigration', 'id', 'name')) {
      return;
    }

    $values = array(
      'entity_id' => $cid,
      'visa_type' => $this->randomItem('visa_type'),
      'start_date' => $this->randomDate('20090101', '20111231'),
      'end_date' => $this->randomDate('20120101', '20141231'),
      'conditions' => $this->randomItem('conditions'),
      'visa_number' => $this->randomItem('visa_number'),
      'evidence_note' => $this->randomItem('evidence_note'),
      'sponsor_certificate_number' => $this->randomItem('sponsor_certificate_number'),
    );

    $this->insertCustomData($gid, $values);
  }

  function addEmergencyContact($cid) {
    if (!$gid = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_CustomGroup', 'Emergency_Contact', 'id', 'name')) {
      return;
    }

    $relationshipTypeId = CRM_Core_DAO::getFieldValue('CRM_Contact_DAO_RelationshipType', 'Emergency Contact', 'id', 'name_a_b');
    $relationship = new CRM_Contact_DAO_Relationship();
    $relationship->relationship_type_id = $relationshipTypeId;
    $relationship->contact_id_a = $this->randomIndex(array_flip($this->Individual));
    $relationship->contact_id_b = $cid;
    $relationship->is_active = 1;

    $this->_insert($relationship);
    $values = array(
      'entity_id' => $relationship->id,
      'priority' => $this->randomItem('priority')
    );

    $this->insertCustomData($gid, $values);
  }

  function addCareerData($cid) {
    if (!$gid = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_CustomGroup', 'Career', 'id', 'name')) {
      return;
    }

    $values = array(
      'entity_id' => $cid,
      'start_date' => $this->randomDate('20090101', '20111231'),
      'end_date' => $this->randomDate('20120101', '20141231'),
      'name_of_organisation' => $this->randomItem('name_of_organisation'),
      'occupation_type' => $this->randomItem('occupation_type'),
      'job_title_course_name' => $this->randomItem('job_title_course_name'),
      'full_time_part_time' => $this->randomItem('contracted_hours'),
      'paid_unpaid' => $this->randomItem('paid_unpaid'),
      'reference_supplied' => $this->randomItem('reference_supplied'),
      'evidence_note' => $this->randomItem('evidence_note'),
    );

    $this->insertCustomData($gid, $values);
  }

  /**
   * This is a common method called to insert the data into the custom table
   */
  private function insertCustomData($gid, $columnVals) {
    $tableName = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_CustomGroup', $gid, 'table_name', 'id');
    $cfDetails = array();
    CRM_Core_DAO::commonRetrieveAll('CRM_Core_BAO_CustomField', 'custom_group_id', $gid, $cfDetails);
    foreach ($cfDetails as $fieldID => $value) {
      $columnNames[] = $value['column_name'];
    }

    $ignoreFieldsByGroup = array(
      'Qualifications' => array('evidence_file_26'),
      'Identify' => array('evidence_file_37'),
      'Medical_Disability' => array('evidence_file_39'),
      'Immigration' => array('evidence_file_41'),
      'Career' => array('evidence_file_42'),
    );
    foreach ($ignoreFieldsByGroup as $groupName => $ignoreFields) {
      if ($gid == CRM_Core_DAO::getFieldValue('CRM_Core_DAO_CustomGroup', $groupName, 'id', 'name')) {
        $columnNames = array_diff($columnNames, $ignoreFields);
      }
    }

    $columns = implode("`,`", $columnNames);
    $columnValues = implode("','", array_values($columnVals));
    $query = "INSERT INTO {$tableName} (`entity_id`,`{$columns}`) VALUES ('{$columnValues}')";
    $dao = CRM_Core_DAO::executeQuery($query);
  }

  /**
   * This is a method to create absence periods
   */
  private function addAbsencePeriods() {
    if (CRM_HRAbsence_BAO_HRAbsencePeriod::getRecordCount($params = array()) != 0) {
      CRM_Core_DAO::executeQuery("DELETE FROM civicrm_hrabsence_period");
    }

    // Create a set of absence periods
    $currentYear = date('Y');

    $years = array();
    for ($i = 4; $i > 0; $i--) {
      $years[] = array(
        'startYear' => $currentYear - ($i - 1),
        'endYear' => $currentYear - ($i - 2),
      );
    }

    $periods = array();
    foreach ($years as $year) {
      $periods[] = array(
        'name' => "FY{$year['startYear']}",
        'title' => "FY{$year['startYear']} (Apr {$year['startYear']} - Mar {$year['endYear']})",
        'start_date' => "{$year['startYear']}-04-01 00:00:00",
        'end_date' => "{$year['endYear']}-03-31 23:59:59",
      );
    }

    foreach ($periods as $absencePeriod) {
      civicrm_api3('HRAbsencePeriod', 'create', $absencePeriod);
    }
  }

  /**
   * This is a method to create absence entitlements
   */
  private function addAbsenceEntitlements($cid) {
    $periods = CRM_HRAbsence_BAO_HRAbsencePeriod::getPeriods();
    $periodIds = array_keys($periods);

    //create period combinations
    $employmentPeriodClusters = array(
      array($periodIds[0]),
      array($periodIds[0], $periodIds[1], $periodIds[2]),
      array($periodIds[2], $periodIds[3], $periodIds[1]),
      array($periodIds[3], $periodIds[2], $periodIds[0]),
      array($periodIds[0], $periodIds[1], $periodIds[2], $periodIds[3]),
    );

    //every period will have following absenceTypes
    $absenceTypes = civicrm_api3('HRAbsenceType', 'get', array());

    //pick up random period
    $employmentPeriods = $employmentPeriodClusters[mt_rand(0, 4)];

    foreach ($employmentPeriods as $this->employmentPeriod) {
      foreach ($absenceTypes['values'] as $absenceType) {
        if ($absenceType['name'] != "TOIL" && $absenceType['name'] != "Other") {
          $absenceEntitlementValues = array(
            'contact_id' => $cid,
            'period_id' => $this->employmentPeriod,
            'type_id' => $absenceType['id'],
            'amount' => mt_rand(5, 15),
          );
          //create Entitlement
          civicrm_api3('HRAbsenceEntitlement', 'create', $absenceEntitlementValues);
        }
      }
      // add absence requests per employmentPeriod.
      $this->addAbsenceRequests($cid);
    }
  }

  /*
   * Adds absence requests to records.
   */
  private function addAbsenceRequests($cid) {

    $activityTypes = civicrm_api3('ActivityType', 'get', array());

    $parentActivities = array('Vacation', 'Sick');

    $periods = civicrm_api3('HRAbsencePeriod', 'get', array());
    $fYStartDate = strtotime($periods['values'][$this->employmentPeriod]['start_date']);
    $fYEndDate = strtotime($periods['values'][$this->employmentPeriod]['end_date']);

    $absenceCount = mt_rand(1, 5);

    while ($absenceCount --) {
      $absenceRequest = civicrm_api3('Activity', 'create', array(
        'activity_type_id' => array_search($parentActivities[array_rand($parentActivities)], $activityTypes['values']),
        'source_contact_id' => $cid, // logged in user
        'target_contact_id' => $cid, // the person who takes the absence
        'activity_date_time' => date("Y-m-d h:i:s", time()),
        'status_id' => mt_rand(1, 2), // Scheduled or Completed
      ));

      $start_date = mt_rand($fYStartDate, $fYEndDate);

      $duration = array(
        // 50% full; 33% half; 17% blank
        8 * 60, // full day
        8 * 60, // full day
        8 * 60, // full day
        4 * 60, // half day
        4 * 60, // half day
        0 * 60, // blank
      );

      // create array of absences to be added
      $absenceValues = array();
      for ($i = 0; $i <= mt_rand(1, 3); $i++) {
        $absenceValues[] = array(
          'activity_date_time' => date("Y-m-d h:i:s", strtotime("+" . $i . "day", $start_date)),
          'duration' => $duration[array_rand($duration)],
          'source_contact_id' => $cid,
        );
      }

      // add absence
      civicrm_api3('Activity', 'replace', array(
        'activity_type_id' => array_search('Absence', $activityTypes['values']),
        'source_record_id' => $absenceRequest['id'],
        'values' => $absenceValues,
      ));
    }
  }

  private function addHolidays($cid) {
    $publicHolidays = array('1 January', '18 April','21 April','5 May','26 May','25 August','25 December','26 December');
    $publicholidays_sub = array("New Year's Day", 'Good Friday','Easter Monday','Early May bank holiday','Spring bank holiday','Summer bank holiday','Christmas Day','Boxing Day');
    $params = array('sequential' => 1,
      'name' => 'Public Holiday',
      'return'=> 'value',
    );
    $activity_id = civicrm_api3('OptionValue', 'getvalue', $params );
    $holidayId = civicrm_api3('Activity', 'get', array('activity_type_id'=> $activity_id ,));
    foreach ($holidayId['values'] as $key=>$val) {
      civicrm_api3('Activity', 'delete', array('id' =>$key ));
    }
    $holidayCount = 7;
    $i = 0;

    while ($holidayCount --) {
      $result = civicrm_api3('Activity', 'create',array(
        'activity_type_id' => $activity_id  ,
        'activity_date_time' => date("Y-m-d h:i:s", strtotime($publicHolidays[$i])) ,
        'status_id' => CRM_Core_OptionGroup::values('activity_status', FALSE, NULL, NULL, 'AND v.name = "Scheduled"'),
        'subject' => $publicholidays_sub[$i] ,
        'source_contact_id' => $cid,
      ));
      $i++;
    }
  }
  private function addVacancies($cid) {
    //sample data for HRRecruitment table
    $grpParams['name'] = 'vacancy_status';
    $optionValues = $ids = array();
    CRM_Core_OptionValue::getValues($grpParams,$optionValues);
    foreach ($optionValues as $Id => $value) {
      $ids[] = $value['value'];
    }
    $status_id = array_flip($ids);

    for ($i = 1; $i <= mt_rand(1, 3); $i++) {
      $position = $this->randomItem('vacancyposition');
      $vacanciesValues = array(
        'salary' => $this->randomItem('salary'),
        'position' => $position,
        'description' => $this->randomItem('vacancydescription'),
        'benefits' => $this->randomItem('benefits'),
        'requirements' => $this->randomItem('requirements'),
        'location' => $this->randomItem('vacancylocation'),
        'is_template' => 0,
        'status_id' => array_rand($status_id,1),
        'start_date' => $this->randomDate('2009-01-01', '2011-12-31','YmdHis'),
        'end_date' =>  $this->randomDate('2012-01-01', '2014-12-31','YmdHis'),
      );
      if ($i == 1) {
        $vacanciesValues['is_template'] = 1;
      }
      $hrVacancies[] = $this->insertVacancyData('CRM_HRRecruitment_DAO_HRVacancy', $vacanciesValues);
    }
    //For each HRVacancies, there may be 0 or 1 records for each of these entity types: HRVacancyCases, HRVacancyStage, HRVacancy_permission.
    $gid = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_CustomGroup', 'application_case', 'id', 'name');

    foreach ($hrVacancies as $key => $hrVacanciesObj) {
      //sample data for HRVacancy Cases table
      for ($i = 1; $i <= mt_rand(0, 1); $i++) {
        $applicationParams = array( 'contact_id' =>$cid,
          'case_type_id' => 'Application',
          'subject' => $position,
          'status_id'=> array_rand($status_id,1));
        $case = CRM_Case_BAO_Case::create($applicationParams);
        $applicationParams['case_id'] = $case->id;
        $caseContact = CRM_Case_BAO_Case::addCaseToContact($applicationParams);

        $values = array( 'entity_id' =>  $case->id,
          'vacancy_id' => $hrVacanciesObj->id
        );
        $this->insertCustomData($gid, $values);
      }
      //sample data for HRVacancy Stages table
      for ($i = 1; $i <= mt_rand(0, 1); $i++) {
        $vacancyStagesValues = array(
          'case_status_id' => mt_rand(0, 1),
          'vacancy_id' => $hrVacanciesObj->id,
          'weight' => mt_rand(0, 1),
        );
        $this->insertVacancyData('CRM_HRRecruitment_DAO_HRVacancyStage', $vacancyStagesValues);
      }
      //sample data for HRPermission table
      for ($i = 1; $i <= mt_rand(0, 1); $i++) {
        $vacancyPermissionValues = array(
          'contact_id' => $cid,
          'vacancy_id' => $hrVacanciesObj->id,
          'permission' => $this->randomItem('permission'),
        );
        $this->insertVacancyData('CRM_HRRecruitment_DAO_HRVacancyPermission', $vacancyPermissionValues);
      }
      //sample data for HRPermission UFJoin table sample data will be entered in later stage
      /* for ($i = 1; $i <= mt_rand(0, 1); $i++) { */
      /*   $vacancyUFJoinValues = array( */
      /*                                'module' => 'Vacancy', */
      /*                                'entity_id' => $hrVacanciesObj->id, */
      /*                                'entity_table' => 'civicrm_hrvacancy', */
      /*                                'uf_group_id' => '1', */
      /*                                ); */
      /*   $this->insertVacancyData('CRM_Core_DAO_UFJoin', $vacancyUFJoinValues); */
      /* } */
    }
  }

  /**
   * This is a common method called to insert the data into HRVacancies tables
   */
  function insertVacancyData($className, $values) {
    $dao = new $className();
    foreach ($values as $columnName => $columnValue) {
      $dao->$columnName = $columnValue;
    }
    $dao->save();
    return $dao;
  }
}

$obj1 = new GenerateHRData();
$obj1->initID();
$obj1->generate('Organization');
$obj1->generate('Individual');
CRM_HRJob_Estimator::updateEstimates(); // generator above bypasses API