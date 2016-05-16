define([
    'common/modules/models-instances',
    'common/models/instances/instance'
], function (instances) {
    'use strict';

    instances.factory('ContactInstance', ['ModelInstance', function (ModelInstance) {
        return ModelInstance.extend({});
    }]);
});
