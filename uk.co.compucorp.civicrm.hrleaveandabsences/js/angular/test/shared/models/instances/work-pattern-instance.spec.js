/* eslint-env amd, jasmine */
/* global inject */

define([
  'common/lodash',
  'leave-absences/shared/models/instances/work-pattern-instance'
], function (_) {
  'use strict';

  describe('WorkPatternInstance', function () {
    var ModelInstance, WorkPatternInstance;

    beforeEach(module('leave-absences.models.instances'));

    beforeEach(inject(['ModelInstance', 'WorkPatternInstance',
      function (_ModelInstance_, _WorkPatternInstance_) {
        ModelInstance = _ModelInstance_;
        WorkPatternInstance = _WorkPatternInstance_;
      }
    ]));

    it('inherits from ModelInstance', function () {
      expect(_.functions(WorkPatternInstance)).toEqual(jasmine.arrayContaining(_.functions(ModelInstance)));
    });
  });
});
