define([
    'mocks/data/absence-period-data',
    'leave-absences/shared/apis/absence-period-api',
  ],
  function (mockData) {
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

        it("with expected length", function () {
          absenceTypePromise.then(function (result) {
            expect(result.length).toEqual(totalAbsencePeriods);
          });
        });

        it("with expected data", function () {
          absenceTypePromise.then(function (result) {
            var firstAbsencePeriod = result[0];

            expect(firstAbsencePeriod.id).toBeDefined();
            expect(firstAbsencePeriod.title).toBeDefined();
            expect(firstAbsencePeriod.start_date).toBeDefined();
            expect(firstAbsencePeriod.end_date).toBeDefined();
            expect(firstAbsencePeriod.weight).toBeDefined();
          });
        });
      });
    });
  });
