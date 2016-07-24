define([], function () {
  'use strict';

  /**
   * Decorates ui-select-choices directive, in order for it to have custom scrollbars,
   * using the "perfect-scrollbar" plugin.
   */
  return ['$delegate', function ($delegate) {
    var directive = $delegate[0];
    var origTemplateUrl = directive.templateUrl;

    directive.templateUrl = function(tElem, tAttrs){
      console.log(tAttrs);
      if(angular.isDefined(tAttrs['contacts'])){
        return 'civihr-ui-select/select-contacts.tpl.html';
      }
      return origTemplateUrl.apply(this, arguments);
    };
    return $delegate;
  }];
});
