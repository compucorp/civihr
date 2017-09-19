/* eslint-env amd */

define([
  'common/modules/components',
  'common/moment'
], function (components, moment) {
  components.component('datePicker', {
    require: { ngModelCtrl: 'ngModel' },
    bindings: {
      showClearBtn: '<',
      clearBtnClick: '&?',
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

    vm.clearButtonClick = clearButtonClick;
    vm.convertToHTML5DatepickerFormat = convertToHTML5DatepickerFormat;
    vm.ngModelChange = ngModelChange;

    /**
     * Calls the clear button click event if it is passed,
     * Otherwise clears the ng-model
     */
    function clearButtonClick () {
      if (vm.clearBtnClick) {
        vm.clearBtnClick()
      } else {
        vm.ngModel = false;
        vm.ngModelCtrl.$setViewValue(vm.ngModel);
      }
    }

    /**
     * Converts a Date object into HTML5 Datepicker format
     *
     * @param  {Date} date
     * @return {String}
     */
    function convertToHTML5DatepickerFormat (date) {
      return date ? moment(date).format('Y-MM-D') : '';
    }

    function ngModelChange () {
      vm.ngModelCtrl.$setViewValue(vm.ngModel);
      vm.ngChange();
    }
  }
});
