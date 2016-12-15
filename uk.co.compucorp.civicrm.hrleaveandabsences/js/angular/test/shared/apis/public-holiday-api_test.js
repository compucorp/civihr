define([
  'mocks/data/public-holiday-data',
  'common/moment',
  'leave-absences/shared/apis/public-holiday-api',
], function (mockData, moment) {
  'use strict'

  describe("PublicHolidayAPI", function () {
    var PublicHolidayAPI, $httpBackend;

    beforeEach(module('leave-absences.apis'));

    beforeEach(inject(function (_PublicHolidayAPI_, _$httpBackend_) {
      PublicHolidayAPI = _PublicHolidayAPI_;
      $httpBackend = _$httpBackend_;
    }));

    it("has expected interface", function () {
      expect(Object.keys(PublicHolidayAPI)).toContain("all");
    });

    describe("all()", function () {
      var promise, totalPublicHolidays, dateFormat = 'YYYY-MM-DD';

      beforeEach(function () {
        $httpBackend.whenGET(/action=get&entity=PublicHoliday/)
          .respond(mockData.all());
      })

      beforeEach(function () {
        totalPublicHolidays = mockData.all().values.length;
        promise = PublicHolidayAPI.all();
      });

      afterEach(function () {
        //enforce flush to make calls to httpBackend
        $httpBackend.flush();
      });

      it("returns all public holidays", function () {
        promise.then(function (result) {
          expect(result.length).toEqual(totalPublicHolidays);
        });
      });

      it("returns public holiday with all attributes keys", function () {
        promise.then(function (result) {
          var firstPublicHoliday = result[0];

          expect(firstPublicHoliday.id).toBeDefined();
          expect(firstPublicHoliday.title).toBeDefined();
          expect(firstPublicHoliday.date).toBeDefined();
          expect(firstPublicHoliday.is_active).toBeDefined();
        });
      });

      it("returns public holiday with all attributes values", function () {
        promise.then(function (result) {
          var firstPublicHoliday = result[0];

          expect(firstPublicHoliday.id).toEqual(jasmine.any(String));
          expect(firstPublicHoliday.title).toEqual(jasmine.any(String));
          expect(moment(firstPublicHoliday.date, dateFormat, true).isValid()).toBe(true);
          expect(firstPublicHoliday.is_active).toEqual(jasmine.any(String));
        });
      });
    });
  });
});
