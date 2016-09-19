define([
  'common/vendor/perfect-scrollbar'
], function (ps) {
  'use strict';

  return ['$delegate', function ($delegate) {
    var directive = $delegate[0];
    var origCompile = directive.compile;

    directive.compile = function compile() {
      var link = origCompile.apply(this, arguments);

      return function (scope, element) {
        link.apply(this, arguments);

        // Enabling the "perfect scrollbar" plugin
        var civihrUiSelect = element.closest('.civihr-ui-select');
        if (civihrUiSelect.length) {
          ps.initialize(element[0]);
          
          // Adding the "contactList" property to the controller
          scope.$select.contactList = civihrUiSelect.attr('contacts') !== undefined;
        }
      };
    };

    return $delegate;
  }];
});
