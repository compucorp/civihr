/* eslint-env amd, jasmine */

define([
  'common/lodash',
  'mocks/data/job-contract.data',
  'common/angularMocks',
  'job-roles/modules/job-roles.module'
], function (_, mockedContracts) {
  'use strict';

  describe('jobRoleService', function () {
    var $q, jobRoleService, deferred;

    beforeEach(module('hrjobroles'));
    beforeEach(inject(['$q', 'jobRoleService', function (_$q_, _jobRoleService_) {
      $q = _$q_;

      jobRoleService = _jobRoleService_;
      deferred = mockDeferred($q);
    }]));

    describe('getContracts()', function () {
      var callArgs, finalResult;

      beforeEach(function () {
        mockAPIResponse(_.cloneDeep(mockedContracts));

        jobRoleService.getContracts('1');

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
    });

    describe('getContactList()', function () {
      beforeEach(function () {
        mockAPIResponse();
      });

      describe('basic test', function () {
        var args;

        beforeEach(function () {
          jobRoleService.getContactList();
          args = CRM.api3.calls.mostRecent().args;
        });

        it('calls the Contact.get api endpoint', function () {
          expect(args[0]).toBe('Contact');
          expect(args[1]).toBe('get');
        });

        it('returns only the id and sort name of each contact', function () {
          expect(args[2]).toEqual(jasmine.objectContaining({
            'return': 'id, sort_name'
          }));
        });
      });

      describe('when no specific filter is passed', function () {
        beforeEach(function () {
          jobRoleService.getContactList();
        });

        it('sets as `null` the filter properties', function () {
          expect(CRM.api3.calls.mostRecent().args[2]).toEqual(jasmine.objectContaining({
            'id': null,
            'sort_name': null
          }));
        });
      });

      describe('when filtering by contact name', function () {
        beforeEach(function () {
          jobRoleService.getContactList('foo');
        });

        it('passes the name to the api endpoint', function () {
          expect(CRM.api3.calls.mostRecent().args[2]).toEqual(jasmine.objectContaining({
            'sort_name': 'foo'
          }));
        });
      });

      describe('when filtering by contact ids', function () {
        var idsList = ['1', '2', '3', '4'];

        beforeEach(function () {
          jobRoleService.getContactList(null, idsList);
        });

        it('passes the ids as an IN parameter to the endpoint', function () {
          expect(CRM.api3.calls.mostRecent().args[2]).toEqual(jasmine.objectContaining({
            'id': { 'IN': idsList }
          }));
        });
      });
    });

    describe('getOptionValues()', function () {
      var callArgs;

      beforeEach(function () {
        mockAPIResponse(mockedResponse());

        jobRoleService.getOptionValues(['group1', 'group2']);
        callArgs = CRM.api3.calls.argsFor(0);
      });

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
        });

        it('is added to the standard response object', function () {
          expect(finalResult.optionGroupData).toBeDefined();
        });

        it('contains a group name/ group id mapping', function () {
          expect(finalResult.optionGroupData).toEqual({
            'Group 1': '11',
            'Group 2': '22'
          });
        });
      });

      /**
       * A mocked list of OptionValues as they would be returned by the api
       *
       * @return {Object}
       */
      function mockedResponse () {
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
    function mockAPIResponse (response) {
      spyOn(CRM, 'api3').and.callFake(function () {
        return {
          done: function (fn) { fn(response); return this; },
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
    function mockDeferred ($q) {
      var deferred = {
        promise: {},
        resolve: jasmine.createSpy('resolve'),
        reject: jasmine.createSpy('reject')
      };

      spyOn($q, 'defer').and.callFake(function () { return deferred; });

      return deferred;
    }
  });
});
