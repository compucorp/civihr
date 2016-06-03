define([
    'common/angular',
    'common/modules/models',
    'common/modules/apis',
    'common/mocks/module', // Temporary, necessary to use the mocked API data
    'appraisals/modules/models-instances'
], function (angular) {
    'use strict';

    return angular.module('appraisals.models', [
        'common.models',
        'common.apis',
        'common.mocks',
        'appraisals.models.instances'
    ]);
});
