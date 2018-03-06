<?php

use CRM_HRCore_Helper_NavigationMenuHelper as NavigationMenuHelper;

/**
 * @group headless
 */
class NavigationMenuHelperTest extends CRM_HRCore_Test_BaseHeadlessTest {

  /**
   * @dataProvider menuItemNameProvider
   *
   * @param string $path
   * @param bool $exists
   */
  public function testFindingMenuItemWillReturnExpectedResult($path, $exists) {
    $menu = $this->getSampleMenu();
    $result = NavigationMenuHelper::findMenuItemByPath($menu, $path);
    $parts = explode('/', $path);
    $expectedName = end($parts);

    if ($exists) {
      $foundName = $result['attributes']['name'];
      $this->assertEquals($expectedName, $foundName);
    }
    else {
      $this->assertNull($result);
    }
  }

  public function testInsertionWillAddChildMenuItem() {
    $menu = $this->getSampleMenu();
    $childName = 'Test Item';
    $newItem = ['name' => $childName];
    $parentName = 'Dropdown Options';
    $parentPath = 'Administer/Customize Data and Screens/' . $parentName;
    $childPath = $parentPath . '/' . $childName;
    NavigationMenuHelper::insertChild($menu, $parentPath, $newItem);

    $added = NavigationMenuHelper::findMenuItemByPath($menu, $childPath);
    $this->assertNotNull($added);

    $parentPath = NavigationMenuHelper::findMenuItemByPath($menu, $parentPath);
    $children = $parentPath['child'];
    $childMatches = array_filter($children, function ($item) use ($childName) {
      return $item['attributes']['name'] === $childName;
    });

    $this->assertCount(1, $childMatches);
    $matchingItem = reset($childMatches);
    $this->assertEquals($childName, $matchingItem['attributes']['name']);
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
    $path = 'Administer/Administration Console/System Status';
    NavigationMenuHelper::updatePermissionByPath($menu, $path, 'foo');
    $item = NavigationMenuHelper::findMenuItemByPath($menu, $path);

    $this->assertEquals('foo', $item['attributes']['permission']);
  }

  public function testFetchingWillNotReturnAReference() {
    $menu = $this->getSampleMenu();
    $name = 'Administer/Administration Console/System Status';
    $item = NavigationMenuHelper::findMenuItemByPath($menu, $name);
    $item['foo'] = 'bar';
    $sameItem = NavigationMenuHelper::findMenuItemByPath($menu, $name);

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
    $itemName = 'Activity Types';
    $originalParentPath = 'Administer/Customize Data and Screens';
    $itemPath = $originalParentPath . '/' . $itemName;
    $targetParentPath = 'Administer/CiviMember';
    $precedingItemName = 'Membership Status Rules';
    $moveAfterPath = $targetParentPath . '/' . $precedingItemName;

    $menu = $this->getSampleMenu();
    NavigationMenuHelper::relocateAfter($menu, $itemPath, $moveAfterPath);

    $origParent = NavigationMenuHelper::findMenuItemByPath($menu, $originalParentPath);
    $newParent = NavigationMenuHelper::findMenuItemByPath($menu, $targetParentPath);

    $origChildren = $origParent['child'];
    $newChildren = $newParent['child'];
    $finderFunc = function ($child) use ($itemName) {
      return $child['attributes']['name'] === $itemName;
    };
    $matchingOriginalChildren = array_filter($origChildren, $finderFunc);
    $matchingNewChildren = array_filter($newChildren, $finderFunc);

    $this->assertCount(0, $matchingOriginalChildren);
    $this->assertCount(1, $matchingNewChildren);

    $newChildrenNames = array_map(function ($item) {
      return $item['attributes']['name'];
    }, $newChildren);

    $moveAfterPosition = array_search($precedingItemName, $newChildrenNames);
    $movedItemPosition = array_search($itemName, $newChildrenNames);
    $this->assertEquals($moveAfterPosition + 1, $movedItemPosition);
  }

  public function testRemovalWillUnsetElementAndChildren() {
    $menu = $this->getSampleMenu();
    $rootElement = 'Administer';
    $childElement = 'Administer/Custom Fields';
    NavigationMenuHelper::remove($menu, $rootElement);

    $foundRoot = NavigationMenuHelper::findMenuItemByPath($menu, $rootElement);
    $foundChild = NavigationMenuHelper::findMenuItemByPath($menu, $childElement);
    $this->assertNull($foundRoot);
    $this->assertNull($foundChild);
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
        'Support/About CiviCRM',
        TRUE
      ],
      [
        'Contacts/New Organization/New Life_Insurance_Provider',
        TRUE
      ],
      [
        'Events/Register Event Participant',
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
