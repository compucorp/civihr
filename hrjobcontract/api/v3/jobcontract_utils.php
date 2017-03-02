<?php

/**
 * File for CiviCRM APIv3 Job Contract utilitity functions
 *
 */

/**
 * Returns entity name by given BAO / DAO class name.
 * 
 * @param String $baoName
 * @return String
 */
function _civicrm_get_entity_name($baoName)
{
    $parts = explode('_', $baoName);
    return end($parts);
}

function _civicrm_get_table_name($className)
{
    if ($className === 'HRJobContract')
    {
        return 'contract';
    }
    preg_match_all('/((?:^|[A-Z])[a-z]+)/', $className, $matches);
    if (!empty($matches[0]))
    {
        return strtolower(end($matches[0]));
    }
    return null;
}

/**
 * Sets $params array to point at valid revision of given $params['jobcontract_id']
 * Job Contract.
 * 
 * @param array $params
 * @param String $table
 */
function _civicrm_hrjobcontract_api3_set_current_revision(array &$params, $table)
{
    if (!empty($params['jobcontract_id'])) {
        $revisionId = _civicrm_hrjobcontract_api3_get_current_revision_id($params, $table);
        if ($revisionId) {
            $params['jobcontract_revision_id'] = (int)$revisionId['values'];
        }
    }
    if (empty($params['jobcontract_revision_id']))
    {
        $params['jobcontract_revision_id'] = 0;
    }
}

/**
 * Creates new revision for given Job Contract Id.
 * If there is no previous revision the function creates new blank revision.
 * Otherwise the function creates new revision with previous entity values.
 * 
 * @param int $jobContractId
 */
function _civicrm_hrjobcontract_api3_create_revision($jobContractId)
{
    // Setting status of current revision: // TODO: status
    /*$pastRevisions = civicrm_api3('HRJobContractRevision', 'get', array(
      'sequential' => 1,
      'jobcontract_id' => $jobContractId,
      'status' => 1,
    ));
    foreach ($pastRevisions['values'] as $pastRevision)
    {
        civicrm_api3('HRJobContractRevision', 'create', array(
            'id' => $pastRevision['id'],
            'status' => 0,
        ));
    }*/
    
    //$currentRevision = _civicrm_hrjobcontract_api3_get_latest_revision((int)$jobContractId);
    $currentRevision = _civicrm_hrjobcontract_api3_get_current_revision(array('jobcontract_id' => (int)$jobContractId));
    if (empty($currentRevision))
    {
        $currentRevision = array('values' => array('jobcontract_id' => $jobContractId));
    }
    else
    {
        unset($currentRevision['values']['id']);
    }
    //$currentRevision['values']['status'] = 1; // TODO: status
    $result = civicrm_api3('HRJobContractRevision', 'create', $currentRevision['values']);
    
    return $result;
}

/**
 * Returns current revision. If current revision is for a past contract, and its
 * terms are changed by future revisions, it will return the latest revision.
 * 
 * @param array $params
 *   Parameter array passed by API call
 * 
 * @return mixed
 *   Returns either:
 *   - Array with contents of revision if one is found
 *   - Null on failure
 */
function _civicrm_hrjobcontract_api3_get_current_revision($params) {
  $jobContractId = (int)$params['jobcontract_id'];
  $deleted = !empty($params['deleted']) ? (int)$params['deleted'] : 0;

  if ($jobContractId) {
    $revision = civicrm_api3('HRJobContractRevision', 'get', [
      'sequential' => 1,
      'jobcontract_id' => $jobContractId,
      'deleted' => $deleted,
      'effective_date' => ['<=' => date('Y-m-d')],
      'options' => ['sort' => 'effective_date DESC, id DESC', 'limit' => 1],
      'return' => [
        'id', 'details_revision_id.id', 'jobcontract_id', 'editor_uid',
        'created_date', 'effective_date', 'effective_end_date', 'modified_date',
        'details_revision_id', 'health_revision_id', 'hour_revision_id',
        'leave_revision_id', 'pay_revision_id', 'pension_revision_id', 
        'role_revision_id', 'deleted', 'editor_name', 
        'details_revision_id.period_end_date'
      ],
    ]);
    $contractEnd = $revision['values'][0]['details_revision_id.period_end_date'];
    $revisionEndDate = $revision['values'][0]['effective_end_date'];

    if (
      empty($revision['values']) 
      || (!empty($revisionEndDate) && !empty($contractEnd) && strtotime($contractEnd) <= time())
    ) {
      $revision = civicrm_api3('HRJobContractRevision', 'get', array(
        'sequential' => 1,
        'jobcontract_id' => $jobContractId,
        'deleted' => $deleted,
        'options' => array('sort' => 'effective_date DESC, id DESC', 'limit' => 1),
      ));
    }

    if (!empty($revision)) {
      $row = array_shift($revision['values']);
      if (!empty($row)) {
          return civicrm_api3_create_success($row);
      }
    }
  }
  return null;
}

