/* eslint-env amd, jasmine */

define([
  'common/angularMocks',
  'common/services/file-mime-types'
], function () {
  'use strict';

  describe('file-mime-types', function () {
    var fileMimeTypes;

    beforeEach(module('common.services'));
    beforeEach(inject(function (_fileMimeTypes_) {
      fileMimeTypes = _fileMimeTypes_;
    }));

    describe('getMimeTypeFor()', function () {
      var promise;

      beforeEach(function () {
        promise = fileMimeTypes.getMimeTypeFor('txt');
      });

      it('returns mime types for given file extension', function () {
        promise.then(function (mimeType) {
          expect(mimeType).toBe('plain');
        });
      });
    });
  });
});
