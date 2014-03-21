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
   * @return array - array of related  casestatus based on job position
   * in key(as id) => value array (contains label, weight and count) format.
   */
  public static function caseStage($id) {
    $result = civicrm_api3('HRVacancyStage', 'get', array('vacancy_id'=> $id ));
    $case_status = CRM_Core_OptionGroup::values('case_status', FALSE, FALSE, FALSE, " AND grouping = 'Vacancy'");

    $customTableName = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_CustomGroup', 'application_case', 'table_name', 'name');

    //array to store contact count(as value) against each case status/vacancy stage(as key)
    $stagesCount = array();

    $sql = "SELECT COUNT(ccc.contact_id) as count, cc.status_id as status
FROM {$customTableName} cg
LEFT JOIN civicrm_case cc ON cc.id = cg.entity_id AND cg.vacancy_id = {$id}
LEFT JOIN civicrm_case_contact ccc ON ccc.case_id = cc.id
GROUP BY cc.status_id
";
    $dao = CRM_Core_DAO::executeQuery($sql);
    while($dao->fetch()) {
      $stagesCount[$dao->status] = $dao->count;
    }

    foreach ($result['values'] as $id => $status) {
      $caseStatus[$id] = array(
        'label' => $case_status[$status['case_status_id']],
        'weight' => $status['weight'],
      );
      if (!empty($stagesCount[$status['case_status_id']])) {
        $caseStatus[$id]['count'] = $stagesCount[$status['case_status_id']];
      }
      else {
        $caseStatus[$id]['count'] = 0;
      }
    }
    return $caseStatus;
  }
}
