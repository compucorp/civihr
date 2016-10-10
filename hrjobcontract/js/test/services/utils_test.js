define([
  'common/angular',
  'common/angularMocks',
  'job-contract/app'
], function (angular, Mock) {
  'use strict';

  describe('UtilsService', function () {
    var UtilsService, API;

    beforeEach(module('hrjc'));
    beforeEach(inject(function (_UtilsService_, _API_) {
      UtilsService = _UtilsService_;
      API = _API_;
    }));

    beforeEach(function () {
      spyOn(API, 'resource').and.callFake(function () { return { get: function () {} } })
    });

    describe('getAbsenceType', function () {
      beforeEach(function () {
        UtilsService.getAbsenceType();
      });

      it("returns the id, name, and title of the absence types", function () {
        expect(API.resource).toHaveBeenCalledWith('HRAbsenceType', 'get', { return: 'id,name,title' });
      });
    });

    describe('getHoursLocation', function () {
      beforeEach(function () {
        UtilsService.getHoursLocation();
      });

      it("returns only the active hours/location entries", function () {
        expect(API.resource).toHaveBeenCalledWith('HRHoursLocation', 'get',  { sequential: 1, is_active: 1 });
      });
    });

    describe('getPayScaleGrade', function () {
      beforeEach(function () {
        UtilsService.getPayScaleGrade();
      });

      it("returns only the active pay scale entries", function () {
        expect(API.resource).toHaveBeenCalledWith('HRPayScale', 'get', { sequential: 1, is_active: 1 });
      });
    });
  });
});
