define([], function() {
  'use strict';

  return ['$delegate', function($delegate) {
    var directive = $delegate[0];
    var origTemplateUrl = directive.templateUrl;

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
