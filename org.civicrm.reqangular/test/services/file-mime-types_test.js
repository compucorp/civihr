/* eslint-env amd, jasmine */

define([
  'common/angular',
  'common/angularMocks',
  'common/services/file-mime-types'
], function () {
  'use strict';

  describe('file-mime-types', function () {
    var FileMimeTypes;

    beforeEach(module('common.services'));
    beforeEach(inject(function (_FileMimeTypes_) {
      FileMimeTypes = _FileMimeTypes_;
    }));

    it('returns mime types for given extension', function () {
      expect(FileMimeTypes.getMimeTypeFor('txt')).toBe('plain');
    });
  });
});
