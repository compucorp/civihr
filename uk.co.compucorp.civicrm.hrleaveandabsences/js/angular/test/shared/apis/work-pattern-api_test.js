/* eslint-env amd, jasmine */
/* global inject */

define([
  'common/lodash',
  'mocks/data/work-pattern-data',
  'leave-absences/shared/apis/work-pattern-api'
], function (_, workPatternMocked) {
  'use strict';

  describe('WorkPatternAPI', function () {
    var WorkPatternAPI, $httpBackend;

    beforeEach(module('leave-absences.apis'));
    beforeEach(inject(function (_WorkPatternAPI_, _$httpBackend_) {
      WorkPatternAPI = _WorkPatternAPI_;
      $httpBackend = _$httpBackend_;
    }));

    afterEach(function () {
      $httpBackend.flush();
    });

    describe('getCalendar()', function () {
      var workPatternPromise;
      var dummyContactId = 1;
      var dummyPeriodId = 2;
      var additionalFilters = { foo: 'foo', bar: 'bar' };

      beforeEach(function () {
        $httpBackend.whenGET(/action=getcalendar&entity=WorkPattern/).respond(workPatternMocked.getCalendar);
        spyOn(WorkPatternAPI, 'sendGET').and.callThrough();
      });

      describe('basic tests', function () {
        beforeEach(function () {
          workPatternPromise = WorkPatternAPI.getCalendar(dummyContactId, dummyPeriodId, additionalFilters);
        });

        it('sends a GET request to the api', function () {
          expect(WorkPatternAPI.sendGET).toHaveBeenCalled();
        });

        it('calls the api with the correct entity and action', function () {
          expect(WorkPatternAPI.sendGET.calls.mostRecent().args[0]).toBe('WorkPattern');
          expect(WorkPatternAPI.sendGET.calls.mostRecent().args[1]).toBe('getcalendar');
        });

        it('passes the contact id and period id to the api', function () {
          expect(WorkPatternAPI.sendGET.calls.mostRecent().args[2]).toEqual(jasmine.objectContaining({
            contact_id: dummyContactId,
            period_id: dummyPeriodId
          }));
        });

        it('adds the additional filters to the request', function () {
          var requestFilters = Object.keys(WorkPatternAPI.sendGET.calls.mostRecent().args[2]);

          Object.keys(additionalFilters).map(function (filterKey) {
            expect(requestFilters).toContain(filterKey);
          });
        });

        it('returns the calendar data', function () {
          workPatternPromise.then(function (response) {
            expect(response).toEqual(workPatternMocked.getCalendar);
          });
        });
      });

      describe('when multiple contact ids are passed', function () {
        var multipleContacts = [dummyContactId, dummyContactId + 1];

        beforeEach(function () {
          WorkPatternAPI.getCalendar(multipleContacts, jasmine.any(Number));
        });

        it('passes the contact ids as an "IN" filter', function () {
          expect(WorkPatternAPI.sendGET.calls.mostRecent().args[2]).toEqual(jasmine.objectContaining({
            contact_id: { 'IN': multipleContacts }
          }));
        });
      });
    });
  });
});
