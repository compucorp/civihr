/* eslint-env amd, jasmine */
/* global inject */

define([
  'common/lodash',
  'mocks/data/work-pattern-data',
  'leave-absences/shared/apis/work-pattern-api'
], function (_, workPatternMocked) {
  'use strict';

  describe('WorkPatternAPI', function () {
    var $q, WorkPatternAPI, $httpBackend;

    beforeEach(module('leave-absences.apis'));
    beforeEach(inject(function (_$q_, _WorkPatternAPI_, _$httpBackend_) {
      $q = _$q_;
      WorkPatternAPI = _WorkPatternAPI_;
      $httpBackend = _$httpBackend_;
    }));

    describe('assignWorkPattern()', function () {
      var contactId = '204';
      var workPatternID = '1';
      var effectiveDate = '02/01/2017';
      var effectiveEndDate = '02/01/2018';
      var changeReason = '2';
      var additionalFilters = { foo: 'foo', bar: 'bar' };

      beforeEach(function () {
        spyOn(WorkPatternAPI, 'sendPOST').and.returnValue($q.resolve({ values: [] }));
        WorkPatternAPI.assignWorkPattern(contactId, workPatternID, effectiveDate, effectiveEndDate, changeReason, additionalFilters);
      });

      it('sends a GET request to the api', function () {
        expect(WorkPatternAPI.sendPOST).toHaveBeenCalled();
      });

      it('calls the api with the correct entity and action', function () {
        expect(WorkPatternAPI.sendPOST.calls.mostRecent().args[0]).toBe('ContactWorkPattern');
        expect(WorkPatternAPI.sendPOST.calls.mostRecent().args[1]).toBe('create');
      });

      it('passes all the parameters to the api', function () {
        expect(WorkPatternAPI.sendPOST.calls.mostRecent().args[2]).toEqual(_.assign(additionalFilters, {
          contact_id: contactId,
          pattern_id: workPatternID,
          effective_date: effectiveDate,
          effective_end_date: effectiveEndDate,
          change_reason: changeReason
        }));
      });
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

      afterEach(function () {
        $httpBackend.flush();
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

    describe('get()', function () {
      var getWorkPatternPromise;
      var additionalFilters = { foo: 'foo', bar: 'bar' };

      beforeEach(function () {
        $httpBackend.whenGET(/action=get&entity=WorkPattern/).respond(workPatternMocked.getAllWorkPattern);
        spyOn(WorkPatternAPI, 'sendGET').and.callThrough();
        getWorkPatternPromise = WorkPatternAPI.get(additionalFilters);
      });

      afterEach(function () {
        $httpBackend.flush();
      });

      it('sends a GET request to the api', function () {
        expect(WorkPatternAPI.sendGET).toHaveBeenCalled();
      });

      it('calls the api with the correct entity and action', function () {
        expect(WorkPatternAPI.sendGET.calls.mostRecent().args[0]).toBe('WorkPattern');
        expect(WorkPatternAPI.sendGET.calls.mostRecent().args[1]).toBe('get');
      });

      it('returns the work pattern data', function () {
        getWorkPatternPromise.then(function (response) {
          expect(response).toEqual(workPatternMocked.getAllWorkPattern.values);
        });
      });
    });

    describe('workPatternsOf()', function () {
      var getWorkPatternPromise;
      var contactId = '204';
      var additionalFilters = { foo: 'foo', bar: 'bar' };
      var cache = false;

      beforeEach(function () {
        $httpBackend.whenGET(/action=get&entity=ContactWorkPattern/).respond(workPatternMocked.workPatternsOf);
        spyOn(WorkPatternAPI, 'sendGET').and.callThrough();
        getWorkPatternPromise = WorkPatternAPI.workPatternsOf(contactId, additionalFilters, cache);
      });

      afterEach(function () {
        $httpBackend.flush();
      });

      it('sends a GET request to the api', function () {
        expect(WorkPatternAPI.sendGET).toHaveBeenCalled();
      });

      it('calls the api with the correct entity and action', function () {
        expect(WorkPatternAPI.sendGET.calls.mostRecent().args[0]).toBe('ContactWorkPattern');
        expect(WorkPatternAPI.sendGET.calls.mostRecent().args[1]).toBe('get');
        expect(WorkPatternAPI.sendGET.calls.mostRecent().args[3]).toBe(cache);
      });

      it('returns the work pattern data', function () {
        getWorkPatternPromise.then(function (response) {
          expect(response).toEqual(workPatternMocked.workPatternsOf.values.map(storeWorkPattern));
        });
      });

      it('passes contact id to the api', function () {
        expect(WorkPatternAPI.sendGET.calls.mostRecent().args[2]).toEqual(jasmine.objectContaining({
          contact_id: contactId
        }));
      });

      it('chains call to WorkPattern API', function () {
        expect(WorkPatternAPI.sendGET.calls.mostRecent().args[2]).toEqual(jasmine.objectContaining({
          'api.WorkPattern.get': { 'contact_id': '$value.contact_id' }
        }));
      });

      /**
       * ContactWorkPatterns data will have key 'api.WorkPattern.get'
       * which is normalized with a friendlier 'workPatterns' key
       *
       * @param  {Object} workPattern
       * @return {Object}
       */
      function storeWorkPattern (workPattern) {
        var clone = _.clone(workPattern);

        clone['workPattern'] = clone['api.WorkPattern.get']['values'][0];
        delete clone['api.WorkPattern.get'];

        return clone;
      }
    });

    describe('unassignWorkPattern()', function () {
      var contactWorkPatternID = '2';

      beforeEach(function () {
        spyOn(WorkPatternAPI, 'sendPOST').and.returnValue($q.resolve({ values: [] }));
        WorkPatternAPI.unassignWorkPattern(contactWorkPatternID);
      });

      it('sends a POST request to the api', function () {
        expect(WorkPatternAPI.sendPOST).toHaveBeenCalled();
      });

      it('calls the api with the correct entity and action', function () {
        expect(WorkPatternAPI.sendPOST.calls.mostRecent().args[0]).toBe('ContactWorkPattern');
        expect(WorkPatternAPI.sendPOST.calls.mostRecent().args[1]).toBe('delete');
      });

      it('passes all the parameters to the api', function () {
        expect(WorkPatternAPI.sendPOST.calls.mostRecent().args[2]).toEqual({
          id: contactWorkPatternID
        });
      });
    });
  });
});