function _civicrm_hrjobcontract_api3_get_latest_revision($params)
{
    $jobContractId = (int)$params['jobcontract_id'];
    if ($jobContractId)
    {
        $deleted = 0;
        if (!empty($params['deleted']))
        {
            $deleted = (int)$params['deleted'];
        }
        $revision = civicrm_api3('HRJobContractRevision', 'get', array(
           'sequential' => 1,
           'jobcontract_id' => $jobContractId,
           'options' => array('sort' => 'id DESC', 'limit' => 1),
        ));

        if (!empty($revision)) {
            $row = array_shift($revision['values']);
            if (!empty($row)) {
                return civicrm_api3_create_success($row);
            }
        }
    }
    return null;
}

function _civicrm_hrjobcontract_api3_get_current_revision_id($params, $table)
{
    $revision = _civicrm_hrjobcontract_api3_get_current_revision($params);
    if (!empty($revision['values'][$table . '_revision_id'])) {
        return civicrm_api3_create_success((int)$revision['values'][$table . '_revision_id']);
    }
    return null;
}

/**
 * Function to do a 'custom' api get
 *
 * @param string $bao_name name of BAO
 * @param array $params params from api
 * @param bool $returnAsSuccess return in api success format
 * @param string $entity
 *
 * @return array
 */
function _civicrm_hrjobcontract_api3_custom_get($bao_name, &$params, $returnAsSuccess = TRUE, $entity = "")
{
    $bao = new $bao_name();
    $callbacks = array();
    
    $fields = $bao::fields();
    foreach ($fields as $field)
    {
        if (!empty($field['callback']))
        {
            $callback = $field['callback'];
            if (CRM_Utils_System::validCallback($callback))
            {
                list($className, $fnName) = explode('::', $callback);
                if (method_exists($className, $fnName))
                {
                    $callbacks[$field['name']] = array(
                        'className' => $className,
                        'fnName' => $fnName,
                    );
                }
            }
        }
    }

    _civicrm_api3_dao_set_filter($bao, $params, TRUE, $entity);
    if ($returnAsSuccess)
    {
        $daoToArray = _civicrm_api3_dao_to_array($bao, $params, FALSE, $entity);
        if (!empty($callbacks))
        {
            foreach ($daoToArray as $entryKey => $entryValue)
            {
                foreach ($callbacks as $callbackKey => $callbackValue)
                {
                  $daoToArray[$entryKey][$callbackKey] = NULL;
                  if (array_key_exists($callbackKey, $entryValue))
                   {
                    $daoToArray[$entryKey][$callbackKey] = call_user_func(array($callbackValue['className'], $callbackValue['fnName']), $entryValue[$callbackKey]);
                   }
                }
            }
        }
        return civicrm_api3_create_success($daoToArray, $params, $entity, 'get');
    }
    else {
      return _civicrm_api3_dao_to_array($bao, $params, FALSE, $entity, 'get');
    }
}

/**
 * HRJobContract implementation of the "replace" action.
 *
 * Replace the old set of entities (matching some given keys) with a new set of
 * entities (matching the same keys).
 *
 * Note: This will verify that 'values' is present, but it does not directly verify
 * any other parameters.
 *
 * @param string $entity entity name
 * @param array $params params from civicrm_api, including:
 *   - 'values': an array of records to save
 *   - all other items: keys which identify new/pre-existing records
 * @return array|int
 */
