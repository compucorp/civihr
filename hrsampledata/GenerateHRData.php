<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.3                                                |
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
  CONST DEBUG_LEVEL = 1;

  CONST NUM_CONTACT = 20;
  CONST INDIVIDUAL_PERCENT = 80;
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
    foreach((array) simplexml_load_file(dirname(__FILE__) . '/xml/sample_data.xml') as $key => $val) {
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

    $this->contact = range(2, self::NUM_CONTACT + 1);
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
  private $phoneType = array('1', '2', '3', '4');

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
  private $stateMap = array();
  private $states = array();

  private $groupMembershipStatus = array('Added', 'Removed', 'Pending');
  private $subscriptionHistoryMethod = array('Admin', 'Email');

  /*********************************
   * private methods
   *********************************/

  // get a randomly generated string
  private function randomString($size = 32) {
    $string = "";

    // get an ascii code for each character
    for ($i = 0; $i < $size; $i++) {
      $random_int = mt_rand(65, 122);
      if (($random_int < 97) && ($random_int > 90)) {
        // if ascii code between 90 and 97 substitute with space
        $random_int = 32;
      }
      $random_char = chr($random_int);
      $string .= $random_char;
    }
    return $string;
  }

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
   * @param  int $startDate Start Date in Unix timestamp
   * @param  int $endDate   End Date in Unix timestamp
   * @access private
   *
   * @return string randomly generated date in the format "Ymd"
   *
   */
  private function randomDate($startDate = 0, $endDate = 0) {

    // number of seconds per year
    $numSecond = 31536000;
    $dateFormat = "Ymdhis";
    $today = time();

    // both are defined
    if ($startDate && $endDate) {
      return date($dateFormat, mt_rand($startDate, $endDate));
    }

    // only startDate is defined
    if ($startDate) {
      return date($dateFormat, mt_rand($startDate, $startDate + $numSecond));
    }

    // only endDate is defined
    if ($startDate) {
      return date($dateFormat, mt_rand($endDate - $numSecond, $endDate));
    }

    // none are defined
    return date($dateFormat, mt_rand($today - $numSecond, $today));
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
   * Execute a query unless we are doing a dry run
   * Note: this wrapper should not be used for SELECT queries
   */
  private function _query($query, $params = array()) {
    if (self::ADD_TO_DB) {
      return CRM_Core_DAO::executeQuery($query, $params);
    }
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


  public function randomName() {
    $first_name = $this->randomItem(($this->probability(.5) ? 'fe' : '') . 'male_name');
    $middle_name = ucfirst($this->randomChar());
    $last_name = $this->randomItem('last_name');
    return "$first_name $middle_name. $last_name";
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
      $contact->contact_type = $this->getContactType($id + 1);
      $contact->do_not_phone = $this->probability(.2);
      $contact->do_not_email = $this->probability(.2);
      $contact->do_not_post = $this->probability(.2);
      $contact->do_not_trade = $this->probability(.2);
      $contact->preferred_communication_method = NULL;
      if ($this->probability(.5)) {
        $contact->preferred_communication_method = CRM_Core_DAO::VALUE_SEPARATOR . $this->randomItem($this->preferredCommunicationMethod) . CRM_Core_DAO::VALUE_SEPARATOR;
      }
      $this->_insert($contact);
    }
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
      $contact->is_deceased = $contact->gender_id = $contact->birth_date = $contact->deceased_date = $email = NULL;
      list($gender_id, $gender) = $this->randomKeyValue($this->gender);
      $birth_date = mt_rand($now - 90 * $year, $now - 10 * $year);

      $contact->last_name = $this->randomItem('last_name');

      if ($this->probability(.6)) {
        $this->_addAddress($cid);
      }

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
        $this->_addEmail($cid, $email, self::HOME);
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
      $this->addJobPositions($cid);
      //if Identification (CiviHR) extension is enabled, add the sample data
      $this->addIdentificationData($cid);
      //if Medical and Disability (CiviHR) extension is enabled, add the sample data
      $this->addMedicalData($cid);
      //if Qualifications (CiviHR) extension is enabled, add the sample data
      $this->addQualifications($cid);
      //if Immigration / Visas (CiviHR) extension is enabled, add the sample data
      $this->addVisaDetails($cid); 
    }
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
      $address = $this->_addAddress($id);

      $namePre = $this->randomItem('organization_prefix');
      $nameMid = $this->randomItem('organization_name');
      $namePost = $this->randomItem('organization_suffix');

      // Some orgs are named after their location
      if ($this->probability(.7)) {
        $place = $this->randomItem(array('city', 'street_name', 'state'));
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
  }


  /**
   * Create an address for a contact
   *
   * @param $cid int: contact id
   * @param $masterContactId int: set if this is a shared address
   */
  private function _addAddress($cid, $masterContactId = NULL) {

    // Share existing address
    if ($masterContactId) {
      $dao = new CRM_Core_DAO_Address();
      $dao->is_primary = 1;
      $dao->contact_id = $masterContactId;
      $dao->find(TRUE);
      $dao->master_id = $dao->id;
      $dao->id = NULL;
      $dao->contact_id = $cid;
      $dao->is_primary = $this->isPrimary($cid, 'Address');
      $dao->location_type_id = $this->getContactType($masterContactId) == 'Organization' ? self::WORK : self::HOME;
      $this->_insert($dao);
    }

    // Generate new address
    else {
      $params = array(
        'contact_id' => $cid,
        'location_type_id' => $this->getContactType($cid) == 'Organization' ? self::MAIN : self::HOME,
        'street_number' => mt_rand(1, 1000),
        'street_number_suffix' => ucfirst($this->randomChar()),
        'street_name' => $this->randomItem('street_name'),
        'street_type' => $this->randomItem('street_type'),
        'street_number_postdirectional' => $this->randomItem('address_direction'),
        'county_id' => 1,
      );

      $params['street_address'] = $params['street_number'] . $params['street_number_suffix'] . " " . $params['street_name'] . " " . $params['street_type'] . " " . $params['street_number_postdirectional'];


      if ($params['location_type_id'] == self::MAIN) {
        $params['supplemental_address_1'] = $this->randomItem('supplemental_addresses_1');
      }


      $this->_addDAO('Address', $params);
      $params['state'] = $this->states[$params['state_province_id']];
      return $params;
    }
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
      'location_type_id' => $this->getContactType($cid) == 'Organization' ? self::MAIN : self::HOME,
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
    $part = array_pad(split(' ', strtolower($name)), 3, '');
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
   * This method populates the crm_note table
   */
  private function addNote() {
    $params = array(
      'entity_table' => 'civicrm_contact',
      'contact_id' => 1,
      'privacy' => 0,
    );
    for ($i = 0; $i < self::NUM_CONTACT; $i += 10) {
      $params['entity_id'] = $this->randomItem($this->contact);
      $params['note'] = $this->randomItem('note');
      $params['modified_date'] = $this->randomDate();
      $this->_addDAO('Note', $params);
    }
  }

  /**
   * This method populates the Job Positions Custom Table
   */
  private function addJobPositions($cid) {
    if (!$gid = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_CustomGroup', 'Job_Positions', 'id', 'name')) {
      return;
    }

    $values = array(
      'entity_id' => $cid,
      'job_position_name' => $this->randomItem('job_position_name'),
      'job_title' => $this->randomItem('job_title'),
      'is_budget_for_job_position_tied' => $this->randomItem('is_budget_for_job_position_tied'),
      'contract_type' => $this->randomItem('contract_type'),
      'contract_term' => $this->randomItem('contract_term'),
      'contracted_hours' => $this->randomItem('contracted_hours'),
      'start_date' => $this->randomItem('start_date'),
      'end_date' => $this->randomItem('end_date'),
      'paid_unpaid' => $this->randomItem('paid_unpaid'),
      'standard_hours_per_week' => $this->randomItem('standard_hours_per_week'),
      'notice_period' => $this->randomItem('notice_period'),
      'annual_leave_entitlement_days' => $this->randomItem('annual_leave_entitlement_days'),
      'line_manager' => $this->randomItem('line_manager'),
      'place_of_work' => $this->randomItem('place_of_work'),
      'pay_rate_period' => $this->randomItem('pay_rate_period'),
      'amount_of_pay' => $this->randomItem('amount_of_pay'),
      'pension_contribution' => $this->randomItem('pension_contribution'),
      'pension_contribution_percentage' => $this->randomItem('pension_contribution_percentage'),
      'opted_out_of_automatic_pension' => $this->randomItem('opted_out_of_automatic_pension'),
      'healthcare_insurance' => $this->randomItem('healthcare_insurance'),
      'type_of_healthcare_provision' => $this->randomItem('type_of_healthcare_provision'),
      'job_role_name' => $this->randomItem('job_role_name'),
      'job_role_description' => $this->randomItem('job_role_description'),
      'department' => $this->randomItem('department'),
      'region' => $this->randomItem('region'),
      'role_manager' => $this->randomItem('role_manager'),
      'functional_area' => $this->randomItem('functional_area'),
      'cost_center' => $this->randomItem('cost_center'),
      'team' => $this->randomItem('team'),
      'role_hours' => $this->randomItem('role_hours'),
      'name_of_organisation' => $this->randomItem('name_of_organisation')
    );

    $this->insertCustomData($gid, $values);
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
      'issue_date' => $this->randomItem('issue_date'),
      'expire_date' => $this->randomItem('expire_date'),
      'country' => $this->randomItem('country'),
      'state_province' => $this->randomItem('state_province')
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
      'special_requirements' => $this->randomItem('special_requirements')
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
      'expiry_date' => $this->randomItem('expiry_date')
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
      'visa_type' => $this->randomItem('visa_type')
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
    if ($gid == CRM_Core_DAO::getFieldValue('CRM_Core_DAO_CustomGroup', 'Qualifications', 'id', 'name')) {
      // removing the file field column "evidence_attached" in Qualifications
      array_pop($columnNames);
    }
    $columns = implode("`,`", $columnNames);
    $columnValues = implode("','", array_values($columnVals));
    $query = "INSERT INTO {$tableName} (`entity_id`,`{$columns}`) VALUES ('{$columnValues}')";
    $dao = CRM_Core_DAO::executeQuery($query);
  }
  
}


$obj1 = new GenerateHRData();
$obj1->initID();
$obj1->generate('Contact');
$obj1->generate('Individual');
$obj1->generate('Organization');
$obj1->generate('Note');
