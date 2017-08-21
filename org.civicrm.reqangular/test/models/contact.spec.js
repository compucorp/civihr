/* eslint-env amd, jasmine */

define([
  'common/lodash',
  'common/angularMocks',
  'common/models/contact',
  'common/models/group',
  'common/models/contact-job-role.model',
  'common/mocks/services/hr-settings-mock',
  'common/mocks/services/api/contact-mock',
  'common/mocks/services/api/contact-job-role-api.api.mock',
  'common/mocks/models/instances/contact-instance-mock'
], function (_) {
  'use strict';

  describe('Contact', function () {
    var $provide, $rootScope, Contact, ContactInstanceMock, Group, ContactJobRole,
      contactAPI, ContactJobRoleAPI, groupContactAPIMock, contacts, contactJobRoles, groupContacts;

    beforeEach(function () {
      module('common.models', 'common.mocks', function (_$provide_) {
        $provide = _$provide_;
      });
      inject([
        'api.contact.mock', 'ContactJobRoleAPIMock', 'HR_settingsMock',
        function (contactAPIMock, ContactJobRoleAPIMock, HRSettingsMock) {
          $provide.value('api.contact', contactAPIMock);
          $provide.value('api.job-role');
          $provide.value('ContactJobRoleAPI', ContactJobRoleAPIMock);
          $provide.value('HR_settings', HRSettingsMock);
        }
      ]);
    });

    beforeEach(inject([
      '$rootScope', 'Contact', 'Group', 'ContactJobRole',
      'ContactInstanceMock', 'ContactJobRoleAPI', 'api.contact',
      'api.group-contact.mock',
      function (_$rootScope_, _Contact_, _Group_, _ContactJobRole_,
        _ContactInstanceMock_, _ContactJobRoleAPI_, _contactAPI_,
        _groupContactAPIMock_) {
        $rootScope = _$rootScope_;

        Contact = _Contact_;
        Group = _Group_;
        ContactJobRole = _ContactJobRole_;

        ContactInstanceMock = _ContactInstanceMock_;

        contactAPI = _contactAPI_;
        ContactJobRoleAPI = _ContactJobRoleAPI_;
        groupContactAPIMock = _groupContactAPIMock_;

        contactAPI.spyOnMethods();
        ContactJobRoleAPI.spyOnMethods();

        contacts = contactAPI.mockedContacts().list;
        contactJobRoles = ContactJobRoleAPI.mockedContactJobRoles.list;
        groupContacts = groupContactAPIMock.mockedGroupsContacts.list;
      }
    ]));

    it('has the expected api', function () {
      expect(Object.keys(Contact)).toEqual(['all', 'find']);
    });

    describe('all()', function () {
      describe('instances', function () {
        var resultsAreInstances;

        beforeEach(function () {
          Contact.all().then(function (response) {
            resultsAreInstances = response.list.every(function (contact) {
              return ContactInstanceMock.isInstance(contact);
            });
          });
          $rootScope.$digest();
        });

        it('returns a list of model instances', function () {
          expect(resultsAreInstances).toBe(true);
        });
      });

      describe('when called without arguments', function () {
        var response;

        beforeEach(function () {
          Contact.all().then(function (_response_) { response = _response_; });
          $rootScope.$digest();
        });

        it('returns all contacts', function () {
          expect(contactAPI.all).toHaveBeenCalled();
          expect(response.list.length).toEqual(contacts.length);
        });
      });

      describe('filters', function () {
        describe('when called with filters', function () {
          var partialName = 'kri';

          beforeEach(function () {
            Contact.all({ display_name: partialName });
            $rootScope.$digest();
          });

          it('passes the filters to the api', function () {
            expect(contactAPI.all).toHaveBeenCalledWith({ display_name: partialName }, undefined);
          });
        });

        describe('when called with job roles filters', function () {
          var jobRolesFilters = {
            department: '2',
            level_type: '1'
          };

          beforeEach(function () {
            spyOn(ContactJobRole, 'all').and.callThrough();
            Contact.all(_.assign({ display_name: 'foo' }, jobRolesFilters));
            $rootScope.$digest();
          });

          it('passes the filters to the JobRole model', function () {
            expect(ContactJobRole.all).toHaveBeenCalledWith(jasmine.objectContaining(jobRolesFilters));
          });

          it('does not pass the filters to its api', function () {
            expect(contactAPI.all).not.toHaveBeenCalledWith(jasmine.objectContaining(jobRolesFilters), undefined);
          });

          it('passes to its api the ids of the contacts whose job roles match the filters', function () {
            expect(contactAPI.all).toHaveBeenCalledWith(jasmine.objectContaining({
              display_name: 'foo',
              id: { 'IN': jasmine.any(Array) }
            }), undefined);
          });
        });

        describe('when called with a group id filter', function () {
          var groupIdFilter = { group_id: '3' };

          beforeEach(function () {
            spyOn(Group, 'contactIdsOf').and.callThrough();
            Contact.all(_.assign({ display_name: 'foo' }, groupIdFilter));
            $rootScope.$digest();
          });

          it('passes the filter to the Group model', function () {
            expect(Group.contactIdsOf).toHaveBeenCalledWith(groupIdFilter.group_id);
          });

          it('does not pass the filters to its api', function () {
            expect(contactAPI.all).not.toHaveBeenCalledWith(jasmine.objectContaining(groupIdFilter), undefined);
          });

          it('passes to its api the ids of the contacts belonging to the group', function () {
            expect(contactAPI.all).toHaveBeenCalledWith(jasmine.objectContaining({
              display_name: 'foo',
              id: { 'IN': jasmine.any(Array) }
            }), undefined);
          });
        });

        describe('when passing a mix of foreign model keys', function () {
          var mixedFilters;

          beforeEach(function () {
            mixedFilters = { department: '859', group_id: '3' };

            Contact.all(_.assign({ display_name: 'foo' }, mixedFilters));
            $rootScope.$digest();
          });

          it('passes to its api the intersection of the contact ids returned by the models', function () {
            expect(contactAPI.all).toHaveBeenCalledWith(jasmine.objectContaining({
              display_name: 'foo',
              id: { 'IN': contactIdsIntersection(mixedFilters) }
            }), undefined);
          });

          /**
           * Returns the intersection of all the contact ids returned
           * by the models
           *
           * @param {object} mixedFilters
           * @return {Array}
           */
          function contactIdsIntersection (mixedFilters) {
            var groupContactIds = groupContacts.filter(function (groupContact) {
              return groupContact.group_id === mixedFilters.group_id;
            }).map(function (groupContact) {
              return groupContact.contact_id;
            });
            var contactJobRoleContactIds = contactJobRoles.filter(function (contactJobRole) {
              return contactJobRole.department === mixedFilters.department;
            }).map(function (contactJobRole) {
              return contactJobRole.contact_id;
            });

            return _.intersection(groupContactIds, contactJobRoleContactIds);
          }
        });
      });

      describe('when called with pagination', function () {
        var pagination = { page: 3, size: 2 };
        var response;

        beforeEach(function () {
          Contact.all(null, pagination).then(function (_response_) {
            response = _response_;
          });
          $rootScope.$digest();
        });

        it('can paginate the contacts list', function () {
          expect(contactAPI.all).toHaveBeenCalledWith(null, pagination);
          expect(response.list.length).toEqual(2);
        });
      });
    });

    describe('find()', function () {
      var contact;
      var targetId = '2';

      beforeEach(function () {
        Contact.find(targetId).then(function (_contact_) {
          contact = _contact_;
        });
        $rootScope.$digest();
      });

      it('finds a contact by id', function () {
        expect(contactAPI.find).toHaveBeenCalledWith(targetId);
        expect(contact.id).toBe(targetId);
      });

      it('returns an instance of the model', function () {
        expect(ContactInstanceMock.isInstance(contact)).toBe(true);
      });
    });
  });
});
