define([
  'common/lodash',
  'common/moment',
  'mocks/data/absence-type-data',
  'leave-absences/shared/apis/absence-type-api',
  ],
  function (_, moment, mockData) {
    'use strict'

    describe("AbsenceTypeAPI", function () {
      var AbsenceTypeAPI, $httpBackend, sharedSettings;

      beforeEach(module('leave-absences.apis', 'leave-absences.settings'));

      beforeEach(inject(['shared-settings', 'AbsenceTypeAPI', '$httpBackend',
        function (_sharedSettings_, _AbsenceTypeAPI_, _$httpBackend_) {
        AbsenceTypeAPI = _AbsenceTypeAPI_;
        $httpBackend = _$httpBackend_;
        sharedSettings = _sharedSettings_;
      }]));

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

      describe('calculateToilExpiryDate()', function() {
        var absenceTypePromise,
          absenceTypeID = 2,
          date = moment(),
          params = {
            key: 'value'
          };

        beforeEach(function () {
          spyOn(AbsenceTypeAPI, 'sendPOST').and.callThrough();
          $httpBackend.whenPOST()
            .respond(mockData.calculateToilExpiryDate());

          absenceTypePromise = AbsenceTypeAPI.calculateToilExpiryDate(absenceTypeID, date, params);
        });

        afterEach(function () {
          //enforce flush to make calls to httpBackend
          $httpBackend.flush();
        });

        it("API is called with absence type ID, date and parameters", function () {
          expect(AbsenceTypeAPI.sendPOST).toHaveBeenCalledWith('AbsenceType', 'calculateToilExpiryDate', _.assign(params, {
            absence_type_id: absenceTypeID,
            date: moment(date).format(sharedSettings.serverDateFormat)
          }))
        });

        it("returns expiry_date", function () {
          absenceTypePromise.then(function (result) {
            expect(result).toEqual(mockData.calculateToilExpiryDate().values.expiry_date);
          });
        });
      });
    });
});
