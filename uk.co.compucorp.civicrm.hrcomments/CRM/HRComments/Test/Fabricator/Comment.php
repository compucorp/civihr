<?php

use CRM_HRComments_BAO_Comment as Comment;

class CRM_HRComments_Test_Fabricator_Comment {

  public static function fabricate($params) {
    $params = self::mergeDefaultParams($params);

    return Comment::create($params);
  }

  private static function mergeDefaultParams($params) {
    $defaultParams = [
      'text' => 'This is some random comment',
      'entity_name' => 'DefaultEntity',
    ];
    return array_merge($defaultParams, $params);
  }
}
