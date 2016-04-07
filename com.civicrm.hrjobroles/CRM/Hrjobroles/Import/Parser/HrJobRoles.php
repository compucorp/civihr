<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.5                                                |
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
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2014
 * $Id$
 *
 */

require_once 'api/api.php';

/**
 * class to parse activity csv files
 */
class CRM_Hrjobroles_Import_Parser_HrJobRoles extends CRM_Hrjobroles_Import_Parser {

  protected $_mapperKeys;


  /**
   * Array of select lists options in job roles page
   *
   * @var array
   */

  private $_optionsList;

  /**
   * Array of parameters to be imported
   *
   * @var array
   */

  private $_params;

  /**
   * Array of successfully imported activity id's
   *
   * @array
   */
  protected $_newJobRole;

  /**
   * class constructor
   */
  function __construct(&$mapperKeys) {
    parent::__construct();
    $this->_mapperKeys = &$mapperKeys;
  }

  /**
   * the initializer code, called before the processing
   *
   * @return void
   * @access public
   */
  function init() {
    $fields = CRM_Hrjobroles_BAO_HrJobRoles::importableFields();

    foreach ($fields as $name => $field) {
      $field['type'] = CRM_Utils_Array::value('type', $field, CRM_Utils_Type::T_INT);
      $field['dataPattern'] = CRM_Utils_Array::value('dataPattern', $field, '//');
      $field['headerPattern'] = CRM_Utils_Array::value('headerPattern', $field, '//');
      $this->addField($name, $field['title'], $field['type'], $field['headerPattern'], $field['dataPattern']);
    }

    $this->_newJobRole = array();

    $this->setActiveFields($this->_mapperKeys);


    // Fetch select list options from the database and cache them
    $this->_optionsList['location'] = CRM_Hrjobroles_BAO_HrJobRoles::buildDbOptions('hrjc_location');
    $this->_optionsList['region'] = CRM_Hrjobroles_BAO_HrJobRoles::buildDbOptions('hrjc_region');
    $this->_optionsList['hrjc_role_department'] = CRM_Hrjobroles_BAO_HrJobRoles::buildDbOptions('hrjc_department');
    $this->_optionsList['hrjc_level_type'] = CRM_Hrjobroles_BAO_HrJobRoles::buildDbOptions('hrjc_level_type');
    $this->_optionsList['hrjc_cost_center'] = CRM_Hrjobroles_BAO_HrJobRoles::buildDbOptions('cost_centres');

  }

  /**
   * handle the values in mapField mode
   *
   * @param array $values the array of values belonging to this line
   *
   * @return boolean
   * @access public
   */
  function mapField(&$values) {
    return CRM_Import_Parser::VALID;
  }

  /**
   * handle the values in preview mode
   *
   * @param array $values the array of values belonging to this line
   *
   * @return boolean      the result of this processing
   * @access public
   */
  function preview(&$values) {
    return $this->summary($values);
  }

