define([
  'mocks/data/absence-type-data',
  'leave-absences/shared/apis/absence-type-api',
  ],
  function (mockData) {
    'use strict'

    describe("AbsenceTypeAPI", function () {
      var AbsenceTypeAPI, $httpBackend;

      beforeEach(module('leave-absences.apis'));

      beforeEach(inject(function (_AbsenceTypeAPI_, _$httpBackend_) {
        AbsenceTypeAPI = _AbsenceTypeAPI_;
        $httpBackend = _$httpBackend_;
      }));

      it("has expected interface", function () {
        expect(Object.keys(AbsenceTypeAPI)).toContain("all");
      });

      describe("all()", function () {
        var absenceTypePromise, totalAbsenceTypes;

        beforeEach(function () {
          $httpBackend.whenGET(/action=get&entity=AbsenceType/)
            .respond(mockData.all());
        })

        beforeEach(function () {
          totalAbsenceTypes = mockData.all().values.length;
          absenceTypePromise = AbsenceTypeAPI.all();
        });

        afterEach(function () {
          //enforce flush to make calls to httpBackend
          $httpBackend.flush();
        });

        it("with expected length", function () {
          absenceTypePromise.then(function (result) {
            expect(result.length).toEqual(totalAbsenceTypes);
          });
        });

        it("with expected data", function () {
          absenceTypePromise.then(function (result) {
            var firstAbsenceType = result[0];

            expect(firstAbsenceType.id).toBeDefined();
            expect(firstAbsenceType.title).toBeDefined();
            expect(firstAbsenceType.weight).toBeDefined();
            expect(firstAbsenceType.color).toBeDefined();
            expect(firstAbsenceType.is_default).toBeDefined();
            expect(firstAbsenceType.is_reserved).toBeDefined();
            expect(firstAbsenceType.allow_request_cancelation).toBeDefined();
            expect(firstAbsenceType.allow_overuse).toBeDefined();
            expect(firstAbsenceType.must_take_public_holiday_as_leave).toBeDefined();
            expect(firstAbsenceType.default_entitlement).toBeDefined();
            expect(firstAbsenceType.add_public_holiday_to_entitlement).toBeDefined();
            expect(firstAbsenceType.is_active).toBeDefined();
            expect(firstAbsenceType.allow_accruals_request).toBeDefined();
            expect(firstAbsenceType.allow_accrue_in_the_past).toBeDefined();
            expect(firstAbsenceType.allow_carry_forward).toBeDefined();
          });
        });
      });
    });
});
