<?php

use CRM_HRContactActionsMenu_Component_GroupButtonItem as ActionsGroupButtonItem;

/**
 * Class CRM_HRContactActionsMenu_Component_GroupButtonItemTest
 *
 * @group headless
 */
class CRM_HRContactActionsMenu_Component_GroupButtonItemTest extends BaseHeadlessTest {

  public function testRenderWhenAddBottomMarginIsFalse() {
    $params = ['label'=> 'myButton', 'class' => 'btn', 'url' => 'www.test.com', 'icon' => 'fa-book'];
    $button = new ActionsGroupButtonItem($params['label']);
    $this->setUpButton($button, $params);
    $expectedResult = $this->getButtonMarkup($params);
    $this->assertEquals($expectedResult, $button->render());
  }

  public function testRenderWhenAddBottomMarginIsTrue() {
    $params = ['label'=> 'myButton', 'class' => 'btn', 'url' => 'www.test.com', 'icon' => 'fa-book'];
    $button = new ActionsGroupButtonItem($params['label']);
    $addBottomMargin = TRUE;
    $this->setUpButton($button, $params, $addBottomMargin);
    $buttonMarkup = $this->getButtonMarkup($params);
    $expectedResult =  sprintf('      
        <div class="crm_contact_action_menu__bottom_margin">
          %s
        </div>',
      $buttonMarkup
    );

    $this->assertEquals($expectedResult, $button->render());
  }

  private function getButtonMarkup($params) {

    $buttonMarkup = '
      <a href="%s">
        <button class="%s">
          <span><i class="%s"></i></span> %s
        </button>
      </a>';

    $buttonMarkup = sprintf(
      $buttonMarkup,
      $params['url'],
      $params['class'],
      $params['icon'],
      $params['label']
    );

    return $buttonMarkup;
  }

  private function setUpButton($button, $params, $addMargin = false) {
    $button->setClass($params['class'])
      ->setIcon($params['icon'])
      ->setUrl($params['url']);

    if($addMargin) {
      $button->addBottomMargin();
    }
  }
}
