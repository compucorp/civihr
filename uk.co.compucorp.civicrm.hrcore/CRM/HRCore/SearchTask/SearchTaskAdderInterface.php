<?php

interface CRM_HRCore_SearchTask_SearchTaskAdderInterface {
  /**
   * Add items to the tasks array in the form:
   * [
   *   'title' => <the title to be displayed in the dropdown>,
   *   'class' => <the class name of the form for the action>
   * ]
   *
   * @param $tasks
   *
   * @return void
   */
  public static function add(&$tasks);

  /**
   * Determines whether or not the tasks should be added.
   *
   * @param $objectName
   *
   * @return bool
   */
  public static function shouldAdd($objectName);
}
