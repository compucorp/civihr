define([
  'leave-absences/shared/models/absence-type-model',
  'mocks/apis/absence-type-api-mock',
], function () {
  'use strict'

  describe('AbsenceType', function () {
    var $provide, AbsenceType, AbsenceTypeAPI, $rootScope;

    beforeEach(module('leave-absences.models', 'leave-absences.mocks', function (_$provide_) {
      $provide = _$provide_;
    }));

    beforeEach(inject(function (_AbsenceTypeAPIMock_) {
      $provide.value('AbsenceTypeAPI', _AbsenceTypeAPIMock_);
    }));

    beforeEach(inject(function (_AbsenceType_, _AbsenceTypeAPI_, _$rootScope_) {
      AbsenceType = _AbsenceType_;
      AbsenceTypeAPI = _AbsenceTypeAPI_;
      $rootScope = _$rootScope_;

      spyOn(AbsenceTypeAPI, 'all').and.callThrough();
    }));

    it('has expected interface', function () {
      expect(Object.keys(AbsenceType)).toEqual(['all']);
    });

    describe('all()', function () {
      var absenceTypePromise;

      beforeEach(function () {
        absenceTypePromise = AbsenceType.all();
      });

      afterEach(function () {
        //to excute the promise force an digest
        $rootScope.$apply();
      });

      it('calls equivalent API method', function () {
        absenceTypePromise.then(function (response) {
          expect(AbsenceTypeAPI.all).toHaveBeenCalled();
        });
      });

      it('returns model instances', function () {
        absenceTypePromise.then(function (response) {
          expect(response.every(function (modelInstance) {
            return 'init' in modelInstance;
          })).toBe(true);
        });
      });
    });
  });
});
