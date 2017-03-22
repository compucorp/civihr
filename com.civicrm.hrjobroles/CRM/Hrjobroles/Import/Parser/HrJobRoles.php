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
    $this->_optionsList['hrjc_region'] = CRM_Hrjobroles_BAO_HrJobRoles::buildDbOptions('hrjc_region');
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

    $contractDetails = NULL;
    if (!empty($params['job_contract_id']))  {
      $contractDetails = CRM_Hrjobcontract_BAO_HRJobContract::checkContract($params['job_contract_id']);
    }

    if (empty($contractDetails))  {
      CRM_Contact_Import_Parser_Contact::addToErrorMsg('job contract ID is not found', $errorMessage);
    }
    else  {

      // START checking imported values if they are valid

      $fields = CRM_Hrjobroles_DAO_HrJobRoles::fields();

      if (empty($params['title'])) {
        CRM_Contact_Import_Parser_Contact::addToErrorMsg($fields['title']['title'].' is required', $errorMessage);
      }

      $optionValues = array('location', 'hrjc_region', 'hrjc_role_department', 'hrjc_level_type');
      foreach($optionValues as $key)  {
        if (!empty($params[$key])) {
          $optionID = $this->getOptionKey($key, $params[$key]);
          if ($optionID !== NULL) {
            $params[$key] = $optionID;
          } else {
            CRM_Contact_Import_Parser_Contact::addToErrorMsg($fields[$key]['title'].' is not found', $errorMessage);
          }
        }
      }

      $cost_center_error = FALSE;
      if (!empty($params['hrjc_cost_center'])) {
        $optionID = $this->getOptionKey('hrjc_cost_center', $params['hrjc_cost_center']);
        if ($optionID !== NULL) {
          $params['hrjc_cost_center'] = $optionID;
          if (!empty($params['hrjc_cost_center_val_type']))  {
            $val = strtolower($params['hrjc_cost_center_val_type']);
            if ($val == 'fixed')  {
              $params['hrjc_cost_center_val_type'] = 0;
            }
            elseif ($val == '%')  {
              $params['hrjc_cost_center_val_type'] = 1;
            }
            else  {
              $cost_center_error = TRUE;
              CRM_Contact_Import_Parser_Contact::addToErrorMsg('cost center value type should be either (fixed) or (%) ', $errorMessage);
            }
          }
          else {
            $cost_center_error = TRUE;
            CRM_Contact_Import_Parser_Contact::addToErrorMsg('cost center value type is not defined', $errorMessage);
          }
        } else {
          $cost_center_error = TRUE;
          CRM_Contact_Import_Parser_Contact::addToErrorMsg('cost center is not found', $errorMessage);
        }
      }
      else {
        $cost_center_error = TRUE;
      }

      if (isset($params['hrjc_cost_center_val_type']))  {
        if ($params['hrjc_cost_center_val_type'] === 0 && !$cost_center_error)  {
          if (!empty($params['hrjc_role_amount_pay_cost_center']) && !empty($params['hrjc_role_percent_pay_cost_center']))  {
            CRM_Contact_Import_Parser_Contact::addToErrorMsg('cost center percent amount should be removed', $errorMessage);
          }
          elseif (!empty($params['hrjc_role_amount_pay_cost_center']))  {
            if ( !filter_var($params['hrjc_role_amount_pay_cost_center'], FILTER_VALIDATE_FLOAT) || $params['hrjc_role_amount_pay_cost_center'] < 0)  {
              CRM_Contact_Import_Parser_Contact::addToErrorMsg('Amount of Pay Assigned to cost center should be positive number', $errorMessage);
            }
          }
          else  {
            CRM_Contact_Import_Parser_Contact::addToErrorMsg('cost center absolute amount is not set', $errorMessage);
          }
          $params['hrjc_role_percent_pay_cost_center'] = 0;
        }
        elseif ($params['hrjc_cost_center_val_type'] === 1 && !$cost_center_error)  {
          if (!empty($params['hrjc_role_amount_pay_cost_center']) && !empty($params['hrjc_role_percent_pay_cost_center']))  {
            CRM_Contact_Import_Parser_Contact::addToErrorMsg('cost center absolute amount should be removed', $errorMessage);
          }
          elseif (!empty($params['hrjc_role_percent_pay_cost_center']))  {
            if ( !filter_var($params['hrjc_role_percent_pay_cost_center'], FILTER_VALIDATE_FLOAT) || $params['hrjc_role_percent_pay_cost_center'] < 0)  {
              CRM_Contact_Import_Parser_Contact::addToErrorMsg('Percent of Pay Assigned to cost center should be positive number', $errorMessage);
            }
          }
          else  {
            CRM_Contact_Import_Parser_Contact::addToErrorMsg('cost center percent amount is not set', $errorMessage);
          }
          $params['hrjc_role_amount_pay_cost_center'] = 0;
        }
      }

      if ($cost_center_error)  {
        unset($params['hrjc_cost_center']);
        unset($params['hrjc_cost_center_val_type']);
        unset($params['hrjc_role_amount_pay_cost_center']);
        unset($params['hrjc_role_percent_pay_cost_center']);
      }


      $funder_error = FALSE;
      if (!empty($params['funder'])) {
        $funder_value = $params['funder'];
        if (is_numeric ($funder_value))  {
          $search_field = 'id';
        }
        else {
          $search_field = 'display_name';
        }
        $result = CRM_Hrjobroles_BAO_HrJobRoles::contactExists($funder_value, $search_field);
        if ($result !== 0)  {
          $params['funder'] = $result;
          if (!empty($params['hrjc_funder_val_type']))  {
            $val = strtolower($params['hrjc_funder_val_type']);
            if ($val == 'fixed')  {
              $params['hrjc_funder_val_type'] = 0;
            }
            elseif ($val == '%')  {
              $params['hrjc_funder_val_type'] = 1;
            }
            else  {
              $funder_error = TRUE;
              CRM_Contact_Import_Parser_Contact::addToErrorMsg('funder value type should be either (fixed) or (%) ', $errorMessage);
            }
          }
          else {
            $funder_error = TRUE;
            CRM_Contact_Import_Parser_Contact::addToErrorMsg('funder value type is not defined', $errorMessage);
          }
        } else {
          $funder_error = TRUE;
          CRM_Contact_Import_Parser_Contact::addToErrorMsg('funder is not an existing contact', $errorMessage);
        }
      }
      else {
        $funder_error = TRUE;
      }

      if (isset($params['hrjc_funder_val_type']))  {
        if ($params['hrjc_funder_val_type'] === 0 && !$funder_error)  {
          if (!empty($params['hrjc_role_amount_pay_funder']) && !empty($params['hrjc_role_percent_pay_funder']))  {
            CRM_Contact_Import_Parser_Contact::addToErrorMsg('funder percent amount should be removed', $errorMessage);
          }
          elseif (!empty($params['hrjc_role_amount_pay_funder']))  {
            if ( !filter_var($params['hrjc_role_amount_pay_funder'], FILTER_VALIDATE_FLOAT) || $params['hrjc_role_amount_pay_funder'] < 0)  {
              CRM_Contact_Import_Parser_Contact::addToErrorMsg('Amount of Pay Assigned to funder should be positive number', $errorMessage);
            }
          }
          else  {
            CRM_Contact_Import_Parser_Contact::addToErrorMsg('funder absolute amount is not set', $errorMessage);
          }
          $params['hrjc_role_percent_pay_funder'] = 0;
        }
        elseif ($params['hrjc_funder_val_type'] === 1 && !$funder_error)  {
          if (!empty($params['hrjc_role_amount_pay_funder']) && !empty($params['hrjc_role_percent_pay_funder']))  {
            CRM_Contact_Import_Parser_Contact::addToErrorMsg('funder absolute amount should be removed', $errorMessage);
          }
          elseif (!empty($params['hrjc_role_percent_pay_funder']))  {
            if ( !filter_var($params['hrjc_role_percent_pay_funder'], FILTER_VALIDATE_FLOAT) || $params['hrjc_role_percent_pay_funder'] < 0)  {
              CRM_Contact_Import_Parser_Contact::addToErrorMsg('Percent of Pay Assigned to funder should be positive number', $errorMessage);
            }
          }
          else  {
            CRM_Contact_Import_Parser_Contact::addToErrorMsg('funder percent amount is not set', $errorMessage);
          }
          $params['hrjc_role_amount_pay_funder'] = 0;
        }
      }

      if ($funder_error)  {
        unset($params['funder']);
        unset($params['hrjc_funder_val_type']);
        unset($params['hrjc_role_amount_pay_funder']);
        unset($params['hrjc_role_percent_pay_funder']);
      }

      // use contract dates as fallback if job role dates not set
      $contractStartDate = CRM_Utils_Date::formatDate($contractDetails->period_start_date, 1);
      $contractEndDate = CRM_Utils_Date::formatDate($contractDetails->period_end_date, 1);

      if (!empty($params['hrjc_role_start_date'])) {
        $roleStartDate = CRM_Utils_Date::formatDate($params['hrjc_role_start_date'], $dateType);
        if ($roleStartDate) {
          $params['hrjc_role_start_date'] = $roleStartDate;
          if ($roleStartDate < $contractStartDate)  {
            CRM_Contact_Import_Parser_Contact::addToErrorMsg('job role start date should not be before contract start date', $errorMessage);
          }
          if (!empty($contractEndDate) && ($roleStartDate > $contractEndDate) )  {
            CRM_Contact_Import_Parser_Contact::addToErrorMsg('job role start date should not be after contract end date', $errorMessage);
          }
        }
        else {
          CRM_Contact_Import_Parser_Contact::addToErrorMsg('Start Date is not a valid date', $errorMessage);
        }
      } else  {
        $params['hrjc_role_start_date'] = $contractStartDate;
      }

      if (!empty($params['hrjc_role_end_date'])) {
        $roleEndDate = CRM_Utils_Date::formatDate($params['hrjc_role_end_date'], $dateType);
        if ($roleEndDate) {
          $params['hrjc_role_end_date'] = $roleEndDate;
          if ($roleEndDate <=  $params['hrjc_role_start_date'])  {
            CRM_Contact_Import_Parser_Contact::addToErrorMsg('job role end date should not be before or equal job role start date', $errorMessage);
          }
          if (!empty($contractEndDate) && $roleEndDate > $contractEndDate)  {
            CRM_Contact_Import_Parser_Contact::addToErrorMsg('job role end date should not be after contract end date', $errorMessage);
          }
        }
        else {
          CRM_Contact_Import_Parser_Contact::addToErrorMsg('End Date is not a valid date', $errorMessage);
        }
      }
      else  {
        $params['hrjc_role_end_date'] = $contractEndDate;
      }

      // END checking imported values if they are valid

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
   * Function of undocumented functionality required by the interface.
   */
   protected function fini() {}


  /**
   * Get the (Option Value) database value given its
   * label or the value itself
   *
   * @param String| $option
   * @param String $value
   * @return Integer|String|NULL
   * @access private
   */
  private function getOptionKey($option, $value)  {
    if (CRM_Utils_Array::value($value, $this->_optionsList[$option])){
      return $value;
    }
    return CRM_Utils_Array::key(strtolower($value), $this->_optionsList[$option]);
  }

}

