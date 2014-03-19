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
class CRM_HRRecruitment_BAO_HRVacancyStage extends CRM_HRRecruitment_DAO_HRVacancyStage {

 /**
   * Function to fetch casestatuses
   *
   * @param int     $id    the vacancy id
   * @return array - array of related  casestatus based on job position.
   */
  public static function caseStage($id) {
    $result = civicrm_api3('HRVacancyStage', 'get', array('vacancy_id'=> $id ));
    $case_status = CRM_Core_OptionGroup::values('case_status', FALSE, FALSE, FALSE, " AND grouping = 'Vacancy'");
    foreach ($result['values'] as $id => $status) {
      $caseStatus[] = $case_status[$status['case_status_id']]; 
    }
    return $caseStatus;
  }  
}