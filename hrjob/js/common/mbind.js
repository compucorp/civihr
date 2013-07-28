CRM.HRApp.module('Common', function(Common, HRApp, Backbone, Marionette, $, _) {

  /**
   * Converter for checkbox/boolean fields
   *
   * @param direction
   * @param value
   * @return {*}
   */
  Common.convertCheckbox = function(direction, value) {
    switch (direction) {
      case 'ModelToView':
        if (value == "0" || value == 0) return false;
        if (value == "1" || value == 1) return true;
        return value;
        break;
      case 'ViewToModel':
        return value ? "1" : "0";
        break;
      default:
        throw "Invalid direction"
    }
  };

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

    // ModelBinder - Some bindings to use in all our views
    var booleanFieldRE = /^is_/;
    var onBindingCreate = function(bindings) {
      _.each(bindings, function(value, key){
        if (booleanFieldRE.test(key)) {
          value.converter = Common.convertCheckbox;
        }
      });
    };

    // View - onRender listener
    var onRender = function() {
      var bindings = Backbone.ModelBinder.createDefaultBindings(this.el, view.bindingAttribute || 'name');
      onBindingCreate(bindings); // Apply some changes to bindings across all our views
      view.triggerMethod.call(view, 'binding:create', bindings);
      modelBinder.bind(this.model, this.el, bindings);
    };

    // View - onClose listener
    var onClose = function() {
      modelBinder.unbind();
    };

    view.listenTo(view, 'render', onRender);
    view.listenTo(view, 'close', onClose);
  };
});