define([
  'common/angularMocks',
  'job-roles/app'
], function () {
  'use strict';

  describe('HRJobRolesService', function () {
    var HRJobRolesService, deferred;

    beforeEach(module('hrjobroles'));
    beforeEach(inject(['$q', 'HRJobRolesService', function ($q, _HRJobRolesService_) {
      HRJobRolesService = _HRJobRolesService_;
      deferred = mockDeferred($q);
    }]));

    describe('getOptionValues()', function () {
      var callArgs;

      beforeEach(function () {
        mockAPIResponse(mockedResponse());

        HRJobRolesService.getOptionValues(['group1', 'group2']);
        callArgs = CRM.api3.calls.argsFor(0);
      })

      it('calls the OptionValue entity directly', function () {
        expect(callArgs[0]).toBe('OptionValue');
      });

      it('does a join with the OptionGroup entity', function () {
        expect(callArgs[2]['option_group_id.name']).toBeDefined();
        expect(callArgs[2]['option_group_id.name']['IN']).toEqual(['group1', 'group2']);
      });

      describe('optionGroupData property', function () {
        var finalResult;

        beforeEach(function () {
          finalResult = deferred.resolve.calls.argsFor(0)[0];
        })

        it('is added to the standard response object', function () {
          expect(finalResult.optionGroupData).toBeDefined();
        });

        it('contains a group name/ group id mapping', function () {
          expect(finalResult.optionGroupData).toEqual({
            'Group 1': '11',
            'Group 2': '22'
          })
        });
      });

      /**
       * A mocked list of OptionValues as they would be returned by the api
       *
       * @return {Object}
       */
      function mockedResponse() {
        return {
          values: [
            {
              id: '1',
              label: 'Label 1',
              value: 'Value 1',
              weight: '1',
              option_group_id: '11',
              'option_group_id.name': 'Group 1'
            },
            {
              id: '2',
              label: 'Label 2',
              value: 'Value 2',
              weight: '2',
              option_group_id: '22',
              'option_group_id.name': 'Group 2'
            },
            {
              id: '3',
              label: 'Label 3',
              value: 'Value 3',
              weight: '3',
              option_group_id: '11',
              'option_group_id.name': 'Group 1'
            }
          ]
        };
      }
    });

    /**
     * Mocks the CRM.api3 method, returning an object that
     * mocks the done() implementation
     *
     * @param  {Object} response the response that the mocked api3 should return
     */
    function mockAPIResponse(response) {
      spyOn(CRM, 'api3').and.callFake(function () {
        return {
          done: function(fn) { fn(response); return this; },
          error: function () { return this; }
        };
      });
    }

    /**
     * Mocks the return value of $q.defer(), so that we can spy on its methods
     *
     * @param  {Object} $q
     * @return {Object} the mocked value
     */
    function mockDeferred($q) {
      var deferred = {
        promise: {},
        resolve: jasmine.createSpy('resolve'),
        reject: jasmine.createSpy('reject'),
      }

      spyOn($q, 'defer').and.callFake(function () { return deferred; });

      return deferred;
    }
  });
});
