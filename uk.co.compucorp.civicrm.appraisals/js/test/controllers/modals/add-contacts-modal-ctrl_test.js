define([
    'common/angularMocks',
    'appraisals/app',
    'common/mocks/services/hr-settings-mock',
], function () {
    'use strict';

    describe('AddContactsModalCtrl', function () {
        var $controller, $modalInstance, $provide, $rootScope, $state, Appraisal,
            Contact, Group, OptionGroup, ctrl;
        var optionGroups = ['hrjc_department', 'hrjc_region', 'hrjc_location',
            'hrjc_level_type'];

        beforeEach(function() {
            module('appraisals', 'common.mocks', function (_$provide_) {
                $provide = _$provide_;
            });
            inject(['HR_settingsMock', function (HR_settingsMock) {
                    $provide.value('$state', jasmine.createSpyObj('$state', ['go']));
                    $provide.value('HR_settings', HR_settingsMock);
                }
            ]);
        })

        beforeEach(inject([
            '$controller', '$rootScope', '$state', 'Appraisal', 'Contact', 'Group', 'OptionGroup',
            function (_$controller_, _$rootScope_, _$state_, _Appraisal_, _Contact_, _Group_, _OptionGroup_) {
                $modalInstance = jasmine.createSpyObj('modalInstance', ['close']);

                $controller = _$controller_;
                $rootScope = _$rootScope_;
                $state = _$state_;

                Appraisal = _Appraisal_;
                Contact = _Contact_;
                Group = _Group_;
                OptionGroup = _OptionGroup_;
            }
        ]));

        describe('inheritance', function () {
            beforeEach(function () {
                initController();
            });

            it('inherits from BasicModalCtrl', function () {
                expect(ctrl.cancel).toBeDefined();
            });
        });

        describe('init', function () {
            beforeEach(function () {
                spyOn(Group, 'all').and.callThrough();
                spyOn(OptionGroup, 'valuesOf').and.callThrough();

                initController();
            });

            it('has not loaded the option values yet', function () {
                expect(ctrl.loading.optionValues).toBe(true);
                expect(ctrl.optionValues).toEqual({});
            });

            it('has not loaded the groups yet', function () {
                expect(ctrl.loading.groups).toBe(true);
                expect(ctrl.groups).toEqual([]);
            });

            it('has not looked up any contacts yet', function () {
                expect(ctrl.contacts.lookedUp).toEqual([]);
            });

            it('has not loaded any contacts that match the search criteria yet', function () {
                expect(ctrl.loading.matchingContacts).toBe(false);
                expect(ctrl.contacts.matching).toEqual([]);
            });

            it('has an empty set of criteria', function () {
                expect(ctrl.criteria).toEqual({});
            });

            it('is not adding any contacts', function () {
                expect(ctrl.loading.addingContacts).toBe(false);
            });

            it('is not showing the confirmation screen', function () {
                expect(ctrl.confirmScreen).toBe(false);
            });

            it('loads the option values', function () {
                expect(OptionGroup.valuesOf).toHaveBeenCalledWith(optionGroups);
            });

            it('loads the groups', function () {
                expect(Group.all).toHaveBeenCalled();
            });
        });

        describe('after init', function () {
            beforeEach(function () {
                initController();
                $rootScope.$digest();
            });

            it('has loaded the option values', function () {
                expect(ctrl.loading.optionValues).toBe(false);
                expect(Object.keys(ctrl.optionValues)).toEqual(optionGroups);
            });

            it('has loaded the groups', function () {
                expect(ctrl.loading.groups).toBe(false);
                expect(ctrl.groups).not.toEqual([]);
            });

            describe('addContacts()', function () {
                var contactsToAdd = [
                    { id: '1', display_name: 'foo' },
                    { id: '2', display_name: 'bar' },
                    { id: '3', display_name: 'baz' }
                ];

                beforeEach(function () {
                    spyOn($rootScope, '$emit');
                    spyOn(Appraisal, 'create').and.callThrough();

                    ctrl.contacts.matching = contactsToAdd;
                    ctrl.addContacts();

                    $rootScope.$digest();
                });

                it('switches the loading flag', function () {
                    expect(ctrl.loading.addingContacts).toBe(true);
                });

                it('creates the new appraisals', function () {
                    expect(Appraisal.create.calls.count()).toEqual(contactsToAdd.length);
                    expect(Appraisal.create).toHaveBeenCalledWith({
                        appraisal_cycle_id: jasmine.any(String),
                        contact_id: jasmine.any(String)
                    });
                });

                it('emits an event', function () {
                    expect($rootScope.$emit).toHaveBeenCalledWith('Appraisal::new', jasmine.any(Array));
                });

                it('closes the modal', function () {
                    expect($modalInstance.close).toHaveBeenCalled();
                });

                it('goes to the Appraisals in Cycle page', function () {
                    expect($state.go).toHaveBeenCalledWith(
                        'appraisals.appraisal-cycle.appraisals-in-cycle',
                        { cycleId: jasmine.any(String) }
                    );
                });
            });

            describe('lookupContact()', function () {
                beforeEach(function() {
                    spyOn(Contact, 'all').and.callThrough();
                });

                describe('when called with an empty string', function () {
                    beforeEach(function () {
                        ctrl.lookupContact('');
                    });

                    it('does not fetch any contact', function () {
                        expect(Contact.all).not.toHaveBeenCalled();
                    });
                });

                describe('when called with a contact name', function () {
                    var searchString = 'foo';

                    beforeEach(function () {
                        ctrl.lookupContact(searchString);
                    });

                    it('fetches the contacts via the model', function () {
                        expect(Contact.all).toHaveBeenCalledWith({
                            display_name: searchString
                        });
                    });
                });
            });

            describe('fetchContacts()', function () {
                beforeEach(function () {
                    spyOn(Contact, 'all').and.callThrough();
                    spyOn(Contact, 'find').and.callThrough();

                    ctrl.criteria = { contact_id: '2', location: '854' };
                    ctrl.fetchContacts();
                });

                it('switches to the confirmation screen', function () {
                    expect(ctrl.confirmScreen).toBe(true);
                });

                it('switches the loading flag', function () {
                    expect(ctrl.loading.matchingContacts).toBe(true);
                });

                it('calls Contact.all() with the criteria, except the contact id', function () {
                    expect(Contact.all).toHaveBeenCalledWith(_.omit(ctrl.criteria, 'contact_id'));
                });

                it('calls Contact.find() with the given contact_id', function () {
                    expect(Contact.find).toHaveBeenCalledWith(ctrl.criteria.contact_id);
                });

                describe('after the contacts have been fetched', function () {
                    beforeEach(function () {
                        $rootScope.$digest();
                    });

                    it('switches the loading flag', function () {
                        expect(ctrl.loading.matchingContacts).toBe(false);
                    });

                    it('gets the contacts that match the criteria', function () {
                        expect(ctrl.contacts.matching).not.toEqual([]);
                    });
                });
            });

            describe('resetSearch()', function () {
                beforeEach(function () {
                    ctrl.confirmScreen = true;
                    ctrl.criteria = { contact_id: '2', location: '854' };
                    ctrl.contacts.matching = [{ foo: 'foo' }, { bar: 'bar' }, { baz: 'baz' }];

                    ctrl.resetSearch();
                });

                it('goes back to the search screen', function () {
                    expect(ctrl.confirmScreen).toBe(false);
                });

                it('empties the list of matching contacts', function () {
                    expect(ctrl.contacts.matching).toEqual([]);
                });

                it('resets the search criteria', function () {
                    expect(ctrl.criteria).toEqual({});
                });
            });
        });

        function initController() {
            var scope = $rootScope.$new();
            scope.cycleId = '9';

            ctrl = $controller('AddContactsModalCtrl', {
                $modalInstance: $modalInstance,
                $scope: scope
            });
        }
    });
})
