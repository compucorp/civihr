define([], function() {
  'use strict';

  return ['$delegate', '$timeout', '$animate', function($delegate, $timeout, $animate) {
    var directive = $delegate[0];
    var origTemplateUrl = directive.templateUrl;
    var directiveCompile = directive.compile;

    // Fix the focus on first click
    directive.compile = function (tElement, tAttrs) {
      var link = directiveCompile.apply(this, arguments);

      return function (scope, elem, attrs, uiSelect) {
        link.apply(this, arguments);
        scope.$watch('$select.open', function (val) {
          $timeout(function () {
            if (elem.hasClass('open')) {
              elem.find('input').focus();
            }
          }, 100);
        });
      };
    };

    // In order to add the "contact actions" the the dropdown, the template file
    // is changed depending on the presence of the "contacts" attribute
    directive.templateUrl = function(elem, attrs) {
      if (angular.isDefined(attrs.contacts)) {
        return angular.isDefined(attrs.multiple) ? 'civihr-ui-select/select-contacts-multiple.tpl.html' : 'civihr-ui-select/select-contacts.tpl.html';
      }
      return origTemplateUrl.apply(this, arguments);
    };
    return $delegate;
  }];
});
