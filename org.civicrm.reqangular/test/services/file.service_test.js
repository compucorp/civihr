/* eslint-env amd, jasmine */

(function (CRM) {
  define([
    'common/angular',
    'common/angularMocks',
    'common/services/file.service'
  ], function () {
    'use strict';

    describe('fileService', function () {
      var $httpBackend, $http, $window, fileService, promise, file;

      beforeEach(module('common.services'));
      beforeEach(inject(function (_$httpBackend_, _$http_, _$window_, _fileService_) {
        $httpBackend = _$httpBackend_;
        $window = _$window_;
        $http = _$http_;
        fileService = _fileService_;
        file = {
          'name': 'exampleFile',
          'url': 'test/file',
          'fileType': 'image/png'
        };

        $httpBackend.whenGET().respond(['binaryFile']);
      }));

      describe('openFile()', function () {
        beforeEach(function () {
          spyOn($http, 'get').and.callThrough();
          spyOn($window, 'open').and.callThrough();

          promise = fileService.openFile(file);
        });

        afterEach(function () {
          $httpBackend.flush();
        });

        it('calls $http.get to to get a file', function () {
          expect($http.get).toHaveBeenCalledWith('test/file', { responseType: 'arraybuffer' });
        });

        it('calls window.open to open blob in new tab', function () {
          promise.then(function () {
            expect($window.open).toHaveBeenCalled();
          });
        });
      });
    });
  });
})(CRM);
