define([
	'common/angularMocks',
	'access-rights/models/location'
], function () {
	'use strict';

	describe('Region', function () {
		var $provide, Region, apiBuilderSpy, apiSpy;

		beforeEach(module('access-rights.models', function ($provide) {
			apiBuilderSpy = jasmine.createSpyObj('apiBuilderSpy', ['build']);
			apiSpy = jasmine.createSpyObj('apiSpy', ['getAllEntities']);
			apiBuilderSpy.build.and.returnValue(apiSpy);
			$provide.value('apiBuilder', apiBuilderSpy);
		}));
		beforeEach(inject(function (_Region_) {
			Region = _Region_;
		}));

		it('calls apiBuilder.build with correct parameters', function () {
			expect(apiBuilderSpy.build.calls.count()).toBe(1);
			expect(apiBuilderSpy.build.calls.mostRecent().args.length).toBe(3);
			expect('getAll' in apiBuilderSpy.build.calls.mostRecent().args[0]).toBeTruthy();
			expect(apiBuilderSpy.build.calls.mostRecent().args[1]).toBe('OptionValue');
			expect(apiBuilderSpy.build.calls.mostRecent().args[2]).toEqual({
	      'option_group_name': 'hrjc_region'
	    });
		});

		describe('getAll', function(){
			it('calls api.getAllEntities', function () {
				apiBuilderSpy.build.calls.mostRecent().args[0].getAll.call(apiSpy, 'filters', 'pagination', 'sort');
				expect(apiSpy.getAllEntities.calls.count()).toBe(1);
				expect(apiSpy.getAllEntities).toHaveBeenCalledWith('filters', 'pagination', 'sort');
			});
		});

	});
});
