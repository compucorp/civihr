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

        // Adding the "contactList" property to the controller
        scope.$select.contactList = element.parent().parent()[0].hasAttribute('contacts');

        // Enabling the "perfect scrollbar" plugin
        if (element.closest('.civihr-ui-select').length) {
          ps.initialize(element[0]);
        }
      };
    };

    return $delegate;
  }];
});
