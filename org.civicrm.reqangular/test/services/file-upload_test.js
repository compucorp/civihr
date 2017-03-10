define([
  'common/angular',
  'common/angularMocks',
  'common/services/file-upload',
  'common/mocks/services/file-uploader-mock',
], function () {
  'use strict';

  describe('FileUpload', function () {
    var $provide, $rootScope, $q, fileUpload, uploader, promise;

    beforeEach(module('common.services', 'common.mocks', function (_$provide_) {
      $provide = _$provide_;
    }));

    beforeEach(inject(function (_FileUploaderMock_) {
      $provide.value('FileUploader', _FileUploaderMock_);
    }));

    beforeEach(inject(['FileUpload',
      function (_FileUpload_) {
        fileUpload = _FileUpload_;
      }
    ]));

    beforeEach(inject(function (_$rootScope_, _$q_) {
      $rootScope = _$rootScope_;
      $q = _$q_;
    }));

    it('has all endpoints', function () {
      expect(Object.keys(fileUpload)).toContain('uploader');
    });

    describe('uploader()', function () {
      beforeEach(function () {
        uploader = fileUpload.uploader({
          entityTable: 'civicrm_hrleaveandabsences_leave_request',
          crmAttachmentToken: '123abc'
        });
      });

      it('creates uploader', function () {
        expect(uploader).toBeDefined();
      });

      describe('missing params', function () {
        it('throws error if no param is passed', function () {
          expect(fileUpload.uploader).toThrow('custom settings missing from parameter');
        });

        it('throws error if entityTable is not defined', function () {
          expect(function () { fileUpload.uploader({ crmAttachmentToken: '123abc' }) })
            .toThrow('entityTable missing from parameter');
        });

        it('throws error if crmAttachmentToken is not defined', function () {
          expect(function () { fileUpload.uploader({ entityTable: 'civicrm_hrleaveandabsences_leave_request' }) })
            .toThrow('crmAttachmentToken missing from parameter');
        });
      });
    });

    describe('uploading all files', function () {
      var param = { entityID: '12' };

      beforeEach(function () {
        uploader = fileUpload.uploader({
          entityTable: 'civicrm_hrleaveandabsences_leave_request',
          crmAttachmentToken: '123abc'
        });

        promise = uploader.uploadAll(param);
      });

      afterEach(function () {
        $rootScope.$apply();
      });

      it('returns array of success objects', function () {
        promise.then(function (result) {
          expect(Array.isArray(result)).toBeTruthy();
        });
      });

      it('returns object with file key', function () {
        promise.then(function (result) {
          var firstObject = result[0];
          expect(firstObject.file).toBeDefined();
        });
      });

      describe('onBeforeUploadItem()', function () {
        beforeEach(function () {
          spyOn(uploader, 'onBeforeUploadItem').and.callThrough();
          uploader.onBeforeUploadItem({ formData: [] });
        });

        it('gets called', function () {
          //verify if method is getting called and hence a simple case
          expect(uploader.onBeforeUploadItem).toHaveBeenCalled();
        });
      });

      describe('onErrorItem()', function () {
        var fileItem = { file: { name: 'filename' } };

        beforeEach(function () {
          spyOn(uploader, 'onErrorItem').and.callThrough();
          promise = uploader.onErrorItem(fileItem);
        });

        it('gets called', function () {
          //verify if method is getting called and hence a simple case
          expect(uploader.onErrorItem).toHaveBeenCalled();
        });
      });
    });
  });
});
