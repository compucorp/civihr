define([
  'mocks/data/absence-period-data',
  'common/moment',
  'leave-absences/shared/apis/absence-period-api',
], function (mockData, moment) {
  'use strict'

  describe("AbsencePeriodAPI", function () {
    var AbsencePeriodAPI, $httpBackend;

    beforeEach(module('leave-absences.apis'));

    beforeEach(inject(function (_AbsencePeriodAPI_, _$httpBackend_) {
      AbsencePeriodAPI = _AbsencePeriodAPI_;
      $httpBackend = _$httpBackend_;
    }));

    it("has expected interface", function () {
      expect(Object.keys(AbsencePeriodAPI)).toContain("all");
    });

    describe("all()", function () {
      var absenceTypePromise, totalAbsencePeriods, dateFormat = 'YYYY-MM-DD';

      beforeEach(function () {
        $httpBackend.whenGET(/action=get&entity=AbsencePeriod/)
          .respond(mockData.all());
      })

      beforeEach(function () {
        totalAbsencePeriods = mockData.all().values.length;
        absenceTypePromise = AbsencePeriodAPI.all();
      });

      afterEach(function () {
        //enforce flush to make calls to httpBackend
        $httpBackend.flush();
      });

      it("returns all the absence periods", function () {
        absenceTypePromise.then(function (result) {
          expect(result.length).toEqual(totalAbsencePeriods);
        });
      });

      it("returns absence period with all attributes keys", function () {
        absenceTypePromise.then(function (result) {
          var firstAbsencePeriod = result[0];

          expect(firstAbsencePeriod.id).toBeDefined();
          expect(firstAbsencePeriod.title).toBeDefined();
          expect(firstAbsencePeriod.start_date).toBeDefined();
          expect(firstAbsencePeriod.end_date).toBeDefined();
          expect(firstAbsencePeriod.weight).toBeDefined();
        });
      });

      it("returns absence period with all attributes values", function () {
        absenceTypePromise.then(function (result) {
          var firstAbsencePeriod = result[0];

          expect(firstAbsencePeriod.id).toEqual(jasmine.any(String));
          expect(firstAbsencePeriod.title).toEqual(jasmine.any(String));
          expect(moment(firstAbsencePeriod.start_date, dateFormat, true).isValid()).toBe(true);
          expect(moment(firstAbsencePeriod.end_date, dateFormat, true).isValid()).toBe(true);
          expect(firstAbsencePeriod.weight).toEqual(jasmine.any(String));
        });
      });
    });
  });
});
