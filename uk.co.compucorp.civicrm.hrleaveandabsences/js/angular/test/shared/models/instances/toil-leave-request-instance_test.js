define([
  'mocks/apis/leave-request-api-mock',
  'mocks/apis/option-group-api-mock',
  'leave-absences/shared/models/instances/toil-leave-request-instance',
], function (helper) {
  'use strict';

  describe('TOILRequestInstance', function () {
    var $rootScope, $provide, TOILRequestInstance, instance, LeaveRequestAPI, promise;

    beforeEach(module('leave-absences.models.instances', 'leave-absences.mocks', function (_$provide_) {
      $provide = _$provide_;
    }));

    beforeEach(inject(function (_LeaveRequestAPIMock_, _OptionGroupAPIMock_) {
      $provide.value('LeaveRequestAPI', _LeaveRequestAPIMock_);
      $provide.value('OptionGroup', _OptionGroupAPIMock_);
    }));

    beforeEach(inject([
      '$rootScope', 'LeaveRequestAPI', 'TOILRequestInstance',
      function (_$rootScope_, _LeaveRequestAPI_, _TOILRequestInstance_) {
        TOILRequestInstance = _TOILRequestInstance_;
        $rootScope = _$rootScope_;
        LeaveRequestAPI = _LeaveRequestAPI_;

        spyOn(LeaveRequestAPI, 'create').and.callThrough();
        spyOn(LeaveRequestAPI, 'update').and.callThrough();
        spyOn(LeaveRequestAPI, 'isValid').and.callThrough();
        spyOn(TOILRequestInstance, 'toAPI').and.callThrough();
      }
    ]));

    describe('init', function () {
      beforeEach(function () {
        instance = TOILRequestInstance.init({}, false);
      });

      it('instance is defined', function () {
        expect(instance).toBeDefined();
      });

      it('default toil Duration value is set', function () {
        expect(instance.toilDuration).toBe(0);
      })
    });

    describe('create()', function () {
      beforeEach(function () {
        instance = TOILRequestInstance.init({}, false);
        promise = instance.create();
      });

      afterEach(function () {
        //to excute the promise force an digest
        $rootScope.$apply();
      });

      it('calls equivalent API method', function () {
        expect(LeaveRequestAPI.create).toHaveBeenCalledWith(jasmine.any(Object), 'toil');
      });

      it('calls toAPI method', function () {
        expect(instance.toAPI).toHaveBeenCalled();
      });

      describe('id field', function() {
        it('is not appended to instance after API returns data', function() {
          expect(instance.id).not.toBeDefined();
        });

        it('is appended to instance after API returns data', function() {
          promise.then(function () {
            expect(instance.id).toEqual(jasmine.any(String));
          });
        });
      });
    });

    describe('isValid()', function () {
      beforeEach(function () {
        instance = TOILRequestInstance.init({}, false);
        promise = instance.isValid();
      });

      afterEach(function () {
        $rootScope.$apply();
      });

      it('calls equivalent API method', function () {
        expect(LeaveRequestAPI.isValid).toHaveBeenCalledWith(jasmine.any(Object), 'toil');
      });

      it('calls toAPI method', function () {
        expect(instance.toAPI).toHaveBeenCalled();
      });
    });

    describe('update()', function () {
      beforeEach(function () {
        instance = TOILRequestInstance.init({}, false);
        promise = instance.update();
      });

      afterEach(function () {
        $rootScope.$apply();
      });

      it('calls update api method with the return value of toAPI method', function () {
        expect(LeaveRequestAPI.update).toHaveBeenCalledWith(jasmine.any(Object), 'toil');
      });

      it('calls toAPI method', function () {
        expect(instance.toAPI).toHaveBeenCalled();
      });
    });
  });
});
