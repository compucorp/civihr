define([
    'common/modules/models',
    'common/models/model',
    'common/mocks/services/api/option-group-mock' // Temporary, necessary to use the mocked API data
], function (models) {
    'use strict';

    models.factory('OptionGroup', [
        'Model', 'api.optionGroup.mock',
        function (Model, optionGroupAPI) {

            return Model.extend({

                /**
                 * Returns the values of a sigle or multiple option groups
                 *
                 * @param {string/array} names
                 *   Bases on the type of the parameter, the method will return
                 *   either an array of values (string) or an object (array)
                 * @return {Promise}
                 */
                valuesOf: function (names) {
                    return optionGroupAPI.valuesOf(names);
                }
            });
        }
    ]);
})
