/* eslint-env amd, jasmine */

define([
  'common/angularMocks',
  'access-rights/modules/access-rights.apis'
], function () {
  'use strict';

  describe('Right API', function () {
    var apiSpy;

    beforeEach(module('access-rights.apis', function ($provide) {
      apiSpy = jasmine.createSpyObj('apiSpy', ['extend', 'sendGET', 'sendPOST']);
      apiSpy.extend.and.returnValue({});
      $provide.value('api', apiSpy);
      $provide.value('$location', {
        search: function () {
          return {
            cid: 1
          };
        }
      });
    }));
    beforeEach(inject(function (rightApi) {}));

    it('calls api.extend with correct parameters', function () {
      expect(apiSpy.extend.calls.count()).toBe(1);
      expect(apiSpy.extend.calls.mostRecent().args.length).toBe(1);
      expect('getLocations' in apiSpy.extend.calls.mostRecent().args[0]).toBeTruthy();
      expect('getRegions' in apiSpy.extend.calls.mostRecent().args[0]).toBeTruthy();
      expect('deleteByIds' in apiSpy.extend.calls.mostRecent().args[0]).toBeTruthy();
      expect('saveRegions' in apiSpy.extend.calls.mostRecent().args[0]).toBeTruthy();
      expect('saveLocations' in apiSpy.extend.calls.mostRecent().args[0]).toBeTruthy();
    });

    describe('getLocations', function () {
      beforeEach(function () {
        apiSpy.extend.calls.mostRecent().args[0].getLocations.call(apiSpy);
      });

      it('calls api.sendGET', function () {
        expect(apiSpy.sendGET.calls.count()).toBe(1);
        expect(apiSpy.sendGET.calls.mostRecent().args.length).toBe(4);
        expect(apiSpy.sendGET).toHaveBeenCalledWith('Rights', 'getlocations', Object({
          contact_id: 1
        }), false);
      });
    });

    describe('getRegions', function () {
      beforeEach(function () {
        apiSpy.extend.calls.mostRecent().args[0].getRegions.call(apiSpy);
      });

      it('calls api.sendGET', function () {
        expect(apiSpy.sendGET.calls.count()).toBe(1);
        expect(apiSpy.sendGET.calls.mostRecent().args.length).toBe(4);
        expect(apiSpy.sendGET).toHaveBeenCalledWith('Rights', 'getregions', {
          contact_id: 1
        }, false);
      });
    });

    describe('deleteByIds', function () {
      var ids = [1, 2];
      beforeEach(function () {
        apiSpy.extend.calls.mostRecent().args[0].deleteByIds.call(apiSpy, ids);
      });

      it('calls api.sendPOST', function () {
        expect(apiSpy.sendPOST.calls.count()).toBe(ids.length);
        expect(apiSpy.sendPOST.calls.argsFor(0)).toEqual(['Rights', 'delete', {
          contact_id: 1,
          id: 1
        }]);
        expect(apiSpy.sendPOST.calls.argsFor(1)).toEqual(['Rights', 'delete', {
          contact_id: 1,
          id: 2
        }]);
      });
    });

    describe('saveRegions', function () {
      var ids = [1, 2];
      beforeEach(function () {
        apiSpy.extend.calls.mostRecent().args[0].saveRegions.call(apiSpy, ids);
      });

      it('calls api.sendPOST', function () {
        expect(apiSpy.sendPOST.calls.count()).toBe(ids.length);
        expect(apiSpy.sendPOST.calls.argsFor(0)).toEqual(['Rights', 'create', {
          contact_id: 1,
          entity_id: 1,
          entity_type: 'hrjc_region'
        }]);
        expect(apiSpy.sendPOST.calls.argsFor(1)).toEqual(['Rights', 'create', {
          contact_id: 1,
          entity_id: 2,
          entity_type: 'hrjc_region'
        }]);
      });
    });

    describe('saveLocations', function () {
      var ids = [1, 2];
      beforeEach(function () {
        apiSpy.extend.calls.mostRecent().args[0].saveLocations.call(apiSpy, ids);
      });

      it('calls api.sendPOST', function () {
        expect(apiSpy.sendPOST.calls.count()).toBe(ids.length);
        expect(apiSpy.sendPOST.calls.argsFor(0)).toEqual(['Rights', 'create', {
          contact_id: 1,
          entity_id: 1,
          entity_type: 'hrjc_location'
        }]);
        expect(apiSpy.sendPOST.calls.argsFor(1)).toEqual(['Rights', 'create', {
          contact_id: 1,
          entity_id: 2,
          entity_type: 'hrjc_location'
        }]);
      });
    });
  });
});
