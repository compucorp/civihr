/* eslint-env amd, jasmine */

define([
  'mocks/data/leave-request-data',
  'mocks/apis/leave-request-api-mock',
  'common/mocks/services/file-uploader-mock',
  'mocks/apis/option-group-api-mock',
  'leave-absences/shared/instances/toil-request.instance'
], function (requestMockData) {
  'use strict';

  describe('TOILRequestInstance', function () {
    var $provide, TOILRequestInstance, instance, LeaveRequestAPI;

    beforeEach(module('leave-absences.models.instances', 'leave-absences.mocks', 'common.mocks', function (_$provide_) {
      $provide = _$provide_;
    }));

    beforeEach(inject(function (_LeaveRequestAPIMock_, _OptionGroupAPIMock_, _FileUploaderMock_) {
      $provide.value('LeaveRequestAPI', _LeaveRequestAPIMock_);
      $provide.value('OptionGroup', _OptionGroupAPIMock_);
      $provide.value('FileUploader', _FileUploaderMock_);
    }));

    beforeEach(inject([
      'LeaveRequestAPI', 'TOILRequestInstance',
      function (_LeaveRequestAPI_, _TOILRequestInstance_) {
        TOILRequestInstance = _TOILRequestInstance_;
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
        expect(instance.toilDurationHours).toBe('0');
        expect(instance.toilDurationMinutes).toBe('0');
      });

      it('initializes request type', function () {
        expect(instance.request_type).toEqual('toil');
      });
    });

    describe('updateDuration()', function () {
      beforeEach(function () {
        instance = TOILRequestInstance.init({}, false);
        instance.toilDurationHours = 1;
        instance.toilDurationMinutes = 1;
        instance.updateDuration();
      });

      it('updates durations in minutes', function () {
        expect(instance.toil_duration).toEqual(61);
      });
    });

    describe('edit toil', function () {
      beforeEach(function () {
        var toilRequest = requestMockData.findBy('request_type', 'toil');
        instance = TOILRequestInstance.init(toilRequest);
      });

      it('sets duration hours', function () {
        expect(instance.toilDurationHours).toEqual('3');
      });

      it('sets duration minutes', function () {
        expect(instance.toilDurationMinutes).toEqual('1');
      });
    });
  });
});
