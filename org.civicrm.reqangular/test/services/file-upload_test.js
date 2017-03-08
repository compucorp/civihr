define([
  'common/angular',
  'common/angularMocks',
  'common/services/file-upload',
  'common/mocks/services/file-uploader-mock',
], function () {
  'use strict';

  describe('FileUploadService', function () {
    var $provide, $rootScope, fileUploadService, uploader, promise;

    beforeEach(module('common.services', 'common.mocks', function (_$provide_) {
      $provide = _$provide_;
    }));

    beforeEach(inject(function (_FileUploaderMock_) {
      $provide.value('FileUploader', _FileUploaderMock_);
    }));

    beforeEach(inject(['FileUploadService',
      function (_FileUploadService_) {
        fileUploadService = _FileUploadService_;
      }
    ]));

    beforeEach(inject(function (_$rootScope_) {
      $rootScope = _$rootScope_;
      uploader = fileUploadService.uploader({
        entityTable: 'civicrm_hrleaveandabsences_leave_request'
      });
    }));

    it('has all endpoints', function () {
      expect(Object.keys(fileUploadService)).toContain('uploader', 'uploadAll');
    });

    describe('uploader()', function () {
      it('creates uploader', function () {
        expect(uploader).toBeDefined();
      });
    });

    describe('uploadAll()', function () {
      var param;

      beforeEach(function () {
        spyOn(fileUploadService, 'uploadAll').and.callThrough();
        spyOn(uploader, 'uploadAll').and.callThrough();
        param = {
          entityID: '12'
        };

        promise = fileUploadService.uploadAll(param);
      });

      afterEach(function () {
        $rootScope.$apply();
      });

      it('calls file upload service endpoint', function () {
        expect(fileUploadService.uploadAll).toHaveBeenCalledWith(param);
      });

      it('calls fileuploader endpoint', function () {
        expect(uploader.uploadAll).toHaveBeenCalledWith();
      });

      it('returns success', function () {
        promise.then(function (result) {
          var firstObject = result[0];
          expect(Array.isArray(result)).toBeTruthy();
          expect(firstObject.file).toBeDefined();
        });
      });
    });
  });
});
