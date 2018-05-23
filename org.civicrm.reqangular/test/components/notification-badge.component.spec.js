/* eslint-env amd, jasmine */

define([
  'common/angularMocks',
  'common/components/notification-badge.component',
  'common/services/pub-sub'
], function () {
  'use strict';

  describe('notificationBadge', function () {
    var $componentController, $log, $rootScope, $q, controller, api, pubSub;
    var apiReturnValue = { list: [1, 2, 3], total: 3 };
    var eventName = 'some-event';
    var filters = [{
      apiName: 'Tasks',
      params: { key: 'value' }
    }, {
      apiName: 'Documents',
      params: { key: 'value' }
    }];

    beforeEach(module('common.components', 'common.services', 'common.apis'));

    beforeEach(inject(function (_$componentController_, _$log_, _$rootScope_, _$q_, _pubSub_, _api_) {
      $componentController = _$componentController_;
      $log = _$log_;
      $q = _$q_;
      $rootScope = _$rootScope_;
      pubSub = _pubSub_;
      api = _api_;

      spyOn($log, 'debug');
      spyOn(api, 'getAll').and.returnValue($q.resolve(apiReturnValue));

      compileComponent();
    }));

    it('is initialized', function () {
      expect($log.debug).toHaveBeenCalled();
    });

    describe('on init', function () {
      it('sets the event name same as the passed attribute', function () {
        expect(controller.refreshCountEventName).toBe(eventName);
      });

      it('calls api to get the count', function () {
        expect(api.getAll).toHaveBeenCalledWith(filters[0].apiName, filters[0].params, null, null, null, 'getFull', false);
        expect(api.getAll).toHaveBeenCalledWith(filters[1].apiName, filters[1].params, null, null, null, 'getFull', false);
      });

      describe('after api returns with value', function () {
        it('sets count to number of records returned', function () {
          expect(controller.count).toBe(6);
        });
      });
    });

    describe('when event is fired', function () {
      beforeEach(function () {
        apiReturnValue = { list: [1, 2, 3, 4], total: 4 };

        pubSub.publish(eventName);
        api.getAll.and.returnValue($q.resolve(apiReturnValue));
      });

      it('calls api to get the count', function () {
        expect(api.getAll).toHaveBeenCalledWith(filters[0].apiName, filters[0].params, null, null, null, 'getFull', false);
        expect(api.getAll).toHaveBeenCalledWith(filters[1].apiName, filters[1].params, null, null, null, 'getFull', false);
      });

      describe('after api returns with value', function () {
        it('sets count to number of records returned', function () {
          expect(controller.count).toBe(8);
        });
      });
    });

    function compileComponent () {
      controller = $componentController('notificationBadge', null, {
        refreshCountEventName: eventName,
        filters: filters
      });
      $rootScope.$digest();
    }
  });
});
