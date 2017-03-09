define([
  'common/angular',
  'common/angularMocks',
  'common/services/file-upload',
  'common/mocks/services/file-uploader-mock',
], function () {
  'use strict';

  describe('FileUpload', function () {
    var $provide, $rootScope, fileUpload, uploader, promise;

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

    beforeEach(inject(function (_$rootScope_) {
      $rootScope = _$rootScope_;
    }));

    it('has all endpoints', function () {
      expect(Object.keys(fileUpload)).toContain('uploader', 'uploadAll');
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
          expect(fileUpload.uploader).toThrow('custom settings object need to be defined in parameter');
        });

        it('throws error if entityTable is not defined', function () {
          expect(function () { fileUpload.uploader({ crmAttachmentToken: '123abc' }) })
            .toThrow('entityTable missing from custom settings');
        });

        it('throws error if crmAttachmentToken is not defined', function () {
          expect(function () { fileUpload.uploader({ entityTable: 'civicrm_hrleaveandabsences_leave_request' }) })
            .toThrow('crmAttachmentToken missing from custom settings');
        });
      });
    });

    describe('uploadAll()', function () {
      var param;

      beforeEach(function () {
        spyOn(uploader, 'uploadAll').and.callThrough();
        param = { entityID: '12' };

        promise = fileUpload.uploadAll(uploader, param);
      });

      afterEach(function () {
        $rootScope.$apply();
      });

      it('calls fileuploader endpoint to upload all', function () {
        expect(uploader.uploadAll).toHaveBeenCalled();
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
    });
  });
});
