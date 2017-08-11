/* eslint-env amd, jasmine */

define([
  'common/lodash',
  'common/angularMocks',
  'common/models/instances/instance'
], function (_) {
  'use strict';

  describe('ModelInstance', function () {
    var ModelInstance;
    var instanceInterface = ['attributes', 'defaultCustomData', 'extend',
      'fromAPI', 'fromAPIFilter', 'init', 'toAPI', 'toAPIFilter'];

    beforeEach(module('common.models.instances'));
    beforeEach(inject(function (_ModelInstance_) {
      ModelInstance = _ModelInstance_;
    }));

    describe('abstract-like functions', function () {
      it('has them defined', function () {
        expect(ModelInstance.defaultCustomData).toBeDefined();
        expect(ModelInstance.fromAPIFilter).toBeDefined();
        expect(ModelInstance.toAPIFilter).toBeDefined();
      });
    });

    describe('init()', function () {
      var instance;

      describe('simple initialization', function () {
        var attributes = { foo: 'foo', bar: 'bar' };

        beforeEach(function () {
          instance = ModelInstance.init(attributes);
        });

        it('create a new instance', function () {
          expect(instance).toEqual(jasmine.any(Object));
        });

        it('retains the same interface', function () {
          expect(_.functions(instance)).toEqual(instanceInterface);
        });

        it('contains the attributes passed to it', function () {
          expect(instance.foo).toBeDefined();
          expect(instance.bar).toBeDefined();
          expect(instance.foo).toEqual(attributes.foo);
          expect(instance.bar).toEqual(attributes.bar);
        });
      });
    });

    describe('attributes()', function () {
      var attributes;

      beforeEach(function () {
        attributes = ModelInstance.init({
          foo: 'foo',
          bar: 'bar',
          fn: function () {}
        })
                .attributes();
      });

      it('returns only the attributes, without the methods', function () {
        expect(attributes).toEqual(jasmine.objectContaining({ foo: 'foo', bar: 'bar' }));
        expect(attributes).not.toEqual(jasmine.objectContaining({ fn: jasmine.any(Function) }));
      });

      it('returns a plain object w/o prototype', function () {
        expect(Object.getPrototypeOf(attributes)).toBe(null);
      });
    });

    describe('extend()', function () {
      var newInstance;

      beforeEach(function () {
        newInstance = ModelInstance.extend({
          foo: 'foo',
          bar: function () {
          }
        });
      });

      it('returns a new instance type extending the basic type', function () {
        expect(newInstance.attributes).toBeDefined();
        expect(newInstance.attributes).toEqual(jasmine.any(Function));
        expect(newInstance.foo).toBeDefined();
        expect(newInstance.bar).toBeDefined();
        expect(newInstance.bar).toEqual(jasmine.any(Function));
      });

      it('keeps the new type functions separated from the basic type ones', function () {
        expect(_.keys(newInstance).filter(function (property) {
          return _.isFunction(newInstance[property]);
        })).toEqual(['bar']);
      });
    });

    describe('toAPI()', function () {
      var instance, toAPIData;

      beforeEach(function () {
        instance = ModelInstance.init({ foo: 'foo', bar: 'bar' });
        toAPIData = instance.toAPI();
      });

      it('returns a plain object w/o prototype', function () {
        expect(Object.getPrototypeOf(toAPIData)).toBe(null);
      });
    });
  });
});
