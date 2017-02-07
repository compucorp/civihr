define([
  'mocks/helpers/helper',
  'mocks/apis/leave-request-api-mock',
  'mocks/apis/option-group-api-mock',
  'leave-absences/shared/models/instances/sickness-leave-request-instance',
], function (helper) {
  'use strict';

  describe('SicknessRequestInstance', function () {
    var expectedError, instance, LeaveRequestAPI, $provide, promise, requestData, $rootScope;

    beforeEach(module('leave-absences.models.instances', 'leave-absences.mocks', function (_$provide_) {
      $provide = _$provide_;
    }));

    beforeEach(inject(function (_LeaveRequestAPIMock_, _OptionGroupAPIMock_) {
      $provide.value('LeaveRequestAPI', _LeaveRequestAPIMock_);
      $provide.value('OptionGroup', _OptionGroupAPIMock_);
    }));

    beforeEach(inject([
       '$rootScope', 'LeaveRequestAPI', 'SicknessRequestInstance',
      function (_$rootScope_, _LeaveRequestAPI_, _SicknessRequestInstance_) {
        instance = _SicknessRequestInstance_.init({}, false);
        $rootScope = _$rootScope_;
        LeaveRequestAPI = _LeaveRequestAPI_;

        spyOn(LeaveRequestAPI, 'create').and.callThrough();
        spyOn(LeaveRequestAPI, 'update').and.callThrough();
        spyOn(LeaveRequestAPI, 'isValid').and.callThrough();
      }
    ]));

    describe('init', function () {
      it('sickness request', function () {
        expect(instance).toBeDefined();
      });
    });

    describe('create()', function () {
      beforeEach(function () {
        requestData = helper.createRandomSicknessRequest();
        instance = instance.init(requestData, false);
        promise = instance.create();
      });

      afterEach(function () {
        //to excute the promise force an digest
        $rootScope.$apply();
      });

      it('calls equivalent API method', function () {
        promise.then(function () {
          expect(LeaveRequestAPI.create).toHaveBeenCalledWith(jasmine.any(Object), 'sick');
        });
      });

      describe('id field', function() {
        it('is not appended to instance before API returns data', function() {
          expect(instance.id).not.toBeDefined();
        });

        it('is appended to instance before API returns data', function() {
          promise.then(function () {
            expect(instance.id).toBeDefined();
            expect(instance.id).toEqual(jasmine.any(String));
          });
        });
      });
    });

    describe('isValid()', function () {
      beforeEach(function () {
        requestData = {
          contact_id: '123'
        };
        instance = instance.init(requestData);
        promise = instance.isValid();
      });

      afterEach(function () {
        $rootScope.$apply();
      });

      it('calls equivalent API method', function () {
        promise.then(function () {
          expect(LeaveRequestAPI.isValid).toHaveBeenCalledWith(jasmine.any(Object), 'sick');
        });
      });
    });

    describe('update()', function () {
      var toAPIReturnValue = {
          key: jasmine.any(String)
        };

      beforeEach(function () {
        spyOn(instance, 'toAPI').and.returnValue(toAPIReturnValue);
        promise = instance.update();
      });

      afterEach(function () {
        $rootScope.$apply();
      });

      it('calls update api method with the return value of toAPI method', function () {
        promise.then(function () {
          expect(LeaveRequestAPI.update).toHaveBeenCalledWith(toAPIReturnValue, 'sick');
        });
      });

      it('calls toAPI method', function () {
        promise.then(function () {
          expect(instance.toAPI).toHaveBeenCalled();
        });
      })
    });
  });
});
