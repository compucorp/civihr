/* eslint-env amd, jasmine */

define([
  'common/mocks/services/file-uploader-mock',
  'mocks/apis/leave-request-api-mock',
  'mocks/apis/option-group-api-mock',
  'leave-absences/shared/instances/sickness-request.instance'
], function () {
  'use strict';

  describe('SicknessRequestInstance', function () {
    var $provide, SicknessRequestInstance, instance, LeaveRequestAPI;

    beforeEach(module('leave-absences.models.instances', 'leave-absences.mocks', 'common.mocks', function (_$provide_) {
      $provide = _$provide_;
    }));

    beforeEach(inject(function (_LeaveRequestAPIMock_, _OptionGroupAPIMock_,
      _FileUploaderMock_) {
      $provide.value('LeaveRequestAPI', _LeaveRequestAPIMock_);
      $provide.value('OptionGroup', _OptionGroupAPIMock_);
      $provide.value('FileUploader', _FileUploaderMock_);
    }));

    beforeEach(inject(['LeaveRequestAPI', 'SicknessRequestInstance',
      function (_LeaveRequestAPI_, _SicknessRequestInstance_) {
        SicknessRequestInstance = _SicknessRequestInstance_;
        LeaveRequestAPI = _LeaveRequestAPI_;

        spyOn(LeaveRequestAPI, 'create').and.callThrough();
        spyOn(LeaveRequestAPI, 'update').and.callThrough();
        spyOn(LeaveRequestAPI, 'isValid').and.callThrough();
        spyOn(SicknessRequestInstance, 'toAPI').and.callThrough();
      }
    ]));

    describe('default attributes', function () {
      beforeEach(function () {
        instance = SicknessRequestInstance.init({}, false);
      });

      it('initializes sickness reason', function () {
        expect(instance.sickness_reason).not.toBeUndefined();
      });

      it('initializes required documents', function () {
        expect(instance.sickness_required_documents).toEqual('');
      });

      it('initializes required documents', function () {
        expect(instance.sickness_required_documents).toEqual('');
      });

      it('initializes request type', function () {
        expect(instance.request_type).toEqual('sickness');
      });
    });

    describe('toggleDocument()', function () {
      beforeEach(function () {
        instance = SicknessRequestInstance.init({}, false);
      });

      describe('when toggled with unique string value', function () {
        beforeEach(function () {
          instance.toggleDocument('1');
          instance.toggleDocument('2');
        });

        it('appends it to required documents', function () {
          expect(instance.sickness_required_documents).toEqual('1,2');
        });
      });

      describe('when toggles with same string value', function () {
        beforeEach(function () {
          instance.toggleDocument('1');
          instance.toggleDocument('1');
        });

        it('removes string value from required documents', function () {
          expect(instance.sickness_required_documents).toEqual('');
        });
      });
    });
  });
});
