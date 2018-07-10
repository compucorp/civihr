/* eslint-env amd, jasmine */

define([
  'common/angular',
  'common/angularMocks',
  'leave-absences/calendar-feeds/link-modal/link-modal.module'
], function (angular) {
  'use strict';

  describe('inputWithCopyButton', function () {
    var $rootScope, $timeout, copyInput, copyButton, inputWithCopyButton, scope;
    var modelValue = 'http://www.civihr.org/';

    beforeEach(angular.mock.module('calendar-feeds.link-modal', 'leave-absences.templates'));

    beforeEach(inject(function ($compile, _$rootScope_, _$timeout_) {
      $rootScope = _$rootScope_;
      $timeout = _$timeout_;
      scope = $rootScope.$new();
      scope.url = modelValue;
      inputWithCopyButton = $compile('<input-with-copy-button ng-model="url"></input-with-copy-button>')(scope);

      $rootScope.$digest();

      copyInput = inputWithCopyButton.find('input').eq(0);
      copyButton = inputWithCopyButton.find('button').eq(0);
    }));

    it('is defined', function () {
      expect(inputWithCopyButton.find('.input-with-copy-button-component').length).toBe(1);
    });

    it('displays the model value in inside an input', function () {
      expect(copyInput.val()).toEqual(modelValue);
    });

    it('does not allow to change the input value', function () {
      expect(copyInput.attr('readonly')).toBeDefined();
    });

    describe('when focusing the copy input', function () {
      beforeEach(function () {
        copyInput.triggerHandler('click');
        $rootScope.$digest();
      });

      it('selects the whole text in the copy input', function () {
        expect(copyInput[0].selectionStart).toBe(0);
        expect(copyInput[0].selectionEnd).toBe(copyInput.val().length);
      });
    });

    describe('when clicking the copy button', function () {
      var copiedValue;

      beforeEach(function () {
        spyOn(document, 'execCommand').and.callFake(function () {
          copiedValue = copyInput.val().slice(copyInput[0].selectionStart,
            copyInput[0].selectionEnd);
        });
        copyButton.triggerHandler('click');
        $rootScope.$digest();
      });

      it('executes the "copy" command', function () {
        expect(document.execCommand).toHaveBeenCalledWith('copy');
      });

      it('copies the content of the input to the clipboard', function () {
        expect(copiedValue).toBe(modelValue);
      });

      it('shows that the input has just been copied', function () {
        expect(copyButton.text().trim()).toBe('Copied!');
      });

      it('does not leave text selected inside the copy input', function () {
        expect(copyInput[0].selectionStart).toBe(0);
        expect(copyInput[0].selectionEnd).toBe(0);
      });

      describe('when time has passed', function () {
        beforeEach(function () {
          $timeout.flush();
        });

        it('reverts the button view to the original state', function () {
          expect(copyButton.text().trim()).toBe('Copy');
        });
      });
    });
  });
});
