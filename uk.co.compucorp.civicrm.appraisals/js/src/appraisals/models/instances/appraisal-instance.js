define([
    'common/models/instances/instance',
    'appraisals/modules/models-instances'
], function (__, instances) {
    'use strict';

    instances.factory('AppraisalInstance', ['ModelInstance', function (ModelInstance) {
        return ModelInstance.extend({});
    }]);
});
