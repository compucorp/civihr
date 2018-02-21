/* eslint-env amd, jasmine */

define([
  'common/angularMocks',
  'common/controllers/dialog/dialog-ctrl',
  'common/modules/services',
  'common/angularBootstrap'
], function () {
  'use strict';

  describe('DialogController', function () {
    var $controller, $q, $rootScope, $scope, modalInstanceSpy;

    beforeEach(module('common.services', 'common.controllers', 'ui.bootstrap'));

    beforeEach(inject(function (_$controller_, _$q_, _$rootScope_) {
      $controller = _$controller_;
      $q = _$q_;
      $rootScope = _$rootScope_;
      $scope = _$rootScope_.$new();
    }));

    beforeEach(function () {
      modalInstanceSpy = jasmine.createSpyObj('modalInstanceSpy', ['close']);
    });

    describe('basic tests', function () {
      var props = {
        title: 'Are you sure?',
        onConfirm: function () {}
      };

      beforeEach(function () {
        initController(props);
      });

      it('has a "cancel" public function', function () {
        expect($scope.cancel).toEqual(jasmine.any(Function));
      });

      it('has a "confirm" public function', function () {
        expect($scope.confirm).toEqual(jasmine.any(Function));
      });

      it('sets properties to the scope', function () {
        expect($scope).toEqual(jasmine.objectContaining(props));
      });

      describe('when user cancels the action', function () {
        beforeEach(function () {
          $scope.cancel();
        });

        it('closes the dialog', function () {
          expect(modalInstanceSpy.close).toHaveBeenCalledWith(false);
        });
      });

      describe('when user confirms the action', function () {
        beforeEach(function () {
          spyOn($scope, 'onConfirm').and.callThrough();
          $scope.confirm();
        });

        it('shows loading icon', function () {
          expect($scope.loading).toBe(true);
        });

        describe('when confirmation is resolved', function () {
          beforeEach(function () {
            $rootScope.$digest();
          });

          it('executes the confirmation handler', function () {
            expect($scope.onConfirm).toHaveBeenCalled();
          });

          it('closes the dialog', function () {
            expect(modalInstanceSpy.close).toHaveBeenCalledWith(true);
          });
        });
      });
    });

    describe('when properties are delayed', function () {
      var titleBeforeLoad = 'Please wait...';
      var titleAfterLoad = 'Are you sure?';
      var confirmationHandler = function () {};

      beforeEach(function () {
        var props = {
          title: titleBeforeLoad,
          delayedProps: function () {
            return $q.resolve().then(function () {
              return {
                title: titleAfterLoad,
                onConfirm: confirmationHandler
              };
            });
          }
        };

        initController(props);
      });

      it('sets initial title', function () {
        expect($scope.title).toBe(titleBeforeLoad);
      });

      it('does not yet have a handler for the action confirmation', function () {
        expect($scope.onConfirm).not.toBeDefined();
      });

      describe('when loads', function () {
        beforeEach(function () {
          $rootScope.$digest();
        });

        it('changes the title', function () {
          expect($scope.title).toBe(titleAfterLoad);
        });

        it('sets the handler for the action confirmation', function () {
          expect($scope.onConfirm).toBe(confirmationHandler);
        });
      });
    });

    /**
     * Initiates the controller with properties
     *
     * @param {Object}  props
     */
    function initController (props) {
      $controller('DialogController', {
        $scope: $scope,
        $uibModalInstance: modalInstanceSpy,
        props: props
      });
    }
  });
});
