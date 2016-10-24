<?php

class CRM_HRCore_Test_Fabricator_Case {

  private static $defaultCaseParams = [
    'subject' => 'test test',
    'case_type_id' => 'test case type',
    'contact_id' => 1,
    'creator_id' => 1,
  ];

  private static $defaultCaseTypeParams = [
    'title' => 'test case type',
    'name' => 'test case type',
    'is_active' => 1,
    'sequential'   => 1,
    'weight' => 100,
    'definition' => [
      'activityTypes' => [
        ['name' => 'Test'],
      ],
      'activitySets' => [
        [
          'name' => 'set1',
          'label' => 'Label 1',
          'timeline' => 1,
          'activityTypes' => [
            ['name' => 'Open Case', 'status' => 'Completed'],
          ],
        ],
      ],
    ],
  ];

  public static function fabricateCase($params = []) {
    $params = array_merge(self::$defaultCaseParams, $params);

    $result = civicrm_api3(
      'Case',
      'create',
      $params
    );

    return array_shift($result['values']);
  }

  public static function fabricateCaseType($params = []) {
    $params = array_merge(self::$defaultCaseTypeParams, $params);

    $result = civicrm_api3(
      'CaseType',
      'create',
      $params
    );

    return array_shift($result['values']);
  }
}
