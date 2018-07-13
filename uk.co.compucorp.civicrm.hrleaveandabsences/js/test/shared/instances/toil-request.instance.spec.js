/* eslint-env amd, jasmine */

define([
  'leave-absences/mocks/data/leave-request.data',
  'leave-absences/mocks/apis/leave-request-api-mock',
  'common/mocks/services/file-uploader-mock',
  'leave-absences/mocks/apis/absence-type-api-mock',
  'leave-absences/mocks/apis/option-group-api-mock',
  'leave-absences/shared/instances/toil-request.instance'
], function (requestMockData) {
  'use strict';

  describe('TOILRequestInstance', function () {
    var $provide, TOILRequestInstance, instance, LeaveRequestAPI;

    beforeEach(module('leave-absences.models.instances', 'leave-absences.mocks',
      'common.mocks', 'leave-absences.models', function (_$provide_) {
        $provide = _$provide_;
      }));

    beforeEach(inject(function (_AbsenceTypeAPIMock_, _LeaveRequestAPIMock_,
      _OptionGroupAPIMock_, _FileUploaderMock_) {
      $provide.value('AbsenceTypeAPI', _AbsenceTypeAPIMock_);
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

      it('default fields for backend are set', function () {
        expect(instance.from_date_amount).toBe(0);
        expect(instance.to_date_amount).toBe(0);
      });

      it('initializes request type', function () {
        expect(instance.request_type).toEqual('toil');
      });
    });

    describe('edit toil', function () {
      beforeEach(function () {
        var toilRequest = requestMockData.findBy('request_type', 'toil');
        instance = TOILRequestInstance.init(toilRequest);
      });
    });
  });
});
