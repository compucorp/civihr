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

class CRM_HRRecruitment_BAO_HRVacancy extends CRM_HRRecruitment_DAO_HRVacancy {

  public static function create(&$params) {
    $vacancy = new self();

    if (!empty($params['id'])) {
      CRM_Core_DAO::executeQuery("DELETE FROM civicrm_hrvacancy_stage WHERE vacancy_id = {$params['id']}");
      CRM_Core_DAO::executeQuery("DELETE FROM civicrm_hrvacancy_permission WHERE vacancy_id = {$params['id']}");
    }

    $vacancyParams = CRM_HRRecruitment_BAO_HRVacancy::formatParams($params);

    $entityName = 'HRVacancy';
    $hook = empty($params['id']) ? 'create' : 'edit';
    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
    if (!empty($params['id'])) {
      $vacancy->find($params['id']);
      $vacancy->created_date = $vacancy->created_date ? CRM_Utils_Date::processDate($vacancy->created_date) : date('YmdHis');
      $vacancy->created_id = $vacancy->created_id ? $vacancy->created_id : CRM_Core_Session::singleton()->get('userID');
    }
    else {
      $vacancyParams['created_date'] = date('YmdHis');
      $vacancyParams['created_id'] = CRM_Core_Session::singleton()->get('userID');
    }

    $vacancy->copyValues($vacancyParams);

    $vacancy->save();

    if (isset($params['stages']) && count($params['stages'])) {
      foreach ($params['stages'] as $key => $stage_id) {
        $dao = new CRM_HRRecruitment_DAO_HRVacancyStage();
        $dao->case_status_id = $stage_id;
        $dao->vacancy_id = $vacancy->id;
        $dao->weight = $key+1;
        $dao->save();
      }
    }

    foreach (array('application_profile', 'evaluation_profile') as $profileName) {
      if (!empty($params[$profileName])) {
        $dao = new CRM_Core_DAO_UFJoin();
        $dao->module = 'Vacancy';
        $dao->entity_table = 'civicrm_hrvacancy';
        $dao->entity_id = $vacancy->id;
        $dao->module_data = $profileName;
        if (!empty($params['id'])) {
          $dao->find(TRUE);
        }
        $dao->uf_group_id = $params[$profileName];
        $dao->save();
      }
    }

    if (!empty($params['permission']) && !empty($params['permission_contact_id'])) {
      foreach ($params['permission'] as $key => $permission) {
        if ($permission && $params['permission_contact_id'][$key]) {
          $dao = new CRM_HRRecruitment_DAO_HRVacancyPermission();
          $dao->contact_id = $params['permission_contact_id'][$key];
          $dao->permission = $permission;
          $dao->vacancy_id = $vacancy->id;
          $dao->save();
        }
      }
    }

    CRM_Utils_Hook::post($hook, $entityName, $vacancy->id, $vacancy);

    return $vacancy;
  }

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
      if (strpos($name, '_date') !== FALSE && strpos($name, 'created_') === FALSE) {
        $time = empty($params[$name . '_time']) ? NULL : $params[$name . '_time'];
        $formattedParams[$name] = CRM_Utils_Date::processDate($params[$name], $time);
      }
      elseif (isset($params[$name])) {
        $formattedParams[$name] = $params[$name];
      }
    }
    return $formattedParams;
  }

  /**
   * This function is to make a copy of a Vacancy
   *
   * @param int $id          the vacancy id to copy
   *        obj     $newVacancy    object of CRM_HRRecruitment_DAO_HRVacancy
   *        boolean $afterCreate call to copy after the create function
   * @return void
   * @access public
   */
  static function copy($id, $newVacancy = NULL, $afterCreate = FALSE) {
    $vacancyValues = array();
    $vacancyParams = array('id' => $id);
    $returnProperties = array('position', 'salary', 'status_id', 'is_template');
    CRM_Core_DAO::commonRetrieve('CRM_HRRecruitment_DAO_HRVacancy', $vacancyParams, $vacancyValues, $returnProperties);
    $fieldsFix = ($afterCreate) ? array() : array('prefix' => array('position' => ts('Copy of') . ' '));
    if ($newVacancy && is_a($newVacancy, 'CRM_HRRecruitment_DAO_HRVacancy')) {
      $copyVacancy = $newVacancy;
    }

    if (!isset($copyVacancy)) {
      $copyVacancy = & CRM_Core_DAO::copyGeneric('CRM_HRRecruitment_DAO_HRVacancy',
        array('id' => $id), '',
        $fieldsFix
      );
    }

    CRM_Core_DAO::copyGeneric('CRM_HRRecruitment_DAO_HRVacancyStage',
      array(
        'vacancy_id' => $id,
      ),
      array('vacancy_id' => $copyVacancy->id)
    );

    CRM_Core_DAO::copyGeneric('CRM_Core_DAO_UFJoin',
      array(
        'entity_id' => $id,
        'entity_table' => 'civicrm_hrvacancy',
      ),
      array('entity_id' => $copyVacancy->id)
    );

    CRM_Core_DAO::copyGeneric('CRM_HRRecruitment_DAO_HRVacancyPermission',
      array(
        'vacancy_id' => $id,
      ),
      array('vacancy_id' => $copyVacancy->id)
    );
    CRM_Utils_System::flushCache();

    return $copyVacancy;
  }

  static function getVacanciesByStatus() {
    $statuses = CRM_Core_OptionGroup::values('vacancy_status');
    $vacancies = $statusesCount = array();
    //initialize $statusesCount which hold the number of vacancies of status 'Draft' and 'Open'
    foreach (array('Open', 'Draft') as $statusName) {
      $value = array_search($statusName, $statuses);
      $statusesCount[$value] = 0;

      //Retrieve top four recently modified vacancies of either status 'Draft' or 'Open'
      $result = civicrm_api3('HRVacancy', 'get', array('is_template' => 0, 'status_id' => $value, 'sort' => 'created_date DESC', 'option.limit' => 4));

      foreach ($result['values'] as $id => $vacancy) {
        $isDraft = FALSE;
        if (isset($statusesCount[$vacancy['status_id']])) {
          $statusesCount[$vacancy['status_id']] += 1;
          if ($vacancy['status_id'] == array_search('Draft', $statuses)) {
            $isDraft = TRUE;
          }
          $position = "<a class='hr-vacancy-title' href='" . CRM_Utils_System::url('civicrm/case/pipeline', "reset=1&vid={$id}") . "'>{$vacancy['position']}</a>";
          //show the pencil icon to edit vacancy only if the user has appropriate permission
          if (CRM_HRRecruitment_BAO_HRVacancyPermission::checkVacancyPermission($id, array('administer CiviCRM', 'administer Vacancy')))  {
            $position .= "<a class='crm-hover-button action-item' title='" . ts('Edit this vacancy') . "' href='" . CRM_Utils_System::url('civicrm/vacancy/add', "reset=1&id={$id}") . "'><span class='icon edit-icon'></span></a>";
          }
          $vacancy['start_date'] = !empty($vacancy['start_date']) ? CRM_Utils_Date::customFormat($vacancy['start_date'], '%b %E, %Y') : NULL;
          $vacancy['end_date'] = !empty($vacancy['end_date']) ? CRM_Utils_Date::customFormat($vacancy['end_date'], '%b %E, %Y') : NULL;
          $vacancyEntry[$vacancy['status_id']]['vacancies'][$id] = array(
            'date' => CRM_Utils_Date::customFormat($vacancy['start_date'], '%b %E, %Y') . ' - ' . CRM_Utils_Date::customFormat($vacancy['end_date'], '%b %E, %Y'),
            'position' => $position,
            'location' => CRM_Utils_Array::value('location', $vacancy),
            'date' => "{$vacancy['start_date']} - {$vacancy['end_date']}",
          );

          //assign stages by weight
          $stages = CRM_HRRecruitment_BAO_HRVacancyStage::caseStage($id);
          foreach ($stages as $stage) {
            $vacancyEntry[$vacancy['status_id']]['vacancies'][$id]['stages'][$stage['weight']] = array(
              'stage_id' => $stage['id'],
              'title' => $stage['title'],
              'count' => $stage['count'],
            );
          }
          ksort($vacancyEntry[$vacancy['status_id']]['vacancies'][$id]['stages']);

          $vacancies[$vacancy['status_id']] = array('title' => $statuses[$vacancy['status_id']]) + $vacancyEntry;
        }
      }
    }

    //append $statusCount result to vacancy's position as title
    foreach ($statusesCount as $status => $count) {
      if ($count) {
        $vacancies[$status]['title'] .= " ({$count})";
      }
      else {
        $vacancies += array($status => array('title' => "{$statuses[$status]} ({$count})"));
      }
    }
    return $vacancies;
  }

  static function recentApplicationActivities($limit = 10) {
    $recentActivities = array();

    $customTableName = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_CustomGroup', 'application_case', 'table_name', 'name');

    //Retrieve top $limit(as total activity count) recent activities
    $query = CRM_Case_BAO_Case::getCaseActivityQuery($type = 'any', NULL, $condition = "AND c_type.name = 'Application' AND t_act.desired_date <= CURRENT_TIMESTAMP ");
    $query .= "LIMIT 0, {$limit}";
    $query = str_replace('ORDER BY case_activity_date ASC', 'ORDER BY case_activity_date DESC', $query);
    $dao = CRM_Core_DAO::executeQuery($query);

    while ($dao->fetch()) {
      $query = "SELECT vacancy_id FROM {$customTableName} WHERE entity_id = {$dao->case_id}";
      $ctDAO = CRM_Core_DAO::executeQuery($query);
      $ctDAO->fetch();

      $vacancyDAO = new self();
      $vacancyDAO->id = $ctDAO->vacancy_id;
      $vacancyDAO->find(TRUE);

      //Applicant contact link
      $applicant = "<a href='" . CRM_Utils_System::url('civicrm/contact/view', "reset=1&cid={$dao->contact_id}") . "'>{$dao->sort_name}</a>";

      //Position link
      $position = "<a href='" . CRM_Utils_System::url('civicrm/case/pipeline', "reset=1&vid={$vacancyDAO->id}") . "'>{$vacancyDAO->position}</a>";

      //Case Activity Source link
      $sourceID = civicrm_api3('OptionValue', 'getvalue', array(
        'option_group_id' => 'activity_contacts',
        'name' => 'Activity Source',
        'return' => 'value'
      ));
      $sourceContact = CRM_Activity_BAO_ActivityContact::getNames($dao->case_activity_id, $sourceID);
      $sourceContactID = key($sourceContact);
      $source = "<a href='" . CRM_Utils_System::url('civicrm/contact/view', "reset=1&cid={$sourceContactID}") . "'>{$sourceContact[$sourceContactID]}</a>";

      switch ($dao->case_activity_type_name) {
        case 'Open Case':
          $recentActivities[] = array(
            'activity' => "{$applicant} applied for {$position}",
            'time' => $dao->case_activity_date
          );
          break;
        case 'Comment':
          $recentActivities[] = array(
            'activity' => "{$source} commented on {$position}",
            'time' => $dao->case_activity_date
          );
          break;
        case 'Phone Call':
        case 'Meeting':
        case 'Follow up':
          $recentActivities[] = array(
            'activity' => "{$source} had a {$dao->case_activity_type_name} with {$applicant} (vis-a-vis {$position})",
            'time' => $dao->case_activity_date
          );
          break;
        case 'Email':
          $recentActivities[] = array(
            'activity' => "{$source} sent email to {$applicant}",
            'time' => $dao->case_activity_date
          );
          break;
        case 'Change Case Status':
          $subject = CRM_Core_DAO::getFieldValue('CRM_Activity_DAO_Activity', $dao->case_activity_id, 'subject');
          $subject = str_replace('Assignment status changed', '', $subject);
          $recentActivities[] = array(
            'activity' => "{$source} changed the status of {$position} {$subject}",
            'time' => $dao->case_activity_date
          );
          break;
        case 'Evaluation':
          $subject = CRM_Core_DAO::getFieldValue('CRM_Activity_DAO_Activity', $dao->case_activity_id, 'subject');
          $recentActivities[] = array(
            'activity' => "{$source} evaluated {$applicant} for {$position}",
            'time' => $dao->case_activity_date
          );
          break;
        default:
          $subject = CRM_Core_DAO::getFieldValue('CRM_Activity_DAO_Activity', $dao->case_activity_id, 'subject');
          $recentActivities[] = array(
            'activity' => "{$position}: {$dao->case_activity_type_name} for {$applicant}",
            'time' => $dao->case_activity_date
          );
          break;
      }
    }

    return $recentActivities;
  }

  /**
   * Given the list of params in the params array, fetch the object
   * and store the values in the values array
   *
   * @param array $params input parameters to find object
   * @param array $values output values of the object
   *
   * @return CRM_HRRecruitment_DAO_HRVacancy|null the found object or null
   * @access public
   * @static
   */

  static function retrieve(&$params, &$defaults) {
    $vacancy = new self();
    $vacancy->copyValues($params);
    if ($vacancy->find(TRUE)) {
      CRM_Core_DAO::storeValues($vacancy, $defaults);

      $stage = new CRM_HRRecruitment_DAO_HRVacancyStage();
      $stage->vacancy_id = $vacancy->id;
      $stage->find();
      while ($stage->fetch()) {
        $defaults['stages'][$stage->weight] = $stage->case_status_id;
      }

      $permission = new CRM_HRRecruitment_DAO_HRVacancyPermission();
      $permission->vacancy_id = $vacancy->id;
      $permission->find();
      $count = 1;
      while ($permission->fetch()) {
        $defaults['permission'][$count] = $permission->permission;
        $defaults['permission_contact_id'][$count] = $permission->contact_id;
        $count++;
      }

      foreach (array('application_profile', 'evaluation_profile') as $profileName) {
        $ufJoin = new CRM_Core_DAO_UFJoin;
        $ufJoin->module = 'Vacancy';
        $ufJoin->entity_id = $vacancy->id;
        $ufJoin->module_data = $profileName;
        $ufJoin->find(TRUE);
        $defaults[$profileName] = $ufJoin->uf_group_id;
      }
    }
  }

  public static function del($vacancyID) {
    $vacancy = new self();
    $vacancy->id = $vacancyID;
    $vacancy->find(TRUE);

    //Delete all Application cases linked to this vacancy
    $customTableName = CRM_Core_DAO::getFieldValue('CRM_Core_DAO_CustomGroup', 'application_case', 'table_name', 'name');
    $query = "SELECT entity_id FROM {$customTableName} WHERE vacancy_id = {$vacancyID}";
    $ctDAO = CRM_Core_DAO::executeQuery($query);
    while ($ctDAO->fetch()) {
      CRM_Case_BAO_Case::deleteCase($ctDAO->entity_id);
    }
    $vacancy->delete();
  }

  /**
   * This function is to get Vacancy ID from Application ID
   *
   * @param int $caseID - Case ID of type Application
   *
   * @return int
   * @access public
   */
  public static function getVacancyIDByCase($caseID) {
    $applCaseID = CRM_Core_PseudoConstant::getKey('CRM_Core_BAO_CustomField', 'custom_group_id', 'application_case');
    $cgID = array('custom_group_id' => $applCaseID);
    CRM_Core_BAO_CustomField::retrieve($cgID, $cfID);

    $params = array(
      "entityID" => $caseID,
      "custom_{$cfID['id']}" => 1,
    );
    $result = CRM_Core_BAO_CustomValueTable::getValues($params);
    $vacancyID = $result["custom_{$cfID['id']}"];
    return $vacancyID;
  }

  public static function getJobPosition($status = 'Open' ) {
    $position = array();
    $sql = "
SELECT      hrv.id, hrv.position
FROM        civicrm_hrvacancy hrv
INNER JOIN  civicrm_option_group og ON og.name='vacancy_status'
INNER JOIN  civicrm_option_value ov ON ( hrv.status_id=ov.value AND ov.option_group_id=og.id )
WHERE       ov.name = '{$status}'
    ";

    $dao = CRM_Core_DAO::executeQuery($sql);
    while ($dao->fetch()) {
      $position[$dao->id] = $dao->position;
    }
    return $position;
  }



}
