<?php

require_once 'CiviTest/CiviUnitTestCase.php';

class CRM_HRLeaveAndAbsences_BAO_WorkWeekTest extends CiviUnitTestCase
{
    protected $_tablesToTruncate = [
        'civicrm_hrleaveandabsences_work_week',
        'civicrm_hrleaveandabsences_work_pattern',
    ];

    protected $workPattern = null;

    public function setUp()
    {
        parent::setUp();
        $this->instantiateWorkPattern();
    }

    public function testNumberShouldAlwaysBeMaxNumberPlus1OnCreate()
    {
        $params = ['pattern_id' => $this->workPattern['id']];

        $entity = $this->createWorkWeek($params);
        $this->assertEquals(1, $entity->number);

        $entity2 = $this->createWorkWeek($params);
        $this->assertEquals(2, $entity2->number);
    }

    public function testCannotSetWeekNumberOnCreate()
    {
        $params = [
            'pattern_id' => $this->workPattern['id'],
            'number' => rand(2, 1000)
        ];
        $entity = $this->createWorkWeek($params);
        $this->assertEquals(1, $entity->number);
    }

    public function testCannotChangeWeekNumberOnUpdate()
    {
        $entity = $this->createWorkWeek(['pattern_id' => $this->workPattern['id']]);
        $this->assertEquals(1, $entity->number);

        $updatedEntity = $this->updateWorkWeek($entity->id, ['number' => rand(100, 200)]);
        $this->assertEquals($entity->number, $updatedEntity->number);
    }

    public function testCannotChangeWorkPatternId()
    {
        $entity = $this->createWorkWeek(['pattern_id' => $this->workPattern['id']]);
        $this->assertEquals($this->workPattern['id'], $entity->pattern_id);

        $updatedEntity = $this->updateWorkWeek($entity->id, ['pattern_id' => rand(100, 200)]);
        $this->assertEquals($this->workPattern['id'], $updatedEntity->pattern_id);
    }

    private function createWorkWeek($params)
    {
        return CRM_HRLeaveAndAbsences_BAO_WorkWeek::create($params);
    }

    private function updateWorkWeek($id, $params)
    {
        $params['id'] = $id;
        CRM_HRLeaveAndAbsences_BAO_WorkWeek::create($params);

        return $this->findWorkWeekByID($id);
    }

    private function instantiateWorkPattern()
    {
        $params = ['label' => 'Pattern ' . microtime()];
        $result = $this->callAPISuccess('WorkPattern', 'create', $params);

        $this->workPattern = reset($result['values']);
    }

    private function findWorkWeekByID($id)
    {
        $entity = new CRM_HRLeaveAndAbsences_BAO_WorkWeek();
        $entity->id = $id;
        $entity->find(true);

        if($entity->N == 0) {
            return null;
        }

        return $entity;
    }
}
