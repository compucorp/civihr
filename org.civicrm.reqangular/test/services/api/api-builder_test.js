define([
	'common/angular',
	'common/angularMocks',
	'common/services/api/resource-builder',
	'common/services/api/api-builder'
], function () {
	'use strict';

	describe('apiBuilder', function () {
		var apiBuilder, resourceBuilder, resourceBuilderSpy;

		beforeEach(module('common.apis'));
		beforeEach(inject(['apiBuilder', 'resourceBuilder',
			function (_apiBuilder_, _resourceBuilder_) {
				apiBuilder = _apiBuilder_;
				resourceBuilder = _resourceBuilder_;
				var spyMethods = ['save', 'remove', 'getAll'];
				resourceBuilderSpy = jasmine.createSpyObj('resourceBuilderSpy', spyMethods);
				spyMethods.forEach(function (i) {
					resourceBuilderSpy[i].and.returnValue({
						$promise: {}
					});
				});
				spyOn(resourceBuilder, 'build').and.returnValue(resourceBuilderSpy);
			}
		]));

		describe('build', function () {
			var api;
			var config;

			beforeEach(function () {
				config = {
					dataTransformations: {
						toApi: function (data) {
							return data;
						},
						fromApi: function (values) {
							return values;
						}
					},
					entityPrototype: {},
					additionalParams: {},
					methods: {
						newAction: function (filters, pagination, sort) {
							return this.getAllEntities(filters, pagination, sort);
						}
					},
					entityName: 'TheEntityName'
				};

				api = apiBuilder.build(config.methods, config.entityName, config.additionalParams,
					config.dataTransformations, config.entityPrototype);
			});

			it('builds the API with custom actions', function () {
				expect('newAction' in api).toBeTruthy();
			});

			describe('internal resource', function () {
				it('gets built with the given configuration', function () {
					expect(resourceBuilder.build).toHaveBeenCalledWith(config.entityName,
						config.additionalParams, config.dataTransformations, config.entityPrototype);
				});
			});

			describe('newAction', function () {
				it('calls the "getAllEntities" action', function () {
					spyOn(api, 'getAllEntities');
					api.newAction();
					expect(api.getAllEntities).toHaveBeenCalled();
				});
			});

			describe('getAllEntities', function () {
				it('calls the "getAll" action from the resource', function () {
					api.getAllEntities();
					expect(resourceBuilderSpy.getAll).toHaveBeenCalled();
				});
			});

			describe('removeEntity', function () {
				it('calls the "remove" action from the resource', function () {
					api.removeEntity();
					expect(resourceBuilderSpy.remove).toHaveBeenCalled();
				});
			});

			describe('saveEntity', function () {
				it('calls the "save" action from the resource', function () {
					api.saveEntity();
					expect(resourceBuilderSpy.save).toHaveBeenCalled();
				});
			});

		});
	});
});
