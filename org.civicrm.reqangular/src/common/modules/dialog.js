define([
    'common/angular',
    'common/angularBootstrap',
    'common/modules/templates'
], function (angular) {
    'use strict';

    return angular.module('common.dialog', ['ui.bootstrap', 'common.templates']);
});
