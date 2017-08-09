/* eslint-env amd, jasmine */

define([
  'common/lodash',
  'common/angularMocks',
  'common/models/group',
  'common/mocks/services/api/group-mock',
  'common/mocks/services/api/group-contact-mock',
  'common/mocks/models/instances/group-instance-mock'
], function (_) {
  'use strict';

  describe('Group', function () {
    var $provide, $rootScope, Group, GroupInstanceMock, groupAPI, groupContactAPI,
      groups, groupContacts;

    beforeEach(function () {
      module('common.models', 'common.mocks', function (_$provide_) {
        $provide = _$provide_;
      });
            // Override api.groups and api.group-contacts with the mocked versions
      inject([
        'api.group.mock', 'api.group-contact.mock',
        function (_groupAPIMock_, _groupContactAPIMock_) {
          $provide.value('api.group', _groupAPIMock_);
          $provide.value('api.group-contact', _groupContactAPIMock_);
        }
      ]);
    });

    beforeEach(inject([
      '$rootScope', 'Group', 'GroupInstanceMock', 'api.group', 'api.group-contact',
      function (_$rootScope_, _Group_, _GroupInstanceMock_, _groupAPI_, _groupContactAPI_) {
        $rootScope = _$rootScope_;

        Group = _Group_;
        GroupInstanceMock = _GroupInstanceMock_;
        groupAPI = _groupAPI_;
        groupContactAPI = _groupContactAPI_;

        groupAPI.spyOnMethods();
        groupContactAPI.spyOnMethods();

        groups = groupAPI.mockedGroups();
        groupContacts = groupContactAPI.mockedGroupsContacts;
      }
    ]));

    it('has the expected api', function () {
      expect(Object.keys(Group)).toEqual(['all', 'find', 'contactIdsOf']);
    });

    describe('all()', function () {
      describe('instances', function () {
        it('returns a list of model instances', function (done) {
          Group.all().then(function (response) {
            expect(response.list.every(function (group) {
              return GroupInstanceMock.isInstance(group);
            })).toBe(true);
          })
          .finally(done) && $rootScope.$digest();
        });
      });

      describe('when called without arguments', function () {
        it('returns all groups', function (done) {
          Group.all().then(function (response) {
            expect(groupAPI.all).toHaveBeenCalled();
            expect(response.list.length).toEqual(groups.list.length);
          })
          .finally(done) && $rootScope.$digest();
        });
      });

      xdescribe('when called with filters', function () {
      });

      describe('when called with pagination', function () {
        var pagination = { page: 2, size: 2 };

        it('can paginate the groups list', function (done) {
          Group.all(null, pagination).then(function (response) {
            expect(groupAPI.all).toHaveBeenCalledWith(null, pagination);
            expect(response.list.length).toEqual(2);
          })
          .finally(done) && $rootScope.$digest();
        });
      });
    });

    describe('find()', function () {
      var targetId = '1';

      it('finds a cycle by id', function (done) {
        Group.find(targetId).then(function (group) {
          expect(groupAPI.find).toHaveBeenCalledWith(targetId);
          expect(group.id).toBe(targetId);
          expect(group.name).toBe('Administrators');
        })
        .finally(done) && $rootScope.$digest();
      });

      it('returns an instance of the model', function (done) {
        Group.find(targetId).then(function (group) {
          expect(GroupInstanceMock.isInstance(group)).toBe(true);
        })
        .finally(done) && $rootScope.$digest();
      });
    });

    describe('contactIdsOf()', function () {
      var contactIds, groupId, promise;

      beforeEach(function () {
        groupId = _.sample(groups.list).id;
        contactIds = groupContacts.list.filter(function (groupContact) {
          return groupContact.group_id === groupId;
        }).map(function (groupContact) {
          return groupContact.contact_id;
        });

        promise = Group.contactIdsOf(groupId);
      });

      it('uses the GroupContact api', function () {
        expect(groupContactAPI.all).toHaveBeenCalledWith({ group_id: groupId });
      });

      it('returns a list of contact ids for the given group', function (done) {
        promise.then(function (ids) {
          expect(ids).not.toEqual([]);
          expect(ids).toEqual(contactIds);
        })
        .finally(done) && $rootScope.$digest();
      });
    });
  });
});