  /**
   * handle the values in summary mode
   *
   * @param array $values the array of values belonging to this line
   *
   * @return boolean      the result of this processing
   * @access public
   */
  function summary(&$values) {
    $erroneousField = NULL;
    $this->setActiveFieldValues($values, $erroneousField);

    $params = &$this->getActiveFieldParams();

    $errorMessage = NULL;

    //for date-Formats
    $session = CRM_Core_Session::singleton();
    $dateType = $session->get('dateTypes');

    $contractDetails = CRM_Hrjobroles_BAO_HrJobRoles::checkContract($params['job_contract_id']);
    if ($contractDetails == 0)  {
      CRM_Contact_Import_Parser_Contact::addToErrorMsg('job contract ID is not found', $errorMessage);
    }
    else  {

      // check some parameters if they are valid

      foreach ($params as $key => $val) {
        switch($key)  {
          case 'title':
            if (empty($val)) {
                CRM_Contact_Import_Parser_Contact::addToErrorMsg('job role title is required', $errorMessage);
            }
            break;
          case 'hrjc_role_start_date':
            if ($val) {
              $dateValue = CRM_Utils_Date::formatDate($val, $dateType);
              if ($dateValue) {
                $params[$key] = $dateValue;
              }
              else {
                CRM_Contact_Import_Parser_Contact::addToErrorMsg('start date is not a valid date', $errorMessage);
              }
            }
            break;
          case 'hrjc_role_end_date':
            if ($val) {
              $dateValue = CRM_Utils_Date::formatDate($val, $dateType);
              if ($dateValue) {
                $params[$key] = $dateValue;
              }
              else {
                CRM_Contact_Import_Parser_Contact::addToErrorMsg('end date is not a valid date', $errorMessage);
              }
            }
            break;
          case 'hrjc_role_amount_pay_cost_center':
            if ( !filter_var($val, FILTER_VALIDATE_FLOAT) || $val < 0)  {
              CRM_Contact_Import_Parser_Contact::addToErrorMsg('Amount of Pay Assigned to cost center should be positive number', $errorMessage);
            }
            break;
          case 'hrjc_role_amount_pay_funder':
            if ( !filter_var($val, FILTER_VALIDATE_FLOAT) || $val < 0 )  {
              CRM_Contact_Import_Parser_Contact::addToErrorMsg('Amount of Pay Assigned to funder should be positive number', $errorMessage);
            }
            break;
          case 'hrjc_role_percent_pay_cost_center':
            if ( !CRM_Utils_Rule::positiveInteger($val))  {
              CRM_Contact_Import_Parser_Contact::addToErrorMsg('Percent of Pay Assigned to cost center should be positive number', $errorMessage);
            }
            break;
          case 'hrjc_role_percent_pay_funder':
            if ( !filter_var($val, FILTER_VALIDATE_FLOAT) || $val < 0 )  {
              CRM_Contact_Import_Parser_Contact::addToErrorMsg('Percent of Pay Assigned to funder should be positive number', $errorMessage);
            }
            break;
          case 'hrjc_funder_val_type':
            if ( $val != 0 && $val != 1)  {
              CRM_Contact_Import_Parser_Contact::addToErrorMsg('funder value type should be either 0 or 1', $errorMessage);
            }
            break;
          case 'hrjc_cost_center_val_type':
            if ( $val != 0 && $val != 1)  {
              CRM_Contact_Import_Parser_Contact::addToErrorMsg('cost center value type should be either 0 or 1', $errorMessage);
            }
            break;
          case 'location':
            if ( !empty($val)) {
              $optionID = $this->getOptionKey($key, $val);
              if ($optionID != 0) {
                $params[$key] = $optionID;
              } else {
                CRM_Contact_Import_Parser_Contact::addToErrorMsg('location is not found', $errorMessage);
              }
            }
            break;
          case 'region':
            if ( !empty($val)) {
              $optionID = $this->getOptionKey($key, $val);
              if ($optionID != 0) {
                $params[$key] = $optionID;
              } else {
                CRM_Contact_Import_Parser_Contact::addToErrorMsg('region is not found', $errorMessage);
              }
            }
            break;
          case 'hrjc_role_department':
            if ( !empty($val)) {
              $optionID = $this->getOptionKey($key, $val);
              if ($optionID != 0) {
                $params[$key] = $optionID;
              } else {
                CRM_Contact_Import_Parser_Contact::addToErrorMsg('department is not found', $errorMessage);
              }
            }
            break;
          case 'hrjc_level_type':
            if ( !empty($val)) {
              $optionID = $this->getOptionKey($key, $val);
              if ($optionID != 0) {
                $params[$key] = $optionID;
              } else {
                CRM_Contact_Import_Parser_Contact::addToErrorMsg('level type is not found', $errorMessage);
              }
            }
            break;
          case 'hrjc_cost_center':
            if ( !empty($val)) {
              $optionID = $this->getOptionKey($key, $val);
              if ($optionID != 0) {
                $params[$key] = $optionID;
              } else {
                CRM_Contact_Import_Parser_Contact::addToErrorMsg('cost center is not found', $errorMessage);
              }
            }
            break;
        }
      }

      // check if job role start and end dates if exist matches or within contract start and end dates

      $contractStartDate = CRM_Utils_Date::formatDate($contractDetails->period_start_date, $dateType);
      $contractEndDate = CRM_Utils_Date::formatDate($contractDetails->period_end_date, $dateType);

      if (isset($params['hrjc_role_start_date']))  {
        $roleStartDate = $params['hrjc_role_start_date'];
        if ($roleStartDate < $contractStartDate)  {
          CRM_Contact_Import_Parser_Contact::addToErrorMsg('job role start date should not be before contract start date', $errorMessage);
        }
        if (isset($contractEndDate) && ($roleStartDate > $contractEndDate) )  {
          CRM_Contact_Import_Parser_Contact::addToErrorMsg('job role start date should not be after contract end date', $errorMessage);
        }
      }
      else  {
        $params['hrjc_role_start_date'] = $contractStartDate;
      }

      if (isset($params['hrjc_role_end_date']))  {
        $roleEndDate = $params['hrjc_role_end_date'];
        if ($roleEndDate <=  $params['hrjc_role_start_date'])  {
          CRM_Contact_Import_Parser_Contact::addToErrorMsg('job role end date should not be before or equal job role start date', $errorMessage);
        }
        if (isset($contractEndDate) && $roleEndDate > $contractEndDate)  {
          CRM_Contact_Import_Parser_Contact::addToErrorMsg('job role end date should not be after contract end date', $errorMessage);
        }
      }
      elseif (isset($contractEndDate))  {
        $params['hrjc_role_start_date'] = $contractEndDate;
      }

      if (!empty($params['funder'])) {
        $funder_value = $params['funder'];
        if (is_numeric ($funder_value))  {
          $search_field = 'id';
        }
        else {
          $search_field = 'display_name';
        }

        $result = CRM_Hrjobroles_BAO_HrJobRoles::checkContact($funder_value, $search_field);
        if ($result != 0)  {
          $params['funder'] = $result;
        }
        else  {
          CRM_Contact_Import_Parser_Contact::addToErrorMsg('funder is not an existing contact', $errorMessage);
        }
      }

    }


    if ($errorMessage) {
      $tempMsg = "Invalid value for field(s) : $errorMessage";
      array_unshift($values, $tempMsg);
      $errorMessage = NULL;
      return CRM_Import_Parser::ERROR;
    }

    $this->_params = $params;

    return CRM_Import_Parser::VALID;
  }

