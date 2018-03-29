/* eslint-env amd, jasmine */

define([
  'common/angularMocks',
  'common/models/option-group',
  'common/mocks/services/api/option-group-mock'
], function () {
  'use strict';

  describe('api.option-group', function () {
    var $q, OptionGroupAPI;

    beforeEach(function () {
      module('common.models', 'common.mocks');
    });

    beforeEach(inject([
      'api.optionGroup',
      function (_optionGroupAPI_) {
        OptionGroupAPI = _optionGroupAPI_;
      }
    ]));

    beforeEach(inject(function (_$q_) {
      $q = _$q_;
    }));

    it('has the expected api', function () {
      expect(Object.keys(OptionGroupAPI)).toEqual(['valuesOf']);
    });

    describe('valuesOf()', function () {
      var optionGroupName = 'hrjc_department';

      beforeEach(function () {
        spyOn(OptionGroupAPI, 'sendGET').and.returnValue($q.resolve());
      });

      describe('when a single option name is passed without any additional parameters', function () {
        var sendGETCallArgs;

        beforeEach(function () {
          OptionGroupAPI.valuesOf(optionGroupName);

          sendGETCallArgs = OptionGroupAPI.sendGET.calls.mostRecent().args;
        });

        it('calls sendGET with correct API entity and method', function () {
          expect(sendGETCallArgs[0]).toBe('OptionValue');
          expect(sendGETCallArgs[1]).toBe('get');
        });

        it('calls sendGET with correct parameters', function () {
          expect(sendGETCallArgs[2]).toEqual({
            'option_group_id.name': { IN: [ optionGroupName ] },
            is_active: '1',
            return: [ 'option_group_id.name', 'option_group_id', 'id', 'name',
              'label', 'value', 'weight', 'is_active', 'is_reserved']
          });
        });

        it('does not tell sendGET to disable caching the API results', function () {
          expect(OptionGroupAPI.sendGET.calls.mostRecent().args[3]).not.toBe(false);
        });
      });

      describe('when multiple option names are passed', function () {
        var anotherOptionGroupName = 'hrjc_level_type';

        beforeEach(function () {
          OptionGroupAPI.valuesOf([optionGroupName, anotherOptionGroupName]);
        });

        it('tells backend API to fetch multiple options', function () {
          expect(OptionGroupAPI.sendGET.calls.mostRecent().args[2]['option_group_id.name']).toEqual(
            { IN: [optionGroupName, anotherOptionGroupName] }
          );
        });
      });

      describe('when extra params are passed', function () {
        var params = { paramKey: 'paramValue' };

        beforeEach(function () {
          OptionGroupAPI.valuesOf(optionGroupName, params);
        });

        it('passes extra parameters to sendGET', function () {
          expect(OptionGroupAPI.sendGET.calls.mostRecent().args[2]).toEqual(
            jasmine.objectContaining(params)
          );
        });
      });

      describe('when no caching is needed', function () {
        var cache = false;

        beforeEach(function () {
          OptionGroupAPI.valuesOf(optionGroupName, {}, cache);
        });

        it('tells sendGET to not cache the API call', function () {
          expect(OptionGroupAPI.sendGET.calls.mostRecent().args[3]).toBe(cache);
        });
      });
    });
  });
});
