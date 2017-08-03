/* eslint-env amd, jasmine */

(function (CRM) {
  define([
    'common/angular',
    'common/angularMocks',
    'common/services/file.service'
  ], function () {
    'use strict';

    describe('fileService', function () {
      var $httpBackend, fileService, promise, file, blob, $window;

      beforeEach(module('common.services'));
      beforeEach(inject(function (_$httpBackend_, _fileService_, _$window_) {
        fileService = _fileService_;
        $httpBackend = _$httpBackend_;
        $window = _$window_;
        file = {
          'name': 'exampleFile',
          'url': 'test/file',
          'fileType': 'image/png'
        };

        $httpBackend.whenGET().respond(['binaryFile']);
      }));

      describe('openFile()', function () {
        beforeEach(function () {
          spyOn($window, 'open').and.callThrough();
          blob = new Blob(['binaryFile'], { type: file.fileType });
          promise = fileService.openFile(file);
        });

        afterEach(function () {
          $httpBackend.flush();
        });

        it('calls CRM.alert to display alert notification', function () {
          promise.then(function (fileBlob) {
            expect(fileBlob).toEqual(blob);
            expect($window.open).toHaveBeenCalled();
          });
        });
      });
    });
  });
})(CRM);