  /**
   * handle the values in import mode
   *
   * @param int $onDuplicate the code for what action to take on duplicates
   * @param array $values the array of values belonging to this line
   *
   * @return boolean      the result of this processing
   * @access public
   */
  function import($onDuplicate, &$values) {
    // first make sure this is a valid line
    $response = $this->summary($values);

    if ($response != CRM_Import_Parser::VALID) {
      return $response;
    }
    $params = $this->_params;


    $params['version'] = 3;
    $newJobRole = civicrm_api('HrJobRoles', 'create', $params);

      if (!empty($newJobRole['is_error'])) {
        array_unshift($values, $newJobRole['error_message']);
        return CRM_Import_Parser::ERROR;
      }

    $this->_params = NULL;
    $this->_newJobRole[] = $newJobRole['id'];
    return CRM_Import_Parser::VALID;

  }

  /**
   * the initializer code, called before the processing
   *
   * @return void
   * @access public
   */
  function fini() {}


  /**
   * get the option database ID given its label or ID
   * @param String|Integer $option
   * @param String|Integer $value
   * @return Integer
   * @access private
   */
   private function getOptionKey($option, $value)  {
     if (is_numeric ($value)) {
       $search_field = 'id';
     }
     else {
       $search_field = 'label';
     }
     $index = array_search(strtolower($value), array_column($this->_optionsList[$option], $search_field));
     if ($index !== FALSE)  {
       return (int) $this->_optionsList[$option][$index]['id'];
     }
     else {
       return 0;
     }
  }

}

