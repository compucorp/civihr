/* eslint-env amd */

define([
  'common/modules/components',
  'common/moment'
], function (components, moment) {
  components.component('datePicker', {
    require: { ngModelCtrl: 'ngModel' },
    bindings: {
      clearBtnClass: '@',
      showClearBtn: '<',
      clearBtnClick: '&',
      customNgClick: '&',
      datepickerOptions: '=',
      isOpen: '=',
      ngChange: '&',
      ngDisabled: '<',
      ngModel: '<',
      ngReadonly: '<',
      showButtonBar: '<'
    },
    templateUrl: 'date-picker.html',
    controllerAs: 'datePicker',
    controller: datePickerController
  });

  function datePickerController () {
    var vm = this;

    vm.convertToHTML5DatepickerFormat = convertToHTML5DatepickerFormat;
    vm.ngModelChange = ngModelChange;

    /**
     * Coverts a Date object into HTML5 Datepicker format
     *
     * @param  {Date} date
     * @return {String}
     */
    function convertToHTML5DatepickerFormat (date) {
      return date ? moment(date).format('Y-MM-D') : '';
    }

    function ngModelChange () {
      this.ngModelCtrl.$setViewValue(this.ngModel);
      this.ngChange();
    }
  }
});
