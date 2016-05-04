define([
	'common/angular',
	'common/angularMocks',
	'common/services/api/resource-builder'
], function () {
	'use strict';

	describe('resourceBuilder', function () {
		var resourceBuilder, $httpBackend;

		beforeEach(module('common.services'));
		beforeEach(inject(['resourceBuilder', '$httpBackend',
			function (_resourceBuilder_, _$httpBackend_) {
				resourceBuilder = _resourceBuilder_;
				$httpBackend = _$httpBackend_;
			}
		]));

		describe('build', function () {
      var resource;
      beforeEach(function () {
        resource = resourceBuilder.build(entityName, additionalParams, dataTransformations, entityPrototype);
      });

			it('builds a new angular resource, with the given configuration', function () {
				$httpBackend.expect('GET', '/say-hello').respond(200, 'success');

				resource.sayHello("your name");
				$httpBackend.flush();
				expect(OriginalResource.saidHello).toBe(true);
			});
		});
	});
});
