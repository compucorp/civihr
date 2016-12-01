define([], function () {
  'use strict';

  /**
   * Decorates the uib-datepicker-popup directive, making its sibling '.input-group-addon'
   * trigger the datepicker
   */
  return ['$delegate', function ($delegate) {
    var directive = $delegate[0];
    var origCompile = directive.compile;

    directive.compile = function compile() {
      var link = origCompile.apply(this, arguments);

      return function (scope, element) {
        link.apply(this, arguments);

        function onClick() {
          element.trigger('click');
        }

        var inputGroupAddon = element.siblings('.input-group-addon');

        if (inputGroupAddon.length) {
          inputGroupAddon.on('click', onClick);

          scope.$on('$destroy', function() {
            inputGroupAddon.off('click', onClick);
          });
        }
      };
    };

    return $delegate;
  }];
});
