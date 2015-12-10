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
