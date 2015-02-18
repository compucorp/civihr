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
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2014
 *
 */

/**
 * This class contains all Indentification related functions
 */
class CRM_HRIdent_Page_HRIdent {
  /**
   *  Function to get email address of a contact
   */
  static function getGovernmentID() {
    $contactID = CRM_Utils_Request::retrieve('cid', 'Integer');
    $govVal = self::retreiveContactFieldValue($contactID);
    $ov = CRM_Core_OptionGroup::values('type_20130502144049');
    $hideGovID = civicrm_api3('CustomField', 'getvalue', array('custom_group_id' => 'Identify', 'name' => 'is_government', 'return' => 'id'));
    $subTypes['govTypeNumber'] = CRM_Utils_Array::value('typeNumber', $govVal);
    $subTypes['govType'] = $ov[CRM_Utils_Array::value('type', $govVal)];
    echo json_encode($subTypes);
    CRM_Utils_System::civiExit();
  }

  /**
   *Retrieve Name, Type and Id of record contain government value from customvalue table
   */
  static function retreiveContactFieldValue($contactID) {
    $govInfo = array();
    $govFieldId = self::retreiveContactFieldId('Identify');
    if (!empty($govFieldId) && $contactID) {
      $govValues = CRM_Core_BAO_CustomValueTable::getEntityValues($contactID, NULL, $govFieldId, TRUE);
      foreach ($govValues as $key => $val) {
        if ($val[$govFieldId['is_government']] == 1) {
          $govInfo['type'] = $val[$govFieldId['Type']];
          $govInfo['typeNumber'] = $val[$govFieldId['Number']];
          $govInfo['key'] = $val[$govFieldId['is_government']];
          $govInfo['id'] = ":{$key}";
          break;
        }
      }
    }
    return $govInfo;
  }

  /**
   *Return associated array name/id pair of custom field
   */
  static function retreiveContactFieldId($customGroupID) {
    $param =  array(
      'custom_group_id' => $customGroupID,
      'return' => "name",
    );
    $custReseult = civicrm_api3('CustomField', 'get', $param);
    foreach ($custReseult['values'] as $k => $val) {
      $govFieldId[$val['name']] = $val['id'];
    }
    return $govFieldId;
  }
}
