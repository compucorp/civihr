define([
  'common/angular',
  'common/angularMocks',
  'common/services/file-upload',
  'common/mocks/services/file-uploader-mock',
], function () {
  'use strict';

  describe('FileUpload', function () {
    var $provide, $rootScope, $q, fileUpload, uploader, promise,
      uploaderParams = {
        entityTable: 'civicrm_hrleaveandabsences_leave_request',
        crmAttachmentToken: '123abc'
      };

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
        uploader = fileUpload.uploader(uploaderParams);
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
        uploader = fileUpload.uploader(uploaderParams);
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
        var testItem = { formData: [] };

        beforeEach(function () {
          uploader = fileUpload.uploader(uploaderParams);

          spyOn(uploader, 'onBeforeUploadItem').and.callThrough();
          spyOn(uploader, 'uploadAll').and.callFake(function () {
            uploader.onBeforeUploadItem(testItem);
            return $q.resolve({});
          });

          promise = uploader.uploadAll(param);
        });

        afterEach(function () {
          $rootScope.$apply();
        });

        it('gets called', function () {
          promise.then(function () {
            expect(uploader.onBeforeUploadItem).toHaveBeenCalledWith(testItem);
          });
        });
      });

      describe('onErrorItem()', function () {
        var fileItem = { file: { name: 'filename' } };

        beforeEach(function () {
          uploader = fileUpload.uploader(uploaderParams);

          spyOn(uploader, 'onErrorItem').and.callThrough();
          spyOn(uploader, 'uploadAll').and.callFake(function () {
            uploader.onErrorItem(fileItem);
            return $q.resolve({});
          });
          promise = uploader.uploadAll(param);
        });

        afterEach(function () {
          $rootScope.$apply();
        });

        it('gets called', function () {
          promise.then(function () {
            expect(uploader.onErrorItem).toHaveBeenCalledWith(fileItem);
          });
        });
      });
    });
  });
});
