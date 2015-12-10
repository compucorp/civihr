define([
    'common/modules/apis',
    'common/services/api'
], function (apis) {
    'use strict';

    apis.factory('api.appraisals', ['$log', 'api', function ($log, api) {
        $log.debug('api.appraisals');

        // Draft

        return api.extend({

            /**
             * # TO DO #
             */
            activeCycles: function () {
                $log.debug('activeCycles');

                return this.mockGET([1, 2, 3, 4, 5, 6, 7, 8, 9]);
            },

            /**
             * # TO DO #
             */
            all: function (filters, pagination) {
                $log.debug('all');
            },

            /**
             * # TO DO #
             */
            create: function (attributes) {
                $log.debug('create');
            },

            /**
             * # TO DO #
             */
            grades: function () {
                $log.debug('grades');

                return this.mockGET([
                    { label: 1, value: 17 },
                    { label: 2, value: 74 },
                    { label: 3, value: 90 },
                    { label: 4, value: 30 }
                ]);
            },

            /**
             * # TO DO #
             */
            statuses: function () {
                $log.debug('statuses');

                return this.mockGET(['Status #1', 'Status #2', 'Status #3']);
            },

            /**
             * # TO DO #
             */
            statusOverview: function () {
                $log.debug('statusOverview');

                return this.mockGET({
                    steps: [
                        { contacts: 28, overdue: 0 },
                        { contacts: 40, overdue: 2 },
                        { contacts: 36, overdue: 0 },
                        { contacts: 28, overdue: 0 },
                        { contacts: 0, overdue: 0 }
                    ],
                    totalAppraisalsNumber: 248
                });
            },

            /**
             * # TO DO #
             */
            types: function () {
                $log.debug('types');

                return this.mockGET(['Type #1', 'Type #2', 'Type #3', 'Type #4']);
            }
        });
    }]);
});
