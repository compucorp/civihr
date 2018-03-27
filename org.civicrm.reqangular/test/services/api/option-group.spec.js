/* eslint-env amd, jasmine */

define([
  'common/angularMocks',
  'common/models/option-group',
  'common/mocks/services/api/option-group-mock'
], function () {
  'use strict';

  describe('api.option-group', function () {
    var $q, methods, OptionGroupAPI;

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
      methods = Object.keys(OptionGroupAPI);
    }));

    it('has expected interface', function () {
      expect(methods).toContain('valuesOf');
    });

    describe('valuesOf()', function () {
      var optionGroupName = 'hrjc_department';

      beforeEach(function () {
        spyOn(OptionGroupAPI, 'sendGET').and.returnValue($q.resolve());
      });

      describe('when a single option name is passed without any additional parameters', function () {
        beforeEach(function () {
          OptionGroupAPI.valuesOf(optionGroupName);
        });

        it('calls sendGET with correct parameters', function () {
          expect(OptionGroupAPI.sendGET).toHaveBeenCalledWith(
            'OptionValue', 'get',
            { 'option_group_id.name': { IN: [ optionGroupName ] },
              is_active: '1',
              return: [ 'option_group_id.name', 'option_group_id', 'id', 'name',
                'label', 'value', 'weight', 'is_active', 'is_reserved'] },
            undefined
          );
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