function _civicrm_hrjobcontract_api3_replace($entity, $params, $forceRevisionId = null) {

  $transaction = new CRM_Core_Transaction();
  try {
    if (!is_array($params['values'])) {
      throw new Exception("Mandatory key(s) missing from params array: values");
    }

    // Extract the keys -- somewhat scary, don't think too hard about it
    $baseParams = _civicrm_api3_generic_replace_base_params($params);

    // Lookup pre-existing records
    $preexisting = civicrm_api($entity, 'get', $baseParams, $params);
    if (civicrm_error($preexisting)) {
      $transaction->rollback();
      return $preexisting;
    }

    // Save the new/updated records
    $jobcontractRevisionId = null;
    $creates = array();
    foreach ($params['values'] as $replacement) {
      if (empty($replacement['id']) && empty($replacement['jobcontract_revision_id']))
      {
        $replacement['jobcontract_revision_id'] = $jobcontractRevisionId;
      }
      if ($forceRevisionId) {
        $replacement['jobcontract_revision_id'] = $forceRevisionId;
      }
      // Sugar: Don't force clients to duplicate the 'key' data
      $replacement = array_merge($baseParams, $replacement);
      //$action      = (isset($replacement['id']) || isset($replacement[$entity . '_id'])) ? 'update' : 'create';
      $action = 'create';
      $create      = civicrm_api($entity, $action, $replacement);
      if (civicrm_error($create)) {
        $transaction->rollback();
        return $create;
      }
      foreach ($create['values'] as $entity_id => $entity_value) {
        $creates[$entity_id] = $entity_value;
      }
      $entityData = CRM_Utils_Array::first($create['values']);
      $jobcontractRevisionId = $entityData['jobcontract_revision_id'];
    }

    // Remove stale records
    $staleIDs = array_diff(
      array_keys($preexisting['values']),
      array_keys($creates)
    );
    foreach ($staleIDs as $staleID) {
      $delete = civicrm_api($entity, 'delete', array(
          'version' => $params['version'],
          'id' => $staleID,
        ));
      if (civicrm_error($delete)) {
        $transaction->rollback();
        return $delete;
      }
    }

    return civicrm_api3_create_success($creates, $params);
  }
  catch(PEAR_Exception $e) {
    $transaction->rollback();
    return civicrm_api3_create_error($e->getMessage());
  }
  catch(Exception $e) {
    $transaction->rollback();
    return civicrm_api3_create_error($e->getMessage());
  }
}

/**
 * HRJobContract implementation of the "delete" contract action.
 *
 * Deletes whole contract with its all revisions and entities.
 *
 * @param string $entity entity name
 * @param array $params params from civicrm_api, including 'jobcontract_id'
 * @return array|int
 */
function _civicrm_hrjobcontract_api3_deletecontract($params) {
  $transaction = new CRM_Core_Transaction();
  try {
    if (empty($params['id'])) {
      throw new Exception("Cannot delete Job Contract: please specify id value.");
    }

    $contract = civicrm_api('HRJobContract', 'get', $params);
    if (empty($contract['id']))
    {
        throw new Exception("Cannot find Job Contract with given id (" . $params['id'] . ").");
    }
    
    $revisions = civicrm_api('HRJobContractRevision', 'get', array('sequential' => 1, 'options' => array('limit' => 0), 'version' => 3, 'jobcontract_id' => $params['id']));
    foreach ($revisions['values'] as $revision)
    {
        civicrm_api3('HRJobContractRevision', 'create', array('version' => 3, 'id' => $revision['id'], 'deleted' => 1));
    }
    civicrm_api3('HRJobContract', 'create', array('version' => 3, 'id' => $contract['id'], 'deleted' => 1));
    CRM_Hrjobcontract_JobContractDates::removeDates($contract['id']);
    
    return 1;
  }
  catch(PEAR_Exception $e) {
    $transaction->rollback();
    return civicrm_api3_create_error($e->getMessage());
  }
  catch(Exception $e) {
    $transaction->rollback();
    return civicrm_api3_create_error($e->getMessage());
  }
}

/**
 * Retrieves a CiviCRM contact by Drupal user ID.
 */
function civicrm_custom_user_profile_get_contact($uid) {
  $contacts = &drupal_static(__FUNCTION__);
  if (isset($contacts[$uid])) {
    return $contacts[$uid];
  }
  if (!isset($contacts)) {
    $contacts = array();
  }
  $contacts[$uid] = FALSE;
  civicrm_initialize();
  require_once 'api/api.php';
  $res = civicrm_api('uf_match', 'get', array('uf_id' => $uid, 'version' => 3));
  if ($res['is_error'] || empty($res['id']) || empty($res['values'][$res['id']])) {
    return FALSE;
  }
  $id = $res['values'][$res['id']]['contact_id'];
  $res = civicrm_api('contact', 'get', array('contact_id' => $id, 'version' => 3));
  if ($res['is_error']) {
    return FALSE;
  }
  $contacts[$uid] = $res['values'][$res['id']];
  return $contacts[$uid];
}
