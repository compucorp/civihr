/* eslint-env amd, jasmine */

define([
  'common/angularMocks',
  'access-rights/access-rights.models'
], function () {
  'use strict';

  describe('Right', function () {
    var modelSpy, apiSpy;

    beforeEach(module('access-rights.models', function ($provide) {
      modelSpy = jasmine.createSpyObj('modelSpy', ['extend']);
      modelSpy.extend.and.returnValue({});
      apiSpy = jasmine.createSpyObj('apiSpy', ['getLocations', 'getRegions',
        'deleteByIds', 'saveRegions', 'saveLocations'
      ]);
      $provide.value('Model', modelSpy);
      $provide.value('RightsAPI', apiSpy);
      $provide.value('$location', {
        search: function () {
          return {
            cid: 1
          };
        }
      });
    }));
    beforeEach(inject(function (Right) {}));

    it('calls Model.extend with correct parameters', function () {
      expect(modelSpy.extend.calls.count()).toBe(1);
      expect(modelSpy.extend.calls.mostRecent().args.length).toBe(1);
      expect('getLocations' in modelSpy.extend.calls.mostRecent().args[0]).toBeTruthy();
      expect('getRegions' in modelSpy.extend.calls.mostRecent().args[0]).toBeTruthy();
      expect('deleteByIds' in modelSpy.extend.calls.mostRecent().args[0]).toBeTruthy();
      expect('saveRegions' in modelSpy.extend.calls.mostRecent().args[0]).toBeTruthy();
      expect('saveLocations' in modelSpy.extend.calls.mostRecent().args[0]).toBeTruthy();
    });

    describe('getLocations', function () {
      beforeEach(function () {
        modelSpy.extend.calls.mostRecent().args[0].getLocations();
      });

      it('calls api.getLocations', function () {
        expect(apiSpy.getLocations.calls.count()).toBe(1);
      });
    });

    describe('getRegions', function () {
      beforeEach(function () {
        modelSpy.extend.calls.mostRecent().args[0].getRegions();
      });

      it('calls api.getRegions', function () {
        expect(apiSpy.getRegions.calls.count()).toBe(1);
      });
    });

    describe('deleteByIds', function () {
      var ids = [1, 2];
      beforeEach(function () {
        modelSpy.extend.calls.mostRecent().args[0].deleteByIds(ids);
      });

      it('calls api.deleteByIds', function () {
        expect(apiSpy.deleteByIds.calls.count()).toBe(1);
        expect(apiSpy.deleteByIds.calls.argsFor(0)).toEqual([ids]);
      });
    });

    describe('saveRegions', function () {
      var ids = [1, 2];
      beforeEach(function () {
        modelSpy.extend.calls.mostRecent().args[0].saveRegions(ids);
      });

      it('calls api.saveRegions', function () {
        expect(apiSpy.saveRegions.calls.count()).toBe(1);
        expect(apiSpy.saveRegions.calls.argsFor(0)).toEqual([ids]);
      });
    });

    describe('saveLocations', function () {
      var ids = [1, 2];
      beforeEach(function () {
        modelSpy.extend.calls.mostRecent().args[0].saveLocations(ids);
      });

      it('calls api.saveLocations', function () {
        expect(apiSpy.saveLocations.calls.count()).toBe(1);
        expect(apiSpy.saveLocations.calls.argsFor(0)).toEqual([ids]);
      });
    });
  });
});
