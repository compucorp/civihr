/* eslint-env amd, jasmine */

define([
  'leave-absences/shared/modules/constants'
], function () {
  'use strict';

  describe('constants', function () {
    var ABSENCE_TYPE_COLOURS;

    beforeEach(module('leave-absences.constants'));
    beforeEach(inject(['ABSENCE_TYPE_COLOURS', function (_ABSENCE_TYPE_COLOURS_) {
      ABSENCE_TYPE_COLOURS = _ABSENCE_TYPE_COLOURS_;
    }]));

    describe('absence types colours', function () {
      it('contains an array of colours in uppercase hexadecimal format', function () {
        expect(ABSENCE_TYPE_COLOURS).toEqual(jasmine.any(Array));
        expect(ABSENCE_TYPE_COLOURS.length > 0).toBeTruthy();
        expect(ABSENCE_TYPE_COLOURS.every(function (colour) {
          return /^#[A-F\d]{6}$/.test(colour);
        })).toBeTruthy();
      });
    });
  });
});
