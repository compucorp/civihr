/* eslint-env amd, jasmine */

define([
  'common/angularMocks',
  'common/services/file-mime-types'
], function () {
  'use strict';

  describe('file-mime-types', function () {
    var fileMimeTypes, $rootScope;

    beforeEach(module('common.services'));
    beforeEach(inject(function (_$rootScope_, _fileMimeTypes_) {
      fileMimeTypes = _fileMimeTypes_;
      $rootScope = _$rootScope_;
    }));

    describe('getMimeTypeFor()', function () {
      var promise;

      beforeEach(function () {
        promise = fileMimeTypes.getMimeTypeFor('txt');
      });

      afterEach(function () {
        $rootScope.$digest();
      });

      it('returns mime types for given file extension', function () {
        promise.then(function (mimeType) {
          expect(mimeType).toBe('plain');
        });
      });
    });
  });
});
