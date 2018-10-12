/* eslint-env amd, jasmine */

define([
  'common/lodash',
  'common/moment',
  'leave-absences/mocks/data/absence-type.data',
  'leave-absences/shared/apis/absence-type.api'
], function (_, moment, mockData) {
  'use strict';

  describe('AbsenceTypeAPI', function () {
    var AbsenceTypeAPI, $httpBackend, sharedSettings;

    beforeEach(module('leave-absences.apis', 'leave-absences.settings'));

    beforeEach(inject(['shared-settings', 'AbsenceTypeAPI', '$httpBackend',
      function (_sharedSettings_, _AbsenceTypeAPI_, _$httpBackend_) {
        AbsenceTypeAPI = _AbsenceTypeAPI_;
        $httpBackend = _$httpBackend_;
        sharedSettings = _sharedSettings_;

        spyOn(AbsenceTypeAPI, 'sendGET').and.callThrough();
      }]));

    it('has expected interface', function () {
      expect(Object.keys(AbsenceTypeAPI)).toContain('all', 'calculateToilExpiryDate');
    });

    describe('all()', function () {
      var absenceTypePromise, totalAbsenceTypes;
      var params = {};

      beforeEach(function () {
        $httpBackend.whenGET(/action=get&entity=AbsenceType/)
          .respond(mockData.all());

        totalAbsenceTypes = mockData.all().values.length;
        absenceTypePromise = AbsenceTypeAPI.all(params);
      });

      afterEach(function () {
        // enforce flush to make calls to httpBackend
        $httpBackend.flush();
      });

      it('sends GET request with correct parameters and default values', function () {
        expect(AbsenceTypeAPI.sendGET).toHaveBeenCalledWith('AbsenceType', 'get',
          { is_active: true, options: { sort: 'weight ASC' } }, undefined);
      });

      it('with expected length', function () {
        absenceTypePromise.then(function (result) {
          expect(result.length).toEqual(totalAbsenceTypes);
        });
      });

      describe('when passing no parameters', function () {
        beforeEach(function () {
          AbsenceTypeAPI.all();
        });

        it('handles the case gracefully', function () {
          expect(AbsenceTypeAPI.sendGET).toHaveBeenCalled();
        });
      });

      it('with expected data', function () {
        absenceTypePromise.then(function (result) {
          var firstAbsenceType = result[0];

          expect(firstAbsenceType.id).toBeDefined();
          expect(firstAbsenceType.title).toBeDefined();
          expect(firstAbsenceType.weight).toBeDefined();
          expect(firstAbsenceType.color).toBeDefined();
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

      describe('when passing "is_active" as false', function () {
        beforeEach(function () {
          AbsenceTypeAPI.all({ is_active: false });
        });

        it('sends GET request with correct parameters', function () {
          expect(AbsenceTypeAPI.sendGET.calls.mostRecent().args[2])
            .toEqual(jasmine.objectContaining({ is_active: false }));
        });
      });

      describe('when overriding "sort" option', function () {
        beforeEach(function () {
          AbsenceTypeAPI.all({ options: { sort: 'title' } });
        });

        it('overrides the default "sort" option value', function () {
          expect(AbsenceTypeAPI.sendGET.calls.mostRecent().args[2])
            .toEqual(jasmine.objectContaining({
              options: { sort: 'title' }
            }));
        });
      });

      describe('when passing other options', function () {
        beforeEach(function () {
          AbsenceTypeAPI.all({ options: { another: 'option' } });
        });

        it('merges options with default ones', function () {
          expect(AbsenceTypeAPI.sendGET.calls.mostRecent().args[2])
            .toEqual(jasmine.objectContaining({
              options: { sort: 'weight ASC', another: 'option' }
            }));
        });
      });
    });

    describe('calculateToilExpiryDate()', function () {
      var absenceTypePromise;
      var absenceTypeID = 2;
      var date = moment();
      var params = { key: 'value' };

      beforeEach(function () {
        spyOn(AbsenceTypeAPI, 'sendPOST').and.callThrough();
        $httpBackend.whenPOST()
          .respond(mockData.calculateToilExpiryDate());

        absenceTypePromise = AbsenceTypeAPI.calculateToilExpiryDate(absenceTypeID, date, params);
      });

      afterEach(function () {
        // enforce flush to make calls to httpBackend
        $httpBackend.flush();
      });

      it('API is called with absence type ID, date and parameters', function () {
        expect(AbsenceTypeAPI.sendPOST).toHaveBeenCalledWith('AbsenceType', 'calculateToilExpiryDate', jasmine.objectContaining(_.assign(params, {
          absence_type_id: absenceTypeID,
          date: moment(date).format(sharedSettings.serverDateFormat)
        })));
      });

      it('returns expiry_date', function () {
        absenceTypePromise.then(function (result) {
          expect(result).toEqual(mockData.calculateToilExpiryDate().values.expiry_date);
        });
      });
    });
  });
});
