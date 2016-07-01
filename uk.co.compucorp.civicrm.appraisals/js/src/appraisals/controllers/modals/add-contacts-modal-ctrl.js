define([
    'appraisals/modules/controllers',
    'common/models/contact',
    'common/models/group',
    'common/models/option-group'
], function (controllers) {
    'use strict';

    controllers.controller('AddContactsModalCtrl', [
        '$q', '$log', '$controller', '$uibModalInstance', '$rootScope', '$scope',
        '$state', 'Appraisal', 'Contact', 'Group', 'OptionGroup',
        function ($q, $log, $controller, $modalInstance, $rootScope, $scope, $state, Appraisal, Contact, Group, OptionGroup) {
            $log.debug('AddContactsModalCtrl');

            var vm = Object.create($controller('BasicModalCtrl', {
                $uibModalInstance: $modalInstance
            }));

            vm.confirmScreen = false;
            vm.groups = [];
            vm.criteria = {};
            vm.optionValues = {};
            vm.contacts = { lookedUp: [], matching: [] };
            vm.loading = {
                addingContacts: false,
                groups: true,
                matchingContacts: false,
                optionValues: true
            };

            /**
             * Creates an appraisal for each of the matching contacts
             * Emits an event and closes the modal once the appraisals are created
             */
            vm.addContacts = function () {
                vm.loading.addingContacts = true;

                $q.all(vm.contacts.matching.map(function (contact) {
                    return Appraisal.create({
                        appraisal_cycle_id: $scope.cycleId,
                        contact_id: contact.id
                    });
                }))
                .then(function (results) {
                    $rootScope.$emit('Appraisal::new', results);
                    $modalInstance.close();

                    $state.go('appraisals.appraisal-cycle.appraisals-in-cycle', {
                        cycleId: $scope.cycleId
                    });
                });
            };

            /**
             * Fetches the contacts that match the criteria set and switch
             * to the confirmation screen
             *
             * The operator used for the criteria is an `AND`, except for
             * `contact_id`, which is treated with an `OR`
             */
            vm.fetchContacts = function () {
                var criteria = _.clone(vm.criteria), promises = [];

                vm.confirmScreen = true;
                vm.loading.matchingContacts = true;

                // If a contact id is passed, find the contact directly
                if (criteria.contact_id) {
                    promises.push(Contact.find(criteria.contact_id)
                        .then(function (contact) {
                            return [contact];
                        })
                    );

                    criteria = _.omit(criteria, 'contact_id');
                }

                // If passed, find the contacts matching all (`AND`) the other criteria
                if (!_.isEmpty(criteria)) {
                    promises.push(Contact.all(criteria)
                        .then(function (contacts) {
                            return contacts.list;
                        })
                    );
                }

                $q.all(promises).then(function (results) {
                    vm.contacts.matching = _(results).flatten().uniq().value();
                    vm.loading.matchingContacts = false;
                });
            };

            /**
             * Fetches contacts based on a search string (if not empty)
             *
             * @param {string} search
             */
            vm.lookupContact = function (search) {
                if (!search) {
                    return;
                }

                Contact.all({ display_name: search })
                    .then(function (response) {
                        vm.contacts.lookedUp = response;
                    });
            };

            /**
             * Resets the search criteria, sending the user back to the
             * form screen and emptying the list of matching contacts
             */
            vm.resetSearch = function () {
                vm.criteria = {};
                vm.contacts.matching = [];
                vm.confirmScreen = false;
            }

            init();

            /**
             * Loads the option values
             */
            function init() {
                Group.all().then(function (response) {
                    vm.loading.groups = false;
                    vm.groups = response.list;
                })

                OptionGroup.valuesOf([
                    'hrjc_department',
                    'hrjc_region',
                    'hrjc_location',
                    'hrjc_level_type',
                ])
                .then(function (values) {
                    vm.loading.optionValues = false;
                    vm.optionValues = values;
                });
            }

            return vm;
    }]);
});
