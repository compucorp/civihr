/* eslint-env amd, jasmine */

define([
  'common/angularMocks',
  'common/models/option-group',
  'common/mocks/services/api/option-group-mock'
], function () {
  'use strict';

  describe('OptionGroup', function () {
    var $provide, OptionGroup, optionGroupAPI;

    beforeEach(function () {
      module('common.models', 'common.mocks', function (_$provide_) {
        $provide = _$provide_;
      });
      // Override api.optionGroup with the mocked version
      inject(['api.optionGroup.mock', function (_optionGroupAPIMock_) {
        $provide.value('api.optionGroup', _optionGroupAPIMock_);
      }]);
    });

    beforeEach(inject([
      'OptionGroup', 'api.optionGroup',
      function (_OptionGroup_, _optionGroupAPI_) {
        OptionGroup = _OptionGroup_;
        optionGroupAPI = _optionGroupAPI_;

        optionGroupAPI.spyOnMethods();
      }
    ]));

    it('has the expected api', function () {
      expect(Object.keys(OptionGroup)).toEqual(['valuesOf']);
    });

    describe('valuesOf()', function () {
      var optionGroupName = 'hrjc_department';

      describe('when just a single option group name is passed', function () {
        beforeEach(function () {
          OptionGroup.valuesOf(optionGroupName);
        });

        it('calls the correct API method', function () {
          expect(optionGroupAPI.valuesOf.calls.mostRecent().args[0]).toBe(optionGroupName);
        });

        it('caches requests by default', function () {
          expect(optionGroupAPI.valuesOf.calls.mostRecent().args[1]).toBe(undefined);
        });
      });

      describe('when several option group names are passed', function () {
        var multipleOptionGroupNames = [optionGroupName, 'some_other_option'];

        beforeEach(function () {
          OptionGroup.valuesOf(multipleOptionGroupNames);
        });

        it('passes the array of option group names to the API method', function () {
          expect(optionGroupAPI.valuesOf.calls.mostRecent().args[0]).toBe(multipleOptionGroupNames);
        });
      });

      describe('when caching is disabled', function () {
        var cache = false;

        beforeEach(function () {
          OptionGroup.valuesOf(optionGroupName, cache);
        });

        it('does not cache requests', function () {
          expect(optionGroupAPI.valuesOf.calls.mostRecent().args[1]).toBe(cache);
        });
      });
    });
  });
});
