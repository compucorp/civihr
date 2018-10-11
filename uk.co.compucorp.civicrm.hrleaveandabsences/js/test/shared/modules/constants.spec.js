/* eslint-env amd, jasmine */

define([
  'leave-absences/shared/modules/constants'
], function () {
  'use strict';

  describe('constants', function () {
    var absenceTypeColours;

    beforeEach(module('leave-absences.constants'));
    beforeEach(inject(['absence-type-colours', function (_absenceTypeColours_) {
      absenceTypeColours = _absenceTypeColours_;
    }]));

    describe('absence types colours', function () {
      it('contains an array of colours in uppercase hexadecimal format', function () {
        expect(absenceTypeColours).toEqual(jasmine.any(Array));
        expect(absenceTypeColours.length > 0).toBeTruthy();
        expect(absenceTypeColours.every(function (colour) {
          return /^#[A-F\d]{6}$/.test(colour);
        })).toBeTruthy();
      });
    });
  });
});
