define([
    'common/angular',
    'common/modules/models',
    'common/modules/apis',
    'appraisals/modules/models-instances'
], function (angular) {
    'use strict';

    return angular.module('appraisals.models', ['common.models', 'common.apis', 'appraisals.models.instances']);
});
