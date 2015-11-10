define([
    'lodash',
    'modules/services',
'services/api'
], function (_, services) {
    'use strict';

    /**
    * @ngdoc service
    * @name KeyDatesService
    * @param {ApiService} Api
    * @param $log
    * @returns {{}}
    * @constructor
    */
    function KeyDatesService($q, $log, Api) {
        var factory = {};
        var data = [];

        ////////////////////
        // Public Members //
        ////////////////////

        /**
         * @ngdoc method
         * @name KeyDatesService#get
         * @returns {Array}
         */
        factory.get = function () {
            var deferred = $q.defer();

            if (_.isEmpty(data)) {
                //var data = {target_contact_id: 197, period_id: 1, options: {'absence-range': 1}};
                //Api.post('Activity', data, 'getabsences')
                //  .then(function (response) {
                //    $log.debug('Absences', response);
                //  });

                data.push({
                    label: 'Initial Join Date',
                    date: '23/23/23'
                });
                data.push({
                    label: 'Contract Start Date',
                    date: '23/23/23'
                });
                data.push({
                    label: 'Final Termination Date',
                    date: '23/23/23'
                });
            }

            deferred.resolve(data);

            return deferred.promise;
        };

        return factory;
    }

    services.factory('KeyDatesService', ['$q', '$log', 'ApiService', KeyDatesService]);
});
