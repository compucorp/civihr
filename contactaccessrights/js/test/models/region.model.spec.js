/* eslint-env amd, jasmine */

define([
  'common/angularMocks',
  'access-rights/modules/access-rights.models'
], function () {
  'use strict';

  describe('Region', function () {
    var modelSpy, apiSpy;

    beforeEach(module('access-rights.models', function ($provide) {
      modelSpy = jasmine.createSpyObj('modelSpy', ['extend']);
      modelSpy.extend.and.returnValue({});
      apiSpy = jasmine.createSpyObj('apiSpy', ['valuesOf']);
      $provide.value('api.optionGroup', apiSpy);
      $provide.value('Model', modelSpy);
    }));
    beforeEach(inject(function (Region) {}));

    it('calls modelSpy.extend with correct parameters', function () {
      expect(modelSpy.extend.calls.count()).toBe(1);
      expect(modelSpy.extend.calls.mostRecent().args.length).toBe(1);
      expect('getAll' in modelSpy.extend.calls.mostRecent().args[0]).toBeTruthy();
    });

    describe('getAll', function () {
      beforeEach(function () {
        modelSpy.extend.calls.mostRecent().args[0].getAll();
      });

      it('calls api.valuesOf', function () {
        expect(apiSpy.valuesOf.calls.count()).toBe(1);
      });
    });
  });
});
