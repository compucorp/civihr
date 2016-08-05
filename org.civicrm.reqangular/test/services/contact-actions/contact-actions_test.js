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
      expect('getContactTypeOptions' in apiSpy.extend.calls.mostRecent().args[0]).toBeTruthy();
      expect('getGroupOptions' in apiSpy.extend.calls.mostRecent().args[0]).toBeTruthy();
      expect('getTagOptions' in apiSpy.extend.calls.mostRecent().args[0]).toBeTruthy();
      expect('getStateProvinceOptions' in apiSpy.extend.calls.mostRecent().args[0]).toBeTruthy();
      expect('getCountryOptions' in apiSpy.extend.calls.mostRecent().args[0]).toBeTruthy();
      expect('getGenderOptions' in apiSpy.extend.calls.mostRecent().args[0]).toBeTruthy();
      expect('getDeceasedOptions' in apiSpy.extend.calls.mostRecent().args[0]).toBeTruthy();

      expect('saveNewIndividual' in apiSpy.extend.calls.mostRecent().args[0]).toBeTruthy();
      expect('saveNewOrganization' in apiSpy.extend.calls.mostRecent().args[0]).toBeTruthy();
      expect('saveNewHousehold' in apiSpy.extend.calls.mostRecent().args[0]).toBeTruthy();
    });

    describe('getContactTypeOptions', function () {
      var result;
      beforeEach(function () {
        result = apiSpy.extend.calls.mostRecent().args[0].getContactTypeOptions.call(apiSpy);
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

    describe('getGroupOptions', function () {
      var result;
      beforeEach(function () {
        result = apiSpy.extend.calls.mostRecent().args[0].getGroupOptions.call(apiSpy);
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

    describe('getTagOptions', function () {
      var result;
      beforeEach(function () {
        result = apiSpy.extend.calls.mostRecent().args[0].getTagOptions.call(apiSpy);
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

    describe('getStateProvinceOptions', function () {
      var result;
      beforeEach(function () {
        result = apiSpy.extend.calls.mostRecent().args[0].getStateProvinceOptions.call(apiSpy);
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

    describe('getCountryOptions', function () {
      var result;
      beforeEach(function () {
        result = apiSpy.extend.calls.mostRecent().args[0].getCountryOptions.call(apiSpy);
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

    describe('getGenderOptions', function () {
      var result;
      beforeEach(function () {
        result = apiSpy.extend.calls.mostRecent().args[0].getGenderOptions.call(apiSpy);
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

    describe('getDeceasedOptions', function () {
      var result;
      beforeEach(function () {
        result = apiSpy.extend.calls.mostRecent().args[0].getDeceasedOptions.call(apiSpy);
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

    describe('saveNewIndividual', function () {
      var result;
      beforeEach(function () {
        result = apiSpy.extend.calls.mostRecent().args[0].saveNewIndividual.call(apiSpy,
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

    describe('saveNewOrganization', function () {
      var result;
      beforeEach(function () {
        result = apiSpy.extend.calls.mostRecent().args[0].saveNewOrganization.call(apiSpy,
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

    describe('saveNewHousehold', function () {
      var result;
      beforeEach(function () {
        result = apiSpy.extend.calls.mostRecent().args[0].saveNewHousehold.call(apiSpy,
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
