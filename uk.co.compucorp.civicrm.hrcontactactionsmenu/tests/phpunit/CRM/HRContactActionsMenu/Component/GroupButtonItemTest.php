<?php

use CRM_HRContactActionsMenu_Component_GroupButtonItem as ActionsGroupButtonItem;

/**
 * Class CRM_HRContactActionsMenu_Component_GroupButtonItemTest
 *
 * @group headless
 */
class CRM_HRContactActionsMenu_Component_GroupButtonItemTest extends BaseHeadlessTest {

  public function testRender() {
    $buttonLabel = 'myButton';
    $buttonClass = 'btn';
    $buttonUrl = 'www.test.com';
    $buttonIcon = 'fa-book';

    $button = new ActionsGroupButtonItem($buttonLabel);
    $button->setClass($buttonClass)
           ->setIcon($buttonIcon)
           ->setUrl($buttonUrl);

    $expectedResult = sprintf(
      '<a href="%s" class="%s"><i class="%s">%s</i></a>',
      $buttonUrl,
      $buttonClass,
      $buttonIcon,
      $buttonLabel
    );

    $this->assertEquals($expectedResult, $button->render());
  }
}
