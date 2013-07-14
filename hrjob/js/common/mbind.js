CRM.HRApp.module('Common', function(Common, HRApp, Backbone, Marionette, $, _) {

  /**
   * Marionette-ModelBinding helper
   *
   * Register a modelBinder for use with a Marionette view. The ModelBinder will
   * participate in Marionette events.
   *
   * Key integration points:
   *  - The view may define a property "bindingAttribute" which specifies the DOM attribute containing the bindings
   *  - The view may define a listner for onBindingCreate which can alter the bindings before they are applied
   *
   * @param view
   * @return void
   */

  Common.mbind = function(view) {
    var modelBinder = new Backbone.ModelBinder();

    var onRender = function() {
      var bindings = Backbone.ModelBinder.createDefaultBindings(this.el, view.bindingAttribute || 'name');
      view.triggerMethod.call(view, 'binding:create', bindings);
      modelBinder.bind(this.model, this.el, bindings);
    };

    var onClose = function() {
      modelBinder.unbind();
    };

    view.listenTo(view, 'render', onRender);
    view.listenTo(view, 'close', onClose);
  };
});