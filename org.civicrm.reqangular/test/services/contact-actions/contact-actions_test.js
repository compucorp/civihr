/* eslint angular/di: 0, jasmine/no-spec-dupes: 0 */

define([
  'common/angularMocks',
  'common/services/api/contact-actions'
], function () {
  'use strict';

  describe('ContactActions API', function () {
    var apiSpy;

    beforeEach(module('common.apis', function ($provide) {
      apiSpy = jasmine.createSpyObj('apiSpy', ['extend', 'sendPOST']);
      $provide.value('api', apiSpy);
    }));

    beforeEach(inject(function ($injector, $q) {
      apiSpy.extend.and.returnValue({});
      apiSpy.sendPOST.and.returnValue(Promise.resolve({
        values: ['test']
      }));
      $injector.get('api.contactActions');
    }));

    it('calls api.extend with correct parameters', function () {
      expect(apiSpy.extend.calls.count()).toBe(1);
      expect(apiSpy.extend.calls.mostRecent().args.length).toBe(1);
      expect('saveNewIndividual' in apiSpy.extend.calls.mostRecent().args[0]).toBeTruthy();
      expect('saveNewOrganization' in apiSpy.extend.calls.mostRecent().args[0]).toBeTruthy();
      expect('saveNewHousehold' in apiSpy.extend.calls.mostRecent().args[0]).toBeTruthy();
    });

    describe('saveNewIndividual', function () {
      var result;
      beforeEach(function () {
        result = apiSpy.extend.calls.mostRecent().args[0].saveNewIndividual.call(apiSpy,
          'First Name', 'Last Name', 'Email');
      });

      it('returns the correct result', function (done) {
        result.then(function(data) {
          expect(data).toEqual('test');
          done();
        });
      });

      it('calls api.sendPOST correctly', function () {
        expect(apiSpy.sendPOST.calls.count()).toBe(1);
        expect(apiSpy.sendPOST.calls.argsFor(0)).toEqual(['Contact', 'create', {
          first_name: 'First Name',
          last_name: 'Last Name',
          custom_100003: 'Email',
          contact_type: 'Individual'
        }]);
      });
    });

    describe('saveNewOrganization', function () {
      var result;
      beforeEach(function () {
        result = apiSpy.extend.calls.mostRecent().args[0].saveNewOrganization.call(apiSpy,
          'Organization Name', 'Email');
      });

      it('returns the correct result', function (done) {
        result.then(function(data) {
          expect(data).toEqual('test');
          done();
        });
      });

      it('calls api.sendPOST correctly', function () {
        expect(apiSpy.sendPOST.calls.count()).toBe(1);
        expect(apiSpy.sendPOST.calls.argsFor(0)).toEqual(['Contact', 'create', {
          organization_name: 'Organization Name',
          custom_100003: 'Email',
          contact_type: 'Organization'
        }]);
      });
    });

    describe('saveNewHousehold', function () {
      var result;
      beforeEach(function () {
        result = apiSpy.extend.calls.mostRecent().args[0].saveNewHousehold.call(apiSpy,
          'Household Name', 'Email');
      });

      it('returns the correct result', function (done) {
        result.then(function(data) {
          expect(data).toEqual('test');
          done();
        });
      });

      it('calls api.sendPOST correctly', function () {
        expect(apiSpy.sendPOST.calls.count()).toBe(1);
        expect(apiSpy.sendPOST.calls.argsFor(0)).toEqual(['Contact', 'create', {
          household_name: 'Household Name',
          custom_100003: 'Email',
          contact_type: 'Household'
        }]);
      });
    });
  });
});
