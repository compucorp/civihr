<?php

use CRM_HRCore_Helper_NavigationMenuHelper as NavigationMenuHelper;
use CRM_HRCore_Test_BaseHeadlessTest as BaseHeadlessTest;

/**
 * @group headless
 */
class CRM_HRCore_Helper_NavigationMenuHelperTest extends BaseHeadlessTest {

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
    $attributes = ['foo' => 'bar'];
    $parentName = 'Dropdown Options';
    $parentPath = 'Administer/Customize Data and Screens/' . $parentName;
    $childPath = $parentPath . '/' . $childName;
    NavigationMenuHelper::insert($menu, $childPath, $attributes);

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

  public function testInsertionWillAddRootMenuItem() {
    $menu = $this->getSampleMenu();
    $name = 'Foo';
    NavigationMenuHelper::insert($menu, $name);
    $newestEntry = array_pop($menu);

    $this->assertEquals($name, $newestEntry['attributes']['name']);
  }

  public function testAttributesWillOverrideDefaultsInInsertion() {
    $menu = $this->getSampleMenu();
    $name = 'Foo';
    $label = 'Bar';
    NavigationMenuHelper::insert($menu, $name, ['label' => $label]);
    $newestEntry = array_pop($menu);

    $this->assertEquals($label, $newestEntry['attributes']['label']);
  }

  public function testInsertionWillThrowExceptionWithInvalidPath() {
    $expectedError = 'Cannot find parent item "Foo"';
    $this->setExpectedException(\Exception::class, $expectedError);
    $menu = [];
    $path = 'Foo/Bar';
    NavigationMenuHelper::insert($menu, $path, []);
  }

  public function testSettingPermissionWillUpdateItInMenu() {
    $menu = $this->getSampleMenu();
    $path = 'Administer/Administration Console/System Status';
    NavigationMenuHelper::updatePermissionByPath($menu, $path, 'foo');
    $item = NavigationMenuHelper::findMenuItemByPath($menu, $path);

    $this->assertEquals('foo', $item['attributes']['permission']);
  }

  public function testFindMenuItemByNameWillNotReturnAReference() {
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
    $second = 'Contacts';
    NavigationMenuHelper::relocateAfter($menu, $first, $second);

    $newFirst = reset($menu);
    $newSecond = next($menu);

    $this->assertEquals($second, $newFirst['attributes']['name']);
    $this->assertEquals($first, $newSecond['attributes']['name']);
  }

  /**
   * @dataProvider itemsToMoveProvider
   *
   * @param string $pathToMove
   * @param string $pathBefore
   */
  public function testInsertingAfterWillPlaceAfterNamedItem(
    $pathToMove,
    $pathBefore
  ) {
    $menu = $this->getSampleMenu();
    $parts = explode('/', $pathToMove);
    $itemName = array_pop($parts);
    $originalParentPath = implode('/', $parts);
    $newParentParts = explode('/', $pathBefore);
    $insertBeforeName = array_pop($newParentParts);
    $newParentPath = implode('/', $newParentParts);

    NavigationMenuHelper::relocateAfter($menu, $pathToMove, $pathBefore);

    $matcherFunc = function ($child) use ($itemName) {
      return $child['attributes']['name'] === $itemName;
    };

    if ($originalParentPath !== '') {
      $originalParent = NavigationMenuHelper::findMenuItemByPath($menu, $originalParentPath);
      $originalChildren = $originalParent['child'];
    } else {
      $originalChildren = $menu;
    }

    if ($newParentPath !== '') {
      $newParent = NavigationMenuHelper::findMenuItemByPath($menu, $newParentPath);
      $newParentChildren = $newParent['child'];
    } else {
      $newParentChildren = $menu;
    }

    // If it was moved to a different menu, check that it doesn't exist in old
    if ($originalParentPath !== $newParentPath) {
      $matchingOriginalChildren = array_filter($originalChildren, $matcherFunc);
      $matchingNewChildren = array_filter($newParentChildren, $matcherFunc);

      $this->assertCount(0, $matchingOriginalChildren);
      $this->assertCount(1, $matchingNewChildren);
    }

    $moveTargetMenuNames = array_map(function ($item) {
      return $item['attributes']['name'];
    }, $newParentChildren);

    $moveBeforePosition = array_search($insertBeforeName, $moveTargetMenuNames);
    $movedItemPosition = array_search($itemName, $moveTargetMenuNames);
    $this->assertEquals($moveBeforePosition + 1, $movedItemPosition);
  }

  /**
   * @dataProvider itemsToMoveProvider
   *
   * @param string $pathToMove
   * @param string $pathAfter
   */
  public function testInsertingBeforeWillPlaceBeforeNamedItem(
    $pathToMove,
    $pathAfter
  ) {
    $menu = $this->getSampleMenu();
    $parts = explode('/', $pathToMove);
    $itemName = array_pop($parts);
    $originalParentPath = implode('/', $parts);
    $newParentParts = explode('/', $pathAfter);
    $insertBeforeName = array_pop($newParentParts);
    $newParentPath = implode('/', $newParentParts);

    NavigationMenuHelper::relocateBefore($menu, $pathToMove, $pathAfter);

    $matcherFunc = function ($child) use ($itemName) {
      return $child['attributes']['name'] === $itemName;
    };

    if ($originalParentPath !== '') {
      $originalParent = NavigationMenuHelper::findMenuItemByPath($menu, $originalParentPath);
      $originalChildren = $originalParent['child'];
    } else {
      $originalChildren = $menu;
    }

    if ($newParentPath !== '') {
      $newParent = NavigationMenuHelper::findMenuItemByPath($menu, $newParentPath);
      $newParentChildren = $newParent['child'];
    } else {
      $newParentChildren = $menu;
    }

    // If it was moved to a different menu, check that it doesn't exist in old
    if ($originalParentPath !== $newParentPath) {
      $matchingOriginalChildren = array_filter($originalChildren, $matcherFunc);
      $matchingNewChildren = array_filter($newParentChildren, $matcherFunc);

      $this->assertCount(0, $matchingOriginalChildren);
      $this->assertCount(1, $matchingNewChildren);
    }

    $moveTargetMenuNames = array_map(function ($item) {
      return $item['attributes']['name'];
    }, $newParentChildren);

    $moveBeforePosition = array_search($insertBeforeName, $moveTargetMenuNames);
    $movedItemPosition = array_search($itemName, $moveTargetMenuNames);
    $this->assertEquals($moveBeforePosition - 1, $movedItemPosition);
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
        'Contacts/New Organization/New Health_Insurance_Provider',
        TRUE
      ],
      [
        'Administer/Administration Console',
        TRUE
      ],
      [
        'Administer/Customize Data and Screens/Dropdown Options/Gender Options',
        TRUE
      ]
    ];
  }

  /**
   * @return array
   */
  public function itemsToMoveProvider() {
    return [
      [
        'Administer/Administration Console',
        'Home'
      ],
      [
        'Home',
        'Administer'
      ],
      [
        'Administer/Customize Data and Screens/Custom Fields',
        'Contacts/New Organization'
      ],
      [
        'Administer/Customize Data and Screens/Dropdown Options/Gender Options',
        'Administer/Administration Console',
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
