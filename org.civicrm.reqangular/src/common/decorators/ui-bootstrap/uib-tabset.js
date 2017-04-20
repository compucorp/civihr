// Decorates uibTabset to add 'customHeaderClass' and
// to change the temnplateUrl
define([
  'common/lodash'
], function(_) {
  'use strict';

  return ['$delegate', function($delegate) {
    var directive = $delegate[0];

    directive.bindToController = _.extend(directive.bindToController, {
      'customHeaderClass': '@'
    });
    directive.templateUrl = 'tab-outer.html';

    return $delegate;
  }];
});
