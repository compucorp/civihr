define([
  'common/moment'
], function (moment) {
  'use strict';

  /**
   * Decorates the uib-datepicker-popup directive, making its sibling '.input-group-addon'
   * trigger the datepicker
   */
  return ['$delegate', '$compile', function ($delegate, $compile) {
    var directive = $delegate[0];
    var isMobile = document.getElementsByTagName('body')[0].classList.contains('mobile');
    var origCompile = directive.compile;
    var calendarIcon = '<span ' +
      'class="input-group-addon pointer">' +
      '<i class="fa fa-calendar"></i>' +
      '</span>';

    if (isMobile) {
      directive.require = 'ngModel';
      directive.compile = function compile() {
        return function (scope, element, attrs, ngModel) {
          element.parent('.input-group').css('display', 'block');
          element.after(calendarIcon);
          element.prop('type', 'date');
          attrs.ngReadonly = false;

          ngModel.$formatters.push(function (viewValue) {
            return convertToHTML5DatepickerFormat(viewValue);
          });

          scope.$watch('datepickerOptions.minDate', function (value) {
            attrs.$set('min', convertToHTML5DatepickerFormat(value));
          });
          scope.$watch('datepickerOptions.maxDate', function (value) {
            attrs.$set('max', convertToHTML5DatepickerFormat(value));
          });
        };
      };
    } else {
      directive.compile = function compile() {
        var link = origCompile.apply(this, arguments);

        return function (scope, element, attrs) {
          element.after(calendarIcon);

          link.apply(this, arguments);

          function onClick() {
            scope.isOpen = true;
            scope.$apply();
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
    }

    function convertToHTML5DatepickerFormat(date) {
      return date ? moment(date).format('Y-MM-DD') : '';
    }

    return $delegate;
  }];
});
