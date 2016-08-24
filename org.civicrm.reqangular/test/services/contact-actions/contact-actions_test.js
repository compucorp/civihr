/* eslint angular/di: 0, jasmine/no-spec-dupes: 0 */

define([
  'common/angularMocks',
  'common/services/api/contact-actions'
], function () {
  'use strict';

  describe('ContactActions API', function () {
    var apiSpy;

    beforeEach(module('common.apis', function ($provide) {
      apiSpy = jasmine.createSpyObj('apiSpy', ['extend', 'sendPOST', 'sendGET']);
      $provide.value('api', apiSpy);
    }));

    beforeEach(inject(function ($injector, $q) {
      apiSpy.extend.and.returnValue({});
      apiSpy.sendPOST.and.returnValue(Promise.resolve({
        values: ['values from post']
      }));
      apiSpy.sendGET.and.returnValue(Promise.resolve({
        values: ['values from get']
      }));
      $injector.get('api.contactActions');
    }));

    it('calls api.extend with correct parameters', function () {
      expect(apiSpy.extend.calls.count()).toBe(1);
      expect(apiSpy.extend.calls.mostRecent().args.length).toBe(1);
      expect('getOptions' in apiSpy.extend.calls.mostRecent().args[0]).toBeTruthy();
      expect('forContactType' in apiSpy.extend.calls.mostRecent().args[0].getOptions).toBeTruthy();
      expect('forGroup' in apiSpy.extend.calls.mostRecent().args[0].getOptions).toBeTruthy();
      expect('forTag' in apiSpy.extend.calls.mostRecent().args[0].getOptions).toBeTruthy();
      expect('forStateProvince' in apiSpy.extend.calls.mostRecent().args[0].getOptions).toBeTruthy();
      expect('forCountry' in apiSpy.extend.calls.mostRecent().args[0].getOptions).toBeTruthy();
      expect('forGender' in apiSpy.extend.calls.mostRecent().args[0].getOptions).toBeTruthy();
      expect('forDeceased' in apiSpy.extend.calls.mostRecent().args[0].getOptions).toBeTruthy();

      expect('save' in apiSpy.extend.calls.mostRecent().args[0]).toBeTruthy();
      expect('newIndividual' in apiSpy.extend.calls.mostRecent().args[0].save).toBeTruthy();
      expect('newOrganization' in apiSpy.extend.calls.mostRecent().args[0].save).toBeTruthy();
      expect('newHousehold' in apiSpy.extend.calls.mostRecent().args[0].save).toBeTruthy();
    });

    describe('getOptions.forContactType', function () {
      var result;
      beforeEach(function () {
        result = apiSpy.extend.calls.mostRecent().args[0].getOptions.forContactType.call(apiSpy);
      });

      it('returns the correct result', function (done) {
        result.then(function(data) {
          expect(data).toEqual(['values from get']);
          done();
        });
      });

      it('calls api.sendGET correctly', function () {
        expect(apiSpy.sendGET.calls.count()).toBe(1);
        expect(apiSpy.sendGET.calls.argsFor(0)).toEqual(['Contact', 'getoptions', {
          field: 'contact_type',
          context: 'search'
        }]);
      });
    });

    describe('getOptions.forGroup', function () {
      var result;
      beforeEach(function () {
        result = apiSpy.extend.calls.mostRecent().args[0].getOptions.forGroup.call(apiSpy);
      });

      it('returns the correct result', function (done) {
        result.then(function(data) {
          expect(data).toEqual(['values from get']);
          done();
        });
      });

      it('calls api.sendGET correctly', function () {
        expect(apiSpy.sendGET.calls.count()).toBe(1);
        expect(apiSpy.sendGET.calls.argsFor(0)).toEqual(['GroupContact', 'getoptions', {
          field: 'group_id',
          context: 'search'
        }]);
      });
    });

    describe('getOptions.forTag', function () {
      var result;
      beforeEach(function () {
        result = apiSpy.extend.calls.mostRecent().args[0].getOptions.forTag.call(apiSpy);
      });

      it('returns the correct result', function (done) {
        result.then(function(data) {
          expect(data).toEqual(['values from get']);
          done();
        });
      });

      it('calls api.sendGET correctly', function () {
        expect(apiSpy.sendGET.calls.count()).toBe(1);
        expect(apiSpy.sendGET.calls.argsFor(0)).toEqual(['EntityTag', 'getoptions', {
          field: 'tag_id',
          context: 'search'
        }]);
      });
    });

    describe('getOptions.forStateProvince', function () {
      var result;
      beforeEach(function () {
        result = apiSpy.extend.calls.mostRecent().args[0].getOptions.forStateProvince.call(apiSpy);
      });

      it('returns the correct result', function (done) {
        result.then(function(data) {
          expect(data).toEqual(['values from get']);
          done();
        });
      });

      it('calls api.sendGET correctly', function () {
        expect(apiSpy.sendGET.calls.count()).toBe(1);
        expect(apiSpy.sendGET.calls.argsFor(0)).toEqual(['Address', 'getoptions', {
          field: 'state_province_id',
          context: 'search'
        }]);
      });
    });

    describe('getOptions.forCountry', function () {
      var result;
      beforeEach(function () {
        result = apiSpy.extend.calls.mostRecent().args[0].getOptions.forCountry.call(apiSpy);
      });

      it('returns the correct result', function (done) {
        result.then(function(data) {
          expect(data).toEqual(['values from get']);
          done();
        });
      });

      it('calls api.sendGET correctly', function () {
        expect(apiSpy.sendGET.calls.count()).toBe(1);
        expect(apiSpy.sendGET.calls.argsFor(0)).toEqual(['Address', 'getoptions', {
          field: 'country_id',
          context: 'search'
        }]);
      });
    });

    describe('getOptions.forGender', function () {
      var result;
      beforeEach(function () {
        result = apiSpy.extend.calls.mostRecent().args[0].getOptions.forGender.call(apiSpy);
      });

      it('returns the correct result', function (done) {
        result.then(function(data) {
          expect(data).toEqual(['values from get']);
          done();
        });
      });

      it('calls api.sendGET correctly', function () {
        expect(apiSpy.sendGET.calls.count()).toBe(1);
        expect(apiSpy.sendGET.calls.argsFor(0)).toEqual(['Contact', 'getoptions', {
          field: 'gender_id',
          context: 'search'
        }]);
      });
    });

    describe('getOptions.forDeceased', function () {
      var result;
      beforeEach(function () {
        result = apiSpy.extend.calls.mostRecent().args[0].getOptions.forDeceased.call(apiSpy);
      });

      it('returns the correct result', function (done) {
        result.then(function(data) {
          expect(data).toEqual(['values from get']);
          done();
        });
      });

      it('calls api.sendGET correctly', function () {
        expect(apiSpy.sendGET.calls.count()).toBe(1);
        expect(apiSpy.sendGET.calls.argsFor(0)).toEqual(['Contact', 'getoptions', {
          field: 'is_deceased',
          context: 'search'
        }]);
      });
    });

    describe('save.newIndividual', function () {
      var result;
      beforeEach(function () {
        result = apiSpy.extend.calls.mostRecent().args[0].save.newIndividual.call(apiSpy,
          'First Name', 'Last Name', 'Email');
      });

      it('returns the correct result', function (done) {
        result.then(function(data) {
          expect(data).toEqual('values from post');
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

    describe('save.newOrganization', function () {
      var result;
      beforeEach(function () {
        result = apiSpy.extend.calls.mostRecent().args[0].save.newOrganization.call(apiSpy,
          'Organization Name', 'Email');
      });

      it('returns the correct result', function (done) {
        result.then(function(data) {
          expect(data).toEqual('values from post');
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

    describe('save.newHousehold', function () {
      var result;
      beforeEach(function () {
        result = apiSpy.extend.calls.mostRecent().args[0].save.newHousehold.call(apiSpy,
          'Household Name', 'Email');
      });

      it('returns the correct result', function (done) {
        result.then(function(data) {
          expect(data).toEqual('values from post');
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
