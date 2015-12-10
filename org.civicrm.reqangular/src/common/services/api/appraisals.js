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
            },

            /**
             * # TO DO #
             */
            statuses: function () {
                $log.debug('statuses');
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
            }
        });
    }]);
});
