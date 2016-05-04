<?php

require_once 'CiviTest/CiviUnitTestCase.php';

class CRM_HRLeaveAndAbsences_BAO_WorkPatternTest extends CiviUnitTestCase
{
    protected $_tablesToTruncate = [
        'civicrm_hrleaveandabsences_work_pattern',
    ];

    public function testWeightShouldAlwaysBeMaxWeightPlus1OnCreate()
    {
        $firstEntity = $this->createBasicWorkPattern();
        $this->assertNotEmpty($firstEntity->weight);

        $secondEntity = $this->createBasicWorkPattern();
        $this->assertNotEmpty($secondEntity->weight);
        $this->assertEquals($firstEntity->weight + 1, $secondEntity->weight);
    }

    /**
     * @expectedException PEAR_Exception
     * @expectedExceptionMessage DB Error: already exists
     */
    public function testWorkPatternLabelsShouldBeUnique() {
        $this->createBasicWorkPattern(['label' => 'Pattern 1']);
        $this->createBasicWorkPattern(['label' => 'Pattern 1']);
    }

    public function testThereShouldBeOnlyOneDefaultTypeOnCreate() {
        $basicEntity = $this->createBasicWorkPattern(['is_default' => true]);
        $entity1 = $this->findWorkPatternByID($basicEntity->id);
        $this->assertEquals(1, $entity1->is_default);

        $basicEntity = $this->createBasicWorkPattern(['is_default' => true]);
        $entity2 = $this->findWorkPatternByID($basicEntity->id);
        $entity1 = $this->findWorkPatternByID($entity1->id);
        $this->assertEquals(0,  $entity1->is_default);
        $this->assertEquals(1, $entity2->is_default);
    }

    public function testThereShouldBeOnlyOneDefaultTypeOnUpdate() {
        $basicEntity1 = $this->createBasicWorkPattern(['is_default' => false]);
        $basicEntity2 = $this->createBasicWorkPattern(['is_default' => false]);
        $entity1 = $this->findWorkPatternByID($basicEntity1->id);
        $entity2 = $this->findWorkPatternByID($basicEntity2->id);
        $this->assertEquals(0,  $entity1->is_default);
        $this->assertEquals(0,  $entity2->is_default);

        $this->updateBasicWorkPattern($basicEntity1->id, ['is_default' => true]);
        $entity1 = $this->findWorkPatternByID($basicEntity1->id);
        $entity2 = $this->findWorkPatternByID($basicEntity2->id);
        $this->assertEquals(1, $entity1->is_default);
        $this->assertEquals(0,  $entity2->is_default);

        $this->updateBasicWorkPattern($basicEntity2->id, ['is_default' => true]);
        $entity1 = $this->findWorkPatternByID($basicEntity1->id);
        $entity2 = $this->findWorkPatternByID($basicEntity2->id);
        $this->assertEquals(0,  $entity1->is_default);
        $this->assertEquals(1, $entity2->is_default);
    }


    private function createBasicWorkPattern($params = [])
    {
        $basicRequiredFields = ['label' => 'Pattern ' . microtime() ];

        $params = array_merge($basicRequiredFields, $params);
        return CRM_HRLeaveAndAbsences_BAO_WorkPattern::create($params);
    }

    private function updateBasicWorkPattern($id, $params)
    {
        $params['id'] = $id;
        return $this->createBasicWorkPattern($params);
    }

    private function findWorkPatternByID($id)
    {
        $entity = new CRM_HRLeaveAndAbsences_BAO_WorkPattern();
        $entity->id = $id;
        $entity->find(true);

        if($entity->N == 0) {
            return null;
        }

        return $entity;
    }
}
