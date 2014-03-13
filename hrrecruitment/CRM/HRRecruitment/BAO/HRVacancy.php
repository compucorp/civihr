<?php
/*
+--------------------------------------------------------------------+
| CiviHR version 1.3                                                 |
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

class CRM_HRRecruitment_BAO_HRVacancy extends CRM_HRRecruitment_DAO_HRVacancy{

  /**
   * Function to format the Vacancy parameters before saving
   *
   * @return array   Formated array before being used for create/update Vacancy
   */
  public static function formatParams($params) {
    $formattedParams = array();
    $instance = new self();
    $fields = $instance->fields();
    foreach ($fields as $name => $dontCare) {
      if (strpos($name, '_date') !== FALSE) {
        $formattedParams[$name]  = CRM_Utils_Date::processDate($params[$name], $params[$name . '_time']);
      }
      elseif ($name == 'is_template' && !array_key_exists('template_id', $params)) {
        $formattedParams[$name] = 1;
      }
      elseif(isset($params[$name])) {
        $formattedParams[$name] = $params[$name];
      }
    }
    return $formattedParams;
  }
}
