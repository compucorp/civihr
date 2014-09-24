<?php
/*
+--------------------------------------------------------------------+
| CiviHR version 1.4                                                 |
+--------------------------------------------------------------------+
| Copyright CiviCRM LLC (c) 2004-2014                                |
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
 *  Provide data to the CRM_HRJob_BAO_QueryTest class
 *
 *  @package CiviCRM
 */
class CRM_HRJob_BAO_QueryTestDataProvider implements Iterator {

  /**
   *  @var integer
   */
  private $i = 0;

  /**
   *  @var mixed[]
   *  This dataset describes various form values and what contact
   *  IDs should be selected when the form values are applied to the
   *  database in dataset.xml
   */
  private $dataset = array(
    array(
      'fv' => array(
        'hrjob_position' => 'Fundraiser',
        'hrjob_title' => 'Copy-editor'
      ),
      'id' => array(
        '9','10',
      ),
    ),
    array(
      'fv' => array(
        'hrjob_role_level_type' => 'Senior Manager',
        'hrjob_period_type' => 'Permanent',
      ),
      'id' => array(
        '9',
      ),
    ),
    array(
      'fv' => array(
        'hrjob_is_healthcare' => 1,
      ),
      'id' => array(
        '9', '11',
      ),
    ),
    array(
      'fv' => array(
        'hrjob_is_healthcare' => 1,
        'hrjob_contract_type' => "Apprentice",
        'hrjob_title' => "Chief Nomenclature Officer",
      ),
      'id' => array(
        '11',
      ),
    ),
    array(
      'fv' => array(
        'hrjob_contract_type' => "Apprentice",
      ),
      'id' => array(
        '10', '11',
      ),
    ),
    array(
      'fv' => array(
        'hrjob_is_enrolled' => 1,
        'hrjob_contract_type' => "Apprentice",
      ),
      'id' => array(
        '11',
      ),
    ),
    array(
      'fv' => array(
        'hrjob_is_paid' => "1",
      ),
      'id' => array(
        '9', '10', '11',
      ),
    ),
    array(
      'fv' => array(
        'hrjob_is_enrolled' => 1,
      ),
      'id' => array(
        '9', '11',
      ),
    ),
    array(
      'fv' => array(
        'hrjob_is_enrolled' => 0,
      ),
      'id' => array(
        '10',
      ),
    ),
    array(
      'fv' => array(
        'hrjob_is_enrolled' => 0,
        'hrjob_is_paid' => "1",
      ),
      'id' => array(
        '10',
      ),
    ),
    array(
      'fv' => array(
        'hrjob_hours_amount_low' => 20,
        'hrjob_hours_amount_high' => 100,
      ),
      'id' => array(
        '9', '10', '11',
      ),
    ),
    array(
      'fv' => array(
        'hrjob_period_start_date_low' => "01/24/2010",
        'hrjob_period_start_date_high' => "04/24/2010",
        'hrjob_period_end_date_low' => "07/27/2013",
        'hrjob_period_end_date_high' => "09/27/2013",
      ),
      'id' => array(
        '9', '11',
      ),
    ),
    array(
      'fv' => array(
        'hrjob_hours_fte_low' => 1,
        'hrjob_hours_fte_high' => 10,
      ),
      'id' => array(
        '9', '10', '11',
      ),
    ),
    array(
      'fv' => array(
        'hrjob_hours_type' => 0,
      ),
      'id' => array(
        '9', '10',
      ),
    ),
    array(
      'fv' => array(
        'hrjob_hours_unit' => "Week",
      ),
      'id' => array(
        '9', '10', '11',
      ),
    ),
    array(
      'fv' => array(
        'hrjob_hours_unit' => "Week",
        'hrjob_hours_type' => 4,
      ),
      'id' => array(
        '11',
      ),
    ),
    array(
      'fv' => array(
        'hrjob_hours_unit' => "Week",
        'hrjob_hours_type' => 0,
        'hrjob_hours_fte_low' => 2,
        'hrjob_hours_fte_high' => 20,
      ),
      'id' => array(
        '9', '10',
      ),
    ),
    array(
      'fv' => array(
        'hrjob_period_start_date_low' => "01/24/2010",
        'hrjob_period_start_date_high' => "04/24/2010",
        'hrjob_period_end_date_low' => "07/27/2013",
        'hrjob_period_end_date_high' => "09/27/2013",
        'hrjob_is_enrolled' => 1,
        'hrjob_is_paid' => "1",
        'hrjob_is_healthcare' => 1,
      ),
      'id' => array(
        '9', '11',
      ),
    ),
    array(
      'fv' => array(
        'hrjob_role_level_type' => 'Junior Staff',
        'hrjob_period_type' => 'Temporary',
        'hrjob_hours_type' => 0,
        'hrjob_is_paid' => "1",
      ),
      'id' => array(
        '10',
      ),
    ),
    array(
      'fv' => array(
        'hrjob_is_paid' => "0",
        'hrjob_contract_type' => "Intern",
      ),
      'id' => array(
        '9',
      ),
    ),
    array(
      'fv' => array(
        'hrjob_is_paid' => "0",
        'hrjob_position' => "Volunteer Manager",
        'hrjob_title' => "Research Assistant",
      ),
      'id' => array(
        '9',
      ),
    ),
    // cross-over test - mixing with unrelated fields
    array(
      'fv' => array(
        'hrjob_position' => 'Fundraiser',
        'email' => '@example',
      ),
      'id' => array(
        '9','10',
      ),
    ),
    // cross-over test - mixing with unrelated fields
    array(
      'fv' => array(
        'hrjob_position' => 'Fundraiser',
        'email' => '@example.org',
      ),
      'id' => array(
        '10',
      ),
    ),
  );

  public function _construct() {
    $this->i = 0;
  }

  public function rewind() {
    $this->i = 0;
  }

  public function current() {
    $count = count($this->dataset[$this->i]['id']);
    $ids   = $this->dataset[$this->i]['id'];
    $full  = array();
    foreach ($this->dataset[$this->i]['id'] as $key => $value) {
      $full[] = array(
        'contact_id' => $value,
        'contact_type' => 'Individual',
        'sort_name' => "Test Contact $value",
      );
    }
    return array($this->dataset[$this->i]['fv'], $count, $ids, $full);
  }

  public function key() {
    return $this->i;
  }

  public function next() {
    $this->i++;
  }

  public function valid() {
    return isset($this->dataset[$this->i]);
  }
}

