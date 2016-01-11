define([
    'common/angular',
    'common/modules/apis',
    'appraisals/modules/models-instances'
], function (angular) {
    'use strict';

    return angular.module('appraisals.models', ['appraisals.models.instances', 'common.apis']);
});
