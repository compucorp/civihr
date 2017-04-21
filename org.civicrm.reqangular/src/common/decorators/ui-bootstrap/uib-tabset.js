// Decorates uibTabset to add 'customHeaderClass'
define([], function() {
  'use strict';

  return ['$delegate', function($delegate) {
    var directive = $delegate[0];

    directive.bindToController.customHeaderClass = '@';

    return $delegate;
  }];
});
