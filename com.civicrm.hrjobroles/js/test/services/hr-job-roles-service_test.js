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

    describe('getContracts()', function () {
      var callArgs, finalResult;

      beforeEach(function () {
        mockAPIResponse(mockedResponse());

        HRJobRolesService.getContracts('1');

        callArgs = CRM.api3.calls.argsFor(0);
        finalResult = deferred.resolve.calls.argsFor(0)[0];
      });

      it('chains 3 api calls together to get the contract revision details', function () {
        expect(callArgs[2]['api.HRJobContractRevision.get']).toBeDefined();
        expect(callArgs[2]['api.HRJobContractRevision.get']['api.HRJobDetails.getsingle']).toBeDefined();
      });

      it('removes the current revision', function () {
        expect(finalResult.values[0].revisions.length).toBe(1);
        expect(finalResult.values[0].revisions[0].id).toBe('1');
      });

      describe('revisions property', function () {
        it('is added to each contract', function () {
          expect(finalResult.values.every(function (contract) {
            return !!contract.revisions;
          })).toBe(true);
        });
      });

      /**
       * A mocked list of contracts as they would be returned by the api
       *
       * @return {Object}
       */
      function mockedResponse() {
        return {
          values: [
            {
              'id': '1',
              'contact_id': '1',
              'is_primary': '1',
              'deleted': '1',
              'is_current': '1',
              'period_start_date': '2016-01-01',
              'period_end_date': '2016-12-31',
              'title': 'Title',
              'api.HRJobContractRevision.get': {
                'is_error': 0,
                'version': 3,
                'count': 1,
                'id': 1,
                'values': [
                  {
                    'id': '1',
                    'jobcontract_id': '1',
                    'api.HRJobDetails.getsingle': {
                      'id': '1',
                      'position': 'Position 1',
                      'title': 'Title 1',
                      'contract_type': 'Type #1',
                      'period_start_date': '2016-01-01',
                      'period_end_date': '2016-05-31',
                      'jobcontract_revision_id': '1'
                    }
                  },
                  {
                    'id': '2',
                    'jobcontract_id': '1',
                    'api.HRJobDetails.getsingle': {
                      'id': '2',
                      'position': 'Position 2',
                      'title': 'Title 2',
                      'contract_type': 'Type #2',
                      'period_start_date': '2016-01-01',
                      'period_end_date': '2016-12-31',
                      'jobcontract_revision_id': '2'
                    }
                  }
                ]
              }
            }
          ]
        };
      }
    });

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
