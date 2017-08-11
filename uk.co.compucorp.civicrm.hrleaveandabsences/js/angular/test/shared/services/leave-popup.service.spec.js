/* eslint-env amd, jasmine */

define([
  'leave-absences/shared/services/leave-popup.service'
], function () {
  'use strict';

  describe('LeavePopup', function () {
    var $provide, $rootScope, $q, notification, $uibModal, LeavePopup, LeaveRequest, LeaveRequestInstance;

    beforeEach(module('common.angularDate', 'leave-absences.models', 'leave-absences.services', 'leave-absences.settings', function (_$provide_) {
      $provide = _$provide_;
    }));

    beforeEach(inject(function () {
      $uibModal = jasmine.createSpyObj('$uibModal', ['open']);
      $provide.value('$uibModal', $uibModal);
    }));

    beforeEach(inject(function (_$rootScope_, _$q_, _notificationService_, _LeavePopup_, _LeaveRequest_, _LeaveRequestInstance_) {
      $rootScope = _$rootScope_;
      $q = _$q_;
      notification = _notificationService_;
      LeavePopup = _LeavePopup_;
      LeaveRequest = _LeaveRequest_;
      LeaveRequestInstance = _LeaveRequestInstance_;

      spyOn(LeaveRequest, 'find');
      spyOn(notification, 'alert');
    }));

    describe('openModal()', function () {
      describe('when called without a Leave request and with a leave type parameter', function () {
        beforeEach(function () {
          LeavePopup.openModal(null, 'Leave');
        });

        it('initializes the leave popup with LeaveRequestController', function () {
          expect($uibModal.open).toHaveBeenCalledWith(jasmine.objectContaining({
            controller: 'LeaveRequestCtrl'
          }));
        });
      });

      describe('when called with a Leave request', function () {
        var requestType = 'SomeValue';

        beforeEach(function () {
          LeavePopup.openModal({ request_type: requestType });
        });

        it('initializes the leave popup with capitalizing the request type', function () {
          expect($uibModal.open).toHaveBeenCalledWith(jasmine.objectContaining({
            controller: requestType + 'RequestCtrl'
          }));
        });
      });
    });

    describe('openModalByID()', function () {
      var promise, errorMessage;

      afterEach(function () {
        $rootScope.$digest();
      });

      describe('when Leave Request is not found', function () {
        beforeEach(function () {
          LeaveRequest.find.and.returnValue($q.reject(errorMessage));
          promise = LeavePopup.openModalByID('101');
        });

        it('initializes the leave popup with LeaveRequestController', function () {
          promise.then(function () {
            expect(notification.alert).toHaveBeenCalledWith('Error', errorMessage);
          });
        });
      });

      describe('when Leave Request is found', function () {
        var instance;

        describe('and the user has permission to view the request', function () {
          beforeEach(function () {
            instance = LeaveRequestInstance.init({});

            spyOn(LeavePopup, 'openModal');
            spyOn(instance, 'roleOf').and.returnValue($q.resolve('admin'));
            LeaveRequest.find.and.returnValue($q.resolve(instance));

            promise = LeavePopup.openModalByID('101');
          });

          it('opens the leave popup', function () {
            promise.then(function () {
              expect($uibModal.open).toHaveBeenCalled();
            });
          });
        });

        describe('and the user does not have permission to view the request', function () {
          beforeEach(function () {
            instance = LeaveRequestInstance.init({});

            spyOn(LeavePopup, 'openModal');
            spyOn(instance, 'roleOf').and.returnValue($q.resolve('none'));
            LeaveRequest.find.and.returnValue($q.resolve(instance));

            promise = LeavePopup.openModalByID('101');
          });

          it('shows an error message', function () {
            promise.then(function () {
              expect(notification.alert).toHaveBeenCalledWith('Error', 'You dont have permission to see this leave request');
            });
          });
        });
      });
    });
  });
});
