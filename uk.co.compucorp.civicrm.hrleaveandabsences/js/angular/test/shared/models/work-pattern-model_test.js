/* eslint-env amd, jasmine */
/* global inject */

define([
  'common/lodash',
  'leave-absences/shared/models/work-pattern-model',
  'mocks/apis/work-pattern-api-mock'
], function (_) {
  'use strict';

  describe('WorkPattern', function () {
    var $provide, WorkPattern, WorkPatternAPI, $rootScope;

    beforeEach(module('leave-absences.models', 'leave-absences.mocks', 'common.mocks',
      function (_$provide_) {
        $provide = _$provide_;
      }));

    beforeEach(inject(function (_WorkPatternAPIMock_) {
      $provide.value('WorkPatternAPI', _WorkPatternAPIMock_);
    }));

    beforeEach(inject(function (_WorkPattern_, _WorkPatternAPI_, _$rootScope_) {
      WorkPattern = _WorkPattern_;
      WorkPatternAPI = _WorkPatternAPI_;
      $rootScope = _$rootScope_;

      spyOn(WorkPatternAPI, 'get').and.callThrough();
      spyOn(WorkPatternAPI, 'workPatternsOf').and.callThrough();
      spyOn(WorkPatternAPI, 'assignWorkPattern').and.callThrough();
    }));

    it('has expected interface', function () {
      expect(Object.keys(WorkPattern)).toEqual(['assignWorkPattern', 'default', 'workPatternsOf']);
    });

    describe('basic tests', function () {
      afterEach(function () {
        $rootScope.$apply();
      });

      describe('default()', function () {
        var promise;
        var additionalParams = { foo: 'bar' };

        beforeEach(function () {
          promise = WorkPattern.default(additionalParams);
        });

        it('calls equivalent API method', function () {
          promise.then(function () {
            expect(WorkPatternAPI.get).toHaveBeenCalled();
          });
        });

        it('adds "default: true" to the passed paramters', function () {
          expect(WorkPatternAPI.get.calls.mostRecent().args[0]).toEqual(_.assign({}, additionalParams, {
            default: true
          }));
        });

        it('returns model instance', function () {
          promise.then(function (response) {
            expect(response.init).toEqual(jasmine.any(Function));
          });
        });
      });

      describe('workPatternsOf()', function () {
        var promise;
        var contactId = '204';
        var additionalParams = { foo: 'bar' };

        beforeEach(function () {
          promise = WorkPattern.workPatternsOf(contactId, additionalParams);
        });

        it('calls equivalent API method', function () {
          promise.then(function () {
            expect(WorkPatternAPI.workPatternsOf).toHaveBeenCalled();
          });
        });

        it('passes all parameters to the API', function () {
          expect(WorkPatternAPI.workPatternsOf.calls.mostRecent().args[0]).toEqual(contactId);
          expect(WorkPatternAPI.workPatternsOf.calls.mostRecent().args[1]).toEqual(additionalParams);
        });

        it('returns model instances', function () {
          promise.then(function (response) {
            expect(response.every(function (modelInstance) {
              return 'init' in modelInstance;
            })).toBe(true);
          });
        });
      });

      describe('assignWorkPattern()', function () {
        var contactId = '204';
        var workPatternID = '1';
        var effectiveDate = '10/4/2017';
        var effectiveEndDate = '10/4/2018';
        var changeReason = '1';
        var additionalParams = { foo: 'bar' };

        beforeEach(function () {
          WorkPattern.assignWorkPattern(contactId, workPatternID, effectiveDate, effectiveEndDate, changeReason, additionalParams);
        });

        it('calls equivalent API method', function () {
          expect(WorkPatternAPI.assignWorkPattern).toHaveBeenCalled();
        });

        it('passes all parameters to the API', function () {
          expect(WorkPatternAPI.assignWorkPattern.calls.mostRecent().args[0]).toEqual(contactId);
          expect(WorkPatternAPI.assignWorkPattern.calls.mostRecent().args[1]).toEqual(workPatternID);
          expect(WorkPatternAPI.assignWorkPattern.calls.mostRecent().args[2]).toEqual(effectiveDate);
          expect(WorkPatternAPI.assignWorkPattern.calls.mostRecent().args[3]).toEqual(effectiveEndDate);
          expect(WorkPatternAPI.assignWorkPattern.calls.mostRecent().args[4]).toEqual(changeReason);
          expect(WorkPatternAPI.assignWorkPattern.calls.mostRecent().args[5]).toEqual(additionalParams);
        });
      });
    });
  });
});
