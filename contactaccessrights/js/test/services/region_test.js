define([
  'common/angularMocks',
  'access-rights/services/api/region'
], function () {
  'use strict';

  describe('Region API', function () {
    var apiSpy;

    beforeEach(module('access-rights.models', function ($provide) {
      apiSpy = jasmine.createSpyObj('apiSpy', ['extend', 'sendGET']);
      apiSpy.extend.and.returnValue({});
      $provide.value('api', apiSpy);
    }));
    beforeEach(inject(function (regionApi) {}));

    it('calls api.extend with correct parameters', function () {
      expect(apiSpy.extend.calls.count()).toBe(1);
      expect(apiSpy.extend.calls.mostRecent().args.length).toBe(1);
      expect('query' in apiSpy.extend.calls.mostRecent().args[0]).toBeTruthy();
    });

    describe('query', function () {
      it('calls api.sendGET', function () {
        apiSpy.extend.calls.mostRecent().args[0].query.call(apiSpy);
        expect(apiSpy.sendGET.calls.count()).toBe(1);
      });
    });

  });
});
