define([
  'common/lodash',
  'common/angularMocks',
  'common/models/contact',
  'common/models/group',
  'common/models/job-role',
  'common/mocks/services/hr-settings-mock',
  'common/mocks/services/api/contact-mock',
  'common/mocks/services/api/job-role-mock',
  'common/mocks/models/instances/contact-instance-mock'
], function (_) {
  'use strict';

  describe('Contact', function () {
    var $provide, $rootScope, Contact, ContactInstanceMock, Group, JobRole, $q,
      contactAPI, jobRoleAPI, groupContactAPIMock, contacts, jobRoles, groupContacts;

    beforeEach(function () {
      module('common.models', 'common.mocks', function (_$provide_) {
        $provide = _$provide_;
      });
      inject([
        'api.contact.mock', 'api.job-role.mock', 'HR_settingsMock',
        function (contactAPIMock, jobRoleAPIMock, HR_settingsMock) {
          $provide.value('api.contact', contactAPIMock);
          $provide.value('api.job-role', jobRoleAPIMock);
          $provide.value('HR_settings', HR_settingsMock);
        }
      ]);
    });

    beforeEach(inject([
      '$rootScope', '$q', 'Contact', 'Group', 'JobRole', 'ContactInstanceMock',
      'api.job-role', 'api.contact', 'api.group-contact.mock',
      function (_$rootScope_, _$q_, _Contact_, _Group_, _JobRole_, _ContactInstanceMock_, _jobRoleAPI_, _contactAPI_, _groupContactAPIMock_) {
        $rootScope = _$rootScope_;

        $q = _$q_;
        Contact = _Contact_;
        Group = _Group_;
        JobRole = _JobRole_;

        ContactInstanceMock = _ContactInstanceMock_;

        contactAPI = _contactAPI_;
        jobRoleAPI = _jobRoleAPI_;
        groupContactAPIMock = _groupContactAPIMock_;

        contactAPI.spyOnMethods();
        jobRoleAPI.spyOnMethods();

        contacts = contactAPI.mockedContacts().list;
        jobRoles = jobRoleAPI.mockedJobRoles.list;
        groupContacts = groupContactAPIMock.mockedGroupsContacts.list;
      }
    ]));

    it('has the expected api', function () {
      expect(Object.keys(Contact)).toEqual(['all', 'find']);
    });

    describe('all()', function () {
      describe('instances', function () {
        it('returns a list of model instances', function (done) {
          Contact.all().then(function (response) {
            expect(response.list.every(function (contact) {
              return ContactInstanceMock.isInstance(contact);
            })).toBe(true);
          })
            .finally(done) && $rootScope.$digest();
        });
      });

      describe('when called without arguments', function () {
        it('returns all contacts', function (done) {
          Contact.all().then(function (response) {
            expect(contactAPI.all).toHaveBeenCalled();
            expect(response.list.length).toEqual(contacts.length);
          })
            .finally(done) && $rootScope.$digest();
        });
      });

      describe('contact api called with right parameters', function () {
        var filter = {display_name: 'kri'},
          pagination = 'page',
          sort = 'display_name',
          additionalParams = 'additionalParams';

        afterEach(function() {
          $rootScope.$digest();
        });

        it('passes the filters to the api', function () {
          Contact.all(filter, pagination, sort, additionalParams).then(function () {
            expect(contactAPI.all).toHaveBeenCalledWith(filter, pagination, sort, additionalParams);
          });
        });
      });

      describe('filters', function () {

        describe('when called with filters', function () {
          var partialName = 'kri';

          it('passes the filters to the api', function (done) {
            Contact.all({display_name: partialName}).then(function (response) {
              expect(contactAPI.all).toHaveBeenCalledWith({display_name: partialName}, undefined, undefined, undefined);
            })
              .finally(done) && $rootScope.$digest();
          });
        });

        describe('when called with job roles filters', function () {
          var jobRolesFilters = {
            department: '2',
            level_type: '1'
          }, groupContactIds, jobRoleContactIds;

          beforeEach(function () {
            spyOn(JobRole, 'all').and.callThrough();

            groupContactIds = contactAPI.mockedContacts().list.map(function (jobRole) {
              return jobRole.contact_id;
            });
            jobRoleContactIds = contactAPI.mockedContacts();
            jobRoleAPI.all.and.callFake(function () {
              var defer = $q.defer();
              defer.resolve(jobRoleContactIds);
              return defer.promise;
            });
            spyOn(Group, 'contactIdsOf').and.callFake(function () {
              var defer = $q.defer();
              defer.resolve(groupContactIds);
              return defer.promise;
            });

            Contact.all(_.assign({
              display_name: 'foo'
            }, jobRolesFilters));

            $rootScope.$digest();
          });


          it('passes the filters to the JobRole model', function () {
            expect(JobRole.all).toHaveBeenCalledWith(jasmine.objectContaining(jobRolesFilters))
          });

          it('does not pass the filters to its api', function () {
            expect(contactAPI.all).not.toHaveBeenCalledWith(jasmine.objectContaining(jobRolesFilters), undefined)
          });

          it('passes to its api the ids of the contacts whose job roles match the filters', function () {
            expect(contactAPI.all).toHaveBeenCalledWith(jasmine.objectContaining({
              display_name: 'foo'
            }), undefined, undefined, undefined);
          });
        });

        describe('when called with a group id filter', function () {
          var groupIdFilter = {group_id: '3'};

          beforeEach(function () {
            spyOn(Group, 'contactIdsOf').and.callThrough();

            Contact.all(_.assign({
              display_name: 'foo'
            }, groupIdFilter));

            $rootScope.$digest();
          });


          it('passes the filter to the Group model', function () {
            expect(Group.contactIdsOf).toHaveBeenCalledWith(groupIdFilter.group_id);
          });

          it('does not pass the filters to its api', function () {
            expect(contactAPI.all).not.toHaveBeenCalledWith(jasmine.objectContaining(groupIdFilter), undefined)
          });

          it('passes to its api the ids of the contacts belonging to the group', function () {
            expect(contactAPI.all).toHaveBeenCalledWith(jasmine.objectContaining({
              display_name: 'foo',
              id: {'IN': jasmine.any(Array)}
            }), undefined, undefined, undefined);
          });
        });

        describe('when passing a mix of foreign model keys', function () {
          var mixedFilters,
            groupContactIds,
            jobRoleContactIds;


          beforeEach(function () {
            groupContactIds = contactAPI.mockedContacts().list.slice(0, 5).map(function (jobRole) {
              return jobRole.contact_id;
            });
            jobRoleContactIds = contactAPI.mockedContacts();

            mixedFilters = {department: '859', group_id: '3'};

            jobRoleAPI.all.and.callFake(function () {
              var defer = $q.defer();
              defer.resolve(jobRoleContactIds);
              return defer.promise;
            });
            spyOn(Group, 'contactIdsOf').and.callFake(function () {
              var defer = $q.defer();
              defer.resolve(groupContactIds);
              return defer.promise;
            });

            Contact.all(_.assign({
              display_name: 'foo',
              id: {'IN': ['1']}
            }, mixedFilters));
            $rootScope.$digest();
          });

          it('passes to its api the intersection of the contact ids returned by the models', function () {
            expect(contactAPI.all).toHaveBeenCalledWith(jasmine.objectContaining({
              display_name: 'foo',
              id: {'IN': intersectionofContactIds()}
            }), undefined, undefined, undefined);
          });

          function intersectionofContactIds() {
            return _.intersection(groupContactIds, jobRoleContactIds.list.map(function (jobRole) {
              return jobRole.contact_id;
            }));
          }
        });
      });

      describe('when called with pagination', function () {
        var pagination = {page: 3, size: 2};

        it('can paginate the contacts list', function (done) {
          Contact.all(null, pagination).then(function (response) {
            expect(contactAPI.all).toHaveBeenCalledWith(null, pagination, undefined, undefined);
            expect(response.list.length).toEqual(2);
          })
            .finally(done) && $rootScope.$digest();
        });
      });
    });

    describe('find()', function () {
      var targetId = '2';

      it('finds a contact by id', function (done) {
        Contact.find(targetId).then(function (contact) {
          expect(contactAPI.find).toHaveBeenCalledWith(targetId);
          expect(contact.id).toBe(targetId);
          expect(contact.display_name).toBe('jacobc82@lol.co.pl');
        })
          .finally(done) && $rootScope.$digest();
      });

      it('returns an instance of the model', function (done) {
        Contact.find(targetId).then(function (contact) {
          expect(ContactInstanceMock.isInstance(contact)).toBe(true);
        })
          .finally(done) && $rootScope.$digest();
      });
    });
  });
});
