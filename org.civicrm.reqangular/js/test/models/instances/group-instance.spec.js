/* eslint-env amd, jasmine */

define([
  'common/lodash',
  'common/angularMocks',
  'common/models/instances/group-instance'
], function (_) {
  'use strict';

  describe('GroupInstance', function () {
    var GroupInstance, ModelInstance;

    beforeEach(module('common.models.instances'));
    beforeEach(inject(function (_GroupInstance_, _ModelInstance_) {
      GroupInstance = _GroupInstance_;
      ModelInstance = _ModelInstance_;
    }));

    it('inherits from ModelInstance', function () {
      expect(_.functions(GroupInstance)).toEqual(jasmine.arrayContaining(_.functions(ModelInstance)));
    });

    describe('init()', function () {
      var instance;

      describe('when initializing with data from the API', function () {
        var attributes = {
          id: '1',
          name: 'name',
          title: 'title',
          description: 'description',
          is_active: '1',
          visibility: 'visibility',
          where_clause: 'where clause',
          select_tables: 'select tables',
          where_tables: 'where tables',
          group_type: '2',
          is_hidden: '0',
          is_reserved: '0'
        };

        beforeEach(function () {
          instance = GroupInstance.init(attributes, true);
        });

        it('normalizes the data', function () {
          expect(Object.keys(instance.attributes())).toEqual([
            'id', 'name', 'title', 'description'
          ]);
          expect(instance.id).toEqual(attributes.id);
          expect(instance.description).toEqual(attributes.description);
        });
      });
    });
  });
});
