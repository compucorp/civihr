<?php

use CRM_HRCore_Helper_NavigationMenuHelper as NavigationMenuHelper;

/**
 * @group headless
 */
class NavigationMenuHelperTest extends CRM_HRCore_Test_BaseHeadlessTest {

  /**
   * @dataProvider menuItemNameProvider
   *
   * @param string $name
   * @param bool $exists
   */
  public function testFindingMenuItemWillReturnExpectedResult($name, $exists) {
    $menu = $this->getSampleMenu();
    $result = NavigationMenuHelper::findMenuItemByName($menu, $name);

    if ($exists) {
      $foundName = $result['attributes']['name'];
      $this->assertEquals($name, $foundName);
    }
    else {
      $this->assertNull($result);
    }
  }

  public function testInsertionWillAddChildMenuItem() {
    $menu = $this->getSampleMenu();
    $name = 'Test Item';
    $newItem = ['name' => $name];
    $parent = 'Dropdown Options';
    NavigationMenuHelper::insertChild($menu, $parent, $newItem);

    $added = NavigationMenuHelper::findMenuItemByName($menu, $name);
    $this->assertNotNull($added);

    $parent = NavigationMenuHelper::findMenuItemByName($menu, 'Dropdown Options');
    $children = $parent['child'];
    $matchingChildItems = array_filter($children, function ($item) use ($name) {
      return $item['attributes']['name'] === $name;
    });

    $this->assertCount(1, $matchingChildItems);
    $matchingItem = reset($matchingChildItems);
    $this->assertEquals($name, $matchingItem['attributes']['name']);
  }

  public function testInsertionWillThrowExceptionWithInvalidPath() {
    $expectedError = 'Cannot find parent item "Bar"';
    $this->setExpectedException(\Exception::class, $expectedError);
    $menu = [];
    $parent = 'Bar';
    NavigationMenuHelper::insertChild($menu, $parent, []);
  }

  public function testSettingPermissionWillUpdateItInMenu() {
    $menu = $this->getSampleMenu();
    $name = 'System Status';
    NavigationMenuHelper::updatePermissionByName($menu, $name, 'foo');
    $item = NavigationMenuHelper::findMenuItemByName($menu, $name);

    $this->assertEquals('foo', $item['attributes']['permission']);
  }

  public function testFetchingWillNotReturnAReference() {
    $menu = $this->getSampleMenu();
    $name = 'System Status';
    $item = NavigationMenuHelper::findMenuItemByName($menu, $name);
    $item['foo'] = 'bar';
    $sameItem = NavigationMenuHelper::findMenuItemByName($menu, $name);

    $this->assertArrayNotHasKey('foo', $sameItem);
  }

  public function testMovingTopLevelMenuItemsWillChangeTheirPositions() {
    $menu = $this->getSampleMenu();
    $first = 'Home';
    $second = 'Search...';
    NavigationMenuHelper::relocateAfter($menu, $first, $second);

    $newFirst = reset($menu);
    $newSecond = next($menu);

    $this->assertEquals($second, $newFirst['attributes']['name']);
    $this->assertEquals($first, $newSecond['attributes']['name']);
  }

  public function testMovingNestedItemsWillChangeTheirPositions() {
    $nestedName = 'Activity Types';
    $nestedParent = 'Customize Data and Screens';
    $moveAfter = 'Membership Status Rules';
    $targetParent = 'CiviMember';

    $menu = $this->getSampleMenu();
    NavigationMenuHelper::relocateAfter($menu, $nestedName, $moveAfter);

    $origParent = NavigationMenuHelper::findMenuItemByName($menu, $nestedParent);
    $newParent = NavigationMenuHelper::findMenuItemByName($menu, $targetParent);

    $origChildren = $origParent['child'];
    $newChildren = $newParent['child'];
    $finderFunc = function ($child) use ($nestedName) {
      return $child['attributes']['name'] === $nestedName;
    };
    $matchingOriginalChildren = array_filter($origChildren, $finderFunc);
    $matchingNewChildren = array_filter($newChildren, $finderFunc);

    $this->assertCount(0, $matchingOriginalChildren);
    $this->assertCount(1, $matchingNewChildren);

    $newChildrenNames = array_map(function ($item) {
      return $item['attributes']['name'];
    }, $newChildren);

    $moveAfterPosition = array_search($moveAfter, $newChildrenNames);
    $movedItemPosition = array_search($nestedName, $newChildrenNames);
    $this->assertEquals($moveAfterPosition + 1, $movedItemPosition);
  }

  public function testRemovalWillUnsetElementAndChildren() {
    $menu = $this->getSampleMenu();
    $rootElement = 'Administer';
    $childElement = 'Custom Fields';
    NavigationMenuHelper::remove($menu, $rootElement);

    $foundRoot = NavigationMenuHelper::findParentItemName($menu, $rootElement);
    $foundChild = NavigationMenuHelper::findParentItemName($menu, $childElement);
    $this->assertNull($foundRoot);
    $this->assertNull($foundChild);
  }

  /**
   * @dataProvider menuItemParentNameProvider
   *
   * @param string $child
   * @param string $expected
   */
  public function testFetchingParentItemNameWillReturnParentName($child, $expected) {
    $menu = $this->getSampleMenu();
    $parentName = NavigationMenuHelper::findParentItemName($menu, $child);

    $this->assertEquals($expected, $parentName);
  }

  public function menuItemParentNameProvider() {
    return [
      [
        'Home',
        'root'
      ],
      [
        'ta_dashboard_tasks',
        'tasksassignments'
      ],
      [
        'hrjc_revision_change_reason',
        'Dropdown Options'
      ],
      [
        'I_DO_NOT_EXIST',
        NULL
      ],
      [
        'Administer',
        'root'
      ]
    ];
  }

  /**
   * @return array
   */
  public function menuItemNameProvider() {
    return [
      [
        'Home',
        TRUE
      ],
      [
        'I_DO_NOT_EXIST',
        FALSE
      ],
      [
        'About CiviCRM',
        TRUE
      ],
      [
        'New Life_Insurance_Provider',
        TRUE
      ]
    ];
  }

  /**
   * Get a sample navigation menu array
   *
   * @return array
   */
  private function getSampleMenu() {
    $jsonFile = __DIR__ . '/../Files/sample_navigation_menu.json';
    $contents = file_get_contents($jsonFile);

    return json_decode($contents, TRUE);
  }

}
