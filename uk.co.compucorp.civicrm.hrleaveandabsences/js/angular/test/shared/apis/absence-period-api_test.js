define([
  'mocks/data/absence-period-data',
  'common/moment',
  'leave-absences/shared/apis/absence-period-api',
  'leave-absences/shared/modules/shared-settings',
], function (mockData, moment) {
  'use strict'

  describe("AbsencePeriodAPI", function () {
    var AbsencePeriodAPI, $httpBackend, sharedSettings;

    beforeEach(module('leave-absences.apis', 'leave-absences.settings'));

    beforeEach(inject(['AbsencePeriodAPI', '$httpBackend', 'shared-settings',
      function (_AbsencePeriodAPI_, _$httpBackend_, _sharedSettings_) {
      AbsencePeriodAPI = _AbsencePeriodAPI_;
      $httpBackend = _$httpBackend_;
      sharedSettings = _sharedSettings_;
    }]));

    it("has expected interface", function () {
      expect(Object.keys(AbsencePeriodAPI)).toContain("all");
    });

    describe("all()", function () {
      var absenceTypePromise, totalAbsencePeriods;

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
          expect(moment(firstAbsencePeriod.start_date, sharedSettings.serverDateFormat, true).isValid()).toBe(true);
          expect(moment(firstAbsencePeriod.end_date, sharedSettings.serverDateFormat, true).isValid()).toBe(true);
          expect(firstAbsencePeriod.weight).toEqual(jasmine.any(String));
        });
      });
    });
  });
});
