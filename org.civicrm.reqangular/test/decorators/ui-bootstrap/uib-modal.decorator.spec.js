/* eslint-env amd, jasmine */

define([
  'common/lodash',
  'common/decorators/ui-bootstrap/uib-modal.decorator',
  'common/angularMocks',
  'common/angular',
  'common/angularBootstrap'
], function (_, $uibModalDecorator) {
  'use strict';

  describe('$uibModal.open', function () {
    var $document, $rootScope, $uibModal;

    beforeEach(module('ui.bootstrap'));

    beforeEach(module(function ($provide) {
      $provide.decorator('$uibModal', $uibModalDecorator);
    }));

    beforeEach(inject(function (_$document_, _$rootScope_, _$uibModal_) {
      $document = _$document_;
      $rootScope = _$rootScope_;
      $uibModal = _$uibModal_;
    }));

    describe('init', function () {
      var $elements;
      var arg1 = { template: '<component></component>' };
      var arg2 = 'another_argument';

      beforeEach(function () {
        $elements = $document.find('body, html');
      });

      describe('when modal is opened', function () {
        var modalInstance1;

        beforeEach(function () {
          modalInstance1 = $uibModal.open(arg1, arg2);
          $rootScope.$digest();
        });

        afterEach(function () {
          $document.find('.modal-dialog').remove();
        });

        it('locks document scroll', function () {
          expect($document.find('.modal-dialog').length).toBe(1);
          expect($elements.hasClass('chr_scroll-lock')).toBe(true);
        });

        describe('when modal is closed', function () {
          beforeEach(function () {
            // Closes the modal and executes the listener
            $document.find('.modal-dialog').remove();
            modalInstance1.closed.$$state.pending[0][1]();
          });

          it('unlocks document scroll', function () {
            expect($document.find('.modal-dialog').length).toBe(0);
            expect($elements.hasClass('chr_scroll-lock')).toBe(false);
          });
        });

        describe('when second modal is opened on top of the first modal', function () {
          var modalInstance2;

          beforeEach(function () {
            modalInstance2 = $uibModal.open(arg1, arg2);
            $rootScope.$digest();
          });

          it('remains document scroll locked', function () {
            expect($document.find('.modal-dialog').length).toBe(2);
            expect($elements.hasClass('chr_scroll-lock')).toBe(true);
          });

          describe('when second modal is closed', function () {
            beforeEach(function () {
              // Closes second modal and executes the listener
              $document.find('.modal-dialog').last().remove();
              modalInstance2.closed.$$state.pending[0][1]();
            });

            it('remains document scroll locked', function () {
              expect($document.find('.modal-dialog').length).toBe(1);
              expect($elements.hasClass('chr_scroll-lock')).toBe(true);
            });

            describe('and when the first modal is closed', function () {
              beforeEach(function () {
                // Closes the first modal and executes the listener
                $document.find('.modal-dialog').first().remove();
                modalInstance1.closed.$$state.pending[0][1]();
              });

              it('unlocks document scroll', function () {
                expect($document.find('.modal-dialog').length).toBe(0);
                expect($elements.hasClass('chr_scroll-lock')).toBe(false);
              });
            });
          });
        });
      });
    });
  });
});
