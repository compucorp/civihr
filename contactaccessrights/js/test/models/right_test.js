define([
  'common/angularMocks',
  'access-rights/models/location'
], function () {
  'use strict';

  describe('Right', function () {
    var $provide, Right, apiBuilderSpy, apiSpy;

    beforeEach(module('access-rights.models', function ($provide) {
      apiBuilderSpy = jasmine.createSpyObj('apiBuilderSpy', ['build']);
      apiSpy = jasmine.createSpyObj('apiSpy', ['getAllEntities', 'removeEntity', 'saveEntity']);
      apiBuilderSpy.build.and.returnValue(apiSpy);
      $provide.value('apiBuilder', apiBuilderSpy);
      $provide.value('$location', {
        search: function () {
          return {
            cid: 1
          }
        }
      });
    }));
    beforeEach(inject(function (_Right_) {
      Right = _Right_;
    }));

    it('calls apiBuilder.build with correct parameters', function () {
      expect(apiBuilderSpy.build.calls.count()).toBe(1);
      expect(apiBuilderSpy.build.calls.mostRecent().args.length).toBe(3);
      expect('getLocations' in apiBuilderSpy.build.calls.mostRecent().args[0]).toBeTruthy();
      expect('getRegions' in apiBuilderSpy.build.calls.mostRecent().args[0]).toBeTruthy();
      expect('deleteByIds' in apiBuilderSpy.build.calls.mostRecent().args[0]).toBeTruthy();
      expect('saveRegions' in apiBuilderSpy.build.calls.mostRecent().args[0]).toBeTruthy();
      expect('saveLocations' in apiBuilderSpy.build.calls.mostRecent().args[0]).toBeTruthy();
      expect(apiBuilderSpy.build.calls.mostRecent().args[1]).toBe('Rights');
      expect(apiBuilderSpy.build.calls.mostRecent().args[2]).toEqual({
        'contact_id': 1
      });
    });

    describe('getLocations', function () {
      it('calls api.getAllEntities', function () {
        apiBuilderSpy.build.calls.mostRecent().args[0].getLocations.call(apiSpy, 'filters', 'pagination', 'sort');
        expect(apiSpy.getAllEntities.calls.count()).toBe(1);
        expect(apiSpy.getAllEntities).toHaveBeenCalledWith('filters', 'pagination', 'sort', {
          action: 'getlocations'
        });
      });
    });

    describe('getRegions', function () {
      it('calls api.getAllEntities', function () {
        apiBuilderSpy.build.calls.mostRecent().args[0].getRegions.call(apiSpy, 'filters', 'pagination', 'sort');
        expect(apiSpy.getAllEntities.calls.count()).toBe(1);
        expect(apiSpy.getAllEntities).toHaveBeenCalledWith('filters', 'pagination', 'sort', {
          action: 'getregions'
        });
      });
    });

    describe('deleteByIds', function () {
      it('calls api.removeEntity', function () {
        var idsToDelete = [1, 2];
        apiBuilderSpy.build.calls.mostRecent().args[0].deleteByIds.call(apiSpy, idsToDelete);
        expect(apiSpy.removeEntity.calls.count()).toBe(idsToDelete.length);
        expect(apiSpy.removeEntity.calls.argsFor(0)).toEqual([{
          id: 1
        }]);
        expect(apiSpy.removeEntity.calls.argsFor(1)).toEqual([{
          id: 2
        }]);
      });
    });

    describe('saveRegions', function () {
      it('calls api.saveEntity', function () {
        var idsToSave = [1, 2];
        apiBuilderSpy.build.calls.mostRecent().args[0].saveRegions.call(apiSpy, idsToSave);
        expect(apiSpy.saveEntity.calls.count()).toBe(idsToSave.length);
        expect(apiSpy.saveEntity.calls.argsFor(0)).toEqual([{
          entity_id: 1,
          entity_type: 'hrjc_region'
        }]);
        expect(apiSpy.saveEntity.calls.argsFor(1)).toEqual([{
          entity_id: 2,
          entity_type: 'hrjc_region'
        }]);
      });
    });

    describe('saveLocations', function () {
      it('calls api.saveEntity', function () {
        var idsToSave = [1, 2];
        apiBuilderSpy.build.calls.mostRecent().args[0].saveLocations.call(apiSpy, idsToSave);
        expect(apiSpy.saveEntity.calls.count()).toBe(idsToSave.length);
        expect(apiSpy.saveEntity.calls.argsFor(0)).toEqual([{
          entity_id: 1,
          entity_type: 'hrjc_location'
        }]);
        expect(apiSpy.saveEntity.calls.argsFor(1)).toEqual([{
          entity_id: 2,
          entity_type: 'hrjc_location'
        }]);
      });
    });

  });
});
