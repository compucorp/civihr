<?php

use CRM_Contact_BAO_Contact as Contact;
use CRM_Contact_BAO_Relationship as Relationship;
use CRM_Contact_BAO_RelationshipType as RelationshipType;
use CRM_Hrjobcontract_BAO_HRJobContract as HRJobContract;
use CRM_Hrjobcontract_BAO_HRJobDetails as HRJobDetails;
use CRM_Hrjobcontract_BAO_HRJobContractRevision as HRJobContractRevision;
use CRM_HRLeaveAndAbsences_BAO_AbsencePeriod as AbsencePeriod;
use CRM_HRLeaveAndAbsences_API_Query_Select as SelectQuery;

class CRM_HRLeaveAndAbsences_API_Query_LeaveBalancesSelect {

  use CRM_HRLeaveAndAbsences_Service_SettingsManagerTrait;

  /**
   * @var CRM_HRLeaveAndAbsences_BAO_AbsencePeriod
   */
  private $absencePeriod;

  public function __construct($params) {
    $this->params = $params;
    $this->buildCustomQuery();
  }

  private function buildCustomQuery() {
    $customQuery = CRM_Utils_SQL_Select::from(Contact::getTableName() . ' as a');

    $this->addJoins($customQuery);
    $this->addWhere($customQuery);

    $this->query = new SelectQuery('Contact', $this->params);
    $this->query->merge($customQuery);
  }

  private function addJoins(CRM_Utils_SQL_Select $query) {
    $absencePeriod = $this->getAbsencePeriod();

    $joins = [
      'INNER JOIN ' . HRJobContract::getTableName() . ' jc ON a.id = jc.contact_id',
      'INNER JOIN ' . HRJobContractRevision::getTableName() . ' jcr ON jcr.id = (SELECT id
                    FROM ' . HRJobContractRevision::getTableName() . " jcr2
                    WHERE
                    jcr2.jobcontract_id = jc.id AND (
                      jcr2.effective_date <= '{$absencePeriod->start_date}'
                      OR
                      (
                        jcr2.effective_date >= '{$absencePeriod->start_date}' AND
                        jcr2.effective_date <= '{$absencePeriod->end_date}' 
                      )
                    )
                    ORDER BY jcr2.effective_date DESC, jcr2.id DESC
                    LIMIT 1)",
      'INNER JOIN ' . HRJobDetails::getTableName() . ' jd ON jd.jobcontract_revision_id = jcr.details_revision_id'
    ];

    if(!empty($this->params['managed_by'])) {
      $joins[] = 'LEFT JOIN ' . Relationship::getTableName() . ' r ON r.contact_id_a = a.id';
      $joins[] = 'LEFT JOIN ' . RelationshipType::getTableName() . ' rt ON rt.id = r.relationship_type_id';
    }

    $query->join(null, $joins);
  }

  private function addWhere($customQuery) {
    $absencePeriod = $this->getAbsencePeriod();

    $conditions = [
      'a.is_deleted = 0',
      'jc.deleted = 0',
      "(
        (jd.period_end_date IS NOT NULL AND jd.period_start_date <= '{$absencePeriod->end_date}' AND jd.period_end_date >= '{$absencePeriod->start_date}')
          OR
        (jd.period_end_date IS NULL AND 
          (
            (jd.period_start_date >= '{$absencePeriod->start_date}' AND jd.period_start_date <= '{$absencePeriod->end_date}')
            OR
            jd.period_start_date <= '{$absencePeriod->end_date}'
          )
        )
      )"
    ];

    if(!empty($this->params['managed_by'])) {
      $managerID = (int)$this->params['managed_by'];

      if(!isset($this->params['unassigned'])) {
        $activeLeaveManagerCondition = $this->hasActiveLeaveManagerCondition();
        $activeLeaveManagerCondition[] = "r.contact_id_b = {$managerID}";
        $conditions = array_merge($conditions, $activeLeaveManagerCondition);
      }
    }


    $customQuery->where($conditions);
  }

  public function run() {
    $results = $this->query->run();

    return $results;
  }


  private function hasActiveLeaveManagerCondition() {
    $today =  '"' . date('Y-m-d') . '"';
    $leaveApproverRelationshipTypes = $this->getLeaveApproverRelationshipsTypesForWhereIn();

    $conditions = [];
    $conditions[] = 'rt.is_active = 1';
    $conditions[] = 'rt.id IN(' . implode(',', $leaveApproverRelationshipTypes) . ')';
    $conditions[] = 'r.is_active = 1';
    $conditions[] = "(r.start_date IS NULL OR r.start_date <= {$today})";
    $conditions[] = "(r.end_date IS NULL OR r.end_date >= {$today})";

    return $conditions;
  }

  private function getAbsencePeriod() {
    if(!$this->absencePeriod) {
      $this->absencePeriod = AbsencePeriod::findById($this->params['period_id']);
    }

    return $this->absencePeriod;
  }

}
