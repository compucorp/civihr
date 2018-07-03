/* eslint-env amd, jasmine */

define([
  'common/angular',
  'common/angularMocks',
  'leave-absences/calendar-feeds/link-modal/calendar-feeds.link-modal.module'
], function (angular) {
  'use strict';

  describe('inputWithCopyButton', function () {
    var $rootScope, inputWithCopyButton, scope;
    var modelValue = 'http://www.civihr.org/';

    beforeEach(angular.mock.module('calendar-feeds.link-modal', 'leave-absences.templates'));

    beforeEach(inject(function ($compile, _$rootScope_) {
      $rootScope = _$rootScope_;
      scope = $rootScope.$new();
      scope.url = modelValue;
      inputWithCopyButton = $compile('<input-with-copy-button ng-model="url"></input-with-copy-button>')(scope);
      $rootScope.$digest();
    }));

    it('is defined', function () {
      expect(inputWithCopyButton.find('.input-with-copy-button-component').length).toBe(1);
    });

    it('displays the model value in inside an input', function () {
      expect(inputWithCopyButton.find('input').val()).toEqual(modelValue);
    });

    describe('when clicking the copy button', function () {
      var copiedValue;

      beforeEach(function () {
        spyOn(document, 'execCommand').and.callFake(function () {
          var element = inputWithCopyButton.find('input')[0];

          copiedValue = element.value.slice(element.selectionStart,
            element.selectionEnd);
        });
        inputWithCopyButton.find('button').click();
        $rootScope.$digest();
      });

      it('executes the "copy" command', function () {
        expect(document.execCommand).toHaveBeenCalledWith('copy');
      });

      it('copies the content of the input to the clipboard', function () {
        expect(copiedValue).toBe(modelValue);
      });
    });
  });
});
