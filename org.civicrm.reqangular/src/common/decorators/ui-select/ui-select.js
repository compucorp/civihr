define([], function() {
  'use strict';

  /**
   * Decorates ui-select-choices directive, making
   * it possible to add the "contact actions" template
   */
  return ['$delegate', function($delegate) {
    var directive = $delegate[0];
    var origTemplateUrl = directive.templateUrl;

    directive.templateUrl = function(elem, attrs) {
      if (angular.isDefined(attrs.contacts)) {
        return 'civihr-ui-select/select-contacts.tpl.html';
      }
      return origTemplateUrl.apply(this, arguments);
    };
    return $delegate;
  }];
});
