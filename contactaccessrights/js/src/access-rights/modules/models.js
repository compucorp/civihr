define([
	'common/angular',
	'common/modules/models',
	'common/modules/apis',
	'common/mocks/module', // Temporary, necessary to use the mocked API data
  'common/angularResource'
], function (angular) {
	'use strict';
	return angular.module('access-rights.models', []);
});
