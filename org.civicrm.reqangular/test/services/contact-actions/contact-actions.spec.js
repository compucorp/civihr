/* eslint angular/di: 0, jasmine/no-spec-dupes: 0 */
/* eslint-env amd, jasmine */

define([
  'common/angularMocks',
  'common/services/api/contact-actions'
], function () {
  'use strict';

  describe('ContactActions API', function () {
    var $rootScope, apiSpy;

    beforeEach(module('common.apis', function ($provide) {
      apiSpy = jasmine.createSpyObj('apiSpy', ['extend', 'sendPOST', 'sendGET']);
      $provide.value('api', apiSpy);
    }));

    beforeEach(inject(function ($injector, $q, _$rootScope_) {
      $rootScope = _$rootScope_;
      apiSpy.extend.and.returnValue({});
      apiSpy.sendPOST.and.returnValue($q.resolve({
        values: ['values from post']
      }));
      apiSpy.sendGET.and.returnValue($q.resolve({
        id: 1,
        values: ['values from get']
      }));
      $injector.get('api.contactActions');
      $rootScope.$apply();
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

      expect('getFormFields' in apiSpy.extend.calls.mostRecent().args[0]).toBeTruthy();
      expect('forNewIndividual' in apiSpy.extend.calls.mostRecent().args[0].getFormFields).toBeTruthy();
      expect('forNewOrganization' in apiSpy.extend.calls.mostRecent().args[0].getFormFields).toBeTruthy();
      expect('forNewHousehold' in apiSpy.extend.calls.mostRecent().args[0].getFormFields).toBeTruthy();
    });

    describe('getOptions.forContactType', function () {
      var result;
      beforeEach(function () {
        result = apiSpy.extend.calls.mostRecent().args[0].getOptions.forContactType.call(apiSpy);
      });

      it('returns the correct result', function (done) {
        result.then(function (data) {
          expect(data).toEqual(['values from get']);
          done();
        });
        $rootScope.$apply();
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
        result.then(function (data) {
          expect(data).toEqual(['values from get']);
          done();
        });
        $rootScope.$apply();
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
        result.then(function (data) {
          expect(data).toEqual(['values from get']);
          done();
        });
        $rootScope.$apply();
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
        result.then(function (data) {
          expect(data).toEqual(['values from get']);
          done();
        });
        $rootScope.$apply();
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
        result.then(function (data) {
          expect(data).toEqual(['values from get']);
          done();
        });
        $rootScope.$apply();
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
        result.then(function (data) {
          expect(data).toEqual(['values from get']);
          done();
        });
        $rootScope.$apply();
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
        result.then(function (data) {
          expect(data).toEqual(['values from get']);
          done();
        });
        $rootScope.$apply();
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
      describe('when the newIndividual data doesn\'t have the "email" field', function () {
        var result;
        beforeEach(function () {
          result = apiSpy.extend.calls.mostRecent().args[0].save.newIndividual.call(apiSpy, {
            first_name: 'First Name',
            last_name: 'Last Name'
          });
        });

        it('returns the correct result', function (done) {
          result.then(function (data) {
            expect(data).toEqual('values from post');
            done();
          });
          $rootScope.$apply();
        });

        it('calls api.sendPOST correctly', function () {
          $rootScope.$apply();
          expect(apiSpy.sendPOST.calls.count()).toBe(1);
          expect(apiSpy.sendPOST.calls.argsFor(0)).toEqual(['Contact', 'create', {
            first_name: 'First Name',
            last_name: 'Last Name',
            contact_type: 'Individual'
          }]);
        });

        it('doesn\'t call api.sendGET', function () {
          $rootScope.$apply();
          expect(apiSpy.sendGET.calls.count()).toBe(0);
        });
      });

      describe('when the newIndividual data has the "email" field', function () {
        var result;
        beforeEach(function () {
          result = apiSpy.extend.calls.mostRecent().args[0].save.newIndividual.call(apiSpy, {
            first_name: 'First Name',
            last_name: 'Last Name',
            email: 'x@x.com'
          });
        });

        it('returns the correct result', function (done) {
          result.then(function (data) {
            expect(data).toEqual('values from post');
            done();
          });
          $rootScope.$apply();
        });

        it('calls api.sendPOST correctly', function () {
          $rootScope.$apply();
          expect(apiSpy.sendPOST.calls.count()).toBe(1);
          expect(apiSpy.sendPOST.calls.argsFor(0)).toEqual(['Contact', 'create', {
            first_name: 'First Name',
            last_name: 'Last Name',
            contact_type: 'Individual',
            custom_1: 'x@x.com'
          }]);
        });

        it('calls api.sendGET', function () {
          $rootScope.$apply();
          expect(apiSpy.sendGET.calls.count()).toBe(1);
          expect(apiSpy.sendGET.calls.argsFor(0)).toEqual(['CustomField', 'get', {
            return: ['id'],
            custom_group_id: 'Emergency_Contacts',
            name: 'email'
          }]);
        });
      });
    });

    describe('save.newOrganization', function () {
      describe('when the newOrganization data doesn\'t have the "email" field', function () {
        var result;
        beforeEach(function () {
          result = apiSpy.extend.calls.mostRecent().args[0].save.newOrganization.call(apiSpy, {
            organization_name: 'Organization Name'
          });
        });

        it('returns the correct result', function (done) {
          result.then(function (data) {
            expect(data).toEqual('values from post');
            done();
          });
          $rootScope.$apply();
        });

        it('calls api.sendPOST correctly', function () {
          $rootScope.$apply();
          expect(apiSpy.sendPOST.calls.count()).toBe(1);
          expect(apiSpy.sendPOST.calls.argsFor(0)).toEqual(['Contact', 'create', {
            organization_name: 'Organization Name',
            contact_type: 'Organization'
          }]);
        });

        it('doesn\'t call api.sendGET', function () {
          $rootScope.$apply();
          expect(apiSpy.sendGET.calls.count()).toBe(0);
        });
      });

      describe('when the newOrganization data has the "email" field', function () {
        var result;
        beforeEach(function () {
          result = apiSpy.extend.calls.mostRecent().args[0].save.newOrganization.call(apiSpy, {
            organization_name: 'Organization Name',
            email: 'x@x.com'
          });
        });

        it('returns the correct result', function (done) {
          result.then(function (data) {
            expect(data).toEqual('values from post');
            done();
          });
          $rootScope.$apply();
        });

        it('calls api.sendPOST correctly', function () {
          $rootScope.$apply();
          expect(apiSpy.sendPOST.calls.count()).toBe(1);
          expect(apiSpy.sendPOST.calls.argsFor(0)).toEqual(['Contact', 'create', {
            organization_name: 'Organization Name',
            custom_1: 'x@x.com',
            contact_type: 'Organization'
          }]);
        });

        it('calls api.sendGET', function () {
          $rootScope.$apply();
          expect(apiSpy.sendGET.calls.count()).toBe(1);
          expect(apiSpy.sendGET.calls.argsFor(0)).toEqual(['CustomField', 'get', {
            return: ['id'],
            custom_group_id: 'Emergency_Contacts',
            name: 'email'
          }]);
        });
      });
    });

    describe('save.newHousehold', function () {
      describe('when the newHousehold data doesn\'t have the "email" field', function () {
        var result;
        beforeEach(function () {
          result = apiSpy.extend.calls.mostRecent().args[0].save.newHousehold.call(apiSpy, {
            household_name: 'Household Name'
          });
        });

        it('returns the correct result', function (done) {
          result.then(function (data) {
            expect(data).toEqual('values from post');
            done();
          });
          $rootScope.$apply();
        });

        it('calls api.sendPOST correctly', function () {
          $rootScope.$apply();
          expect(apiSpy.sendPOST.calls.count()).toBe(1);
          expect(apiSpy.sendPOST.calls.argsFor(0)).toEqual(['Contact', 'create', {
            household_name: 'Household Name',
            contact_type: 'Household'
          }]);
        });

        it('doesn\'t call api.sendGET', function () {
          $rootScope.$apply();
          expect(apiSpy.sendGET.calls.count()).toBe(0);
        });
      });

      describe('when the newHousehold data has the "email" field', function () {
        var result;
        beforeEach(function () {
          result = apiSpy.extend.calls.mostRecent().args[0].save.newHousehold.call(apiSpy, {
            household_name: 'Household Name',
            email: 'x@x.com'
          });
        });

        it('returns the correct result', function (done) {
          result.then(function (data) {
            expect(data).toEqual('values from post');
            done();
          });
          $rootScope.$apply();
        });

        it('calls api.sendPOST correctly', function () {
          $rootScope.$apply();
          expect(apiSpy.sendPOST.calls.count()).toBe(1);
          expect(apiSpy.sendPOST.calls.argsFor(0)).toEqual(['Contact', 'create', {
            household_name: 'Household Name',
            custom_1: 'x@x.com',
            contact_type: 'Household'
          }]);
        });

        it('calls api.sendGET', function () {
          $rootScope.$apply();
          expect(apiSpy.sendGET.calls.count()).toBe(1);
          expect(apiSpy.sendGET.calls.argsFor(0)).toEqual(['CustomField', 'get', {
            return: ['id'],
            custom_group_id: 'Emergency_Contacts',
            name: 'email'
          }]);
        });
      });
    });

    describe('getFormFields.forNewIndividual', function () {
      var result;
      beforeEach(function () {
        result = apiSpy.extend.calls.mostRecent().args[0].getFormFields.forNewIndividual.call(apiSpy);
      });

      it('returns the correct result', function (done) {
        result.then(function (data) {
          expect(data).toEqual(['values from get']);
          done();
        });
        $rootScope.$apply();
      });

      it('calls api.sendGET correctly', function () {
        expect(apiSpy.sendGET.calls.count()).toBe(1);
        expect(apiSpy.sendGET.calls.argsFor(0)).toEqual(['UFField', 'get', {
          uf_group_id: 'new_individual',
          is_active: true
        }]);
      });
    });

    describe('getFormFields.forNewOrganization', function () {
      var result;
      beforeEach(function () {
        result = apiSpy.extend.calls.mostRecent().args[0].getFormFields.forNewOrganization.call(apiSpy);
      });

      it('returns the correct result', function (done) {
        result.then(function (data) {
          expect(data).toEqual(['values from get']);
          done();
        });
        $rootScope.$apply();
      });

      it('calls api.sendGET correctly', function () {
        expect(apiSpy.sendGET.calls.count()).toBe(1);
        expect(apiSpy.sendGET.calls.argsFor(0)).toEqual(['UFField', 'get', {
          uf_group_id: 'new_organization',
          is_active: true
        }]);
      });
    });

    describe('getFormFields.forNewHousehold', function () {
      var result;
      beforeEach(function () {
        result = apiSpy.extend.calls.mostRecent().args[0].getFormFields.forNewHousehold.call(apiSpy);
      });

      it('returns the correct result', function (done) {
        result.then(function (data) {
          expect(data).toEqual(['values from get']);
          done();
        });
        $rootScope.$apply();
      });

      it('calls api.sendGET correctly', function () {
        expect(apiSpy.sendGET.calls.count()).toBe(1);
        expect(apiSpy.sendGET.calls.argsFor(0)).toEqual(['UFField', 'get', {
          uf_group_id: 'new_household',
          is_active: true
        }]);
      });
    });
  });
});
