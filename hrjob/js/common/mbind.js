// Copyright CiviCRM LLC 2013. See http://civicrm.org/licensing
CRM.HRApp.module('Common', function(Common, HRApp, Backbone, Marionette, $, _) {

  /**
   * Converter for checkbox/boolean fields
   *
   * Note that this works well with *only* checkboxes. If the template does
   * or could include a mix of widgets for the same field, then you would need
   * to define separate bindings (with separate converters) for each widget.
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
   * Converter for currency
   *
   * Note: If a field uses alternate currencies, then you should declare how to
   * lookup the currency in every binding, e.g.
   *
   * <span name="pay_amount" data-currency-field="pay_currency" />
   *
   * @param direction
   * @param value
   * @return string
   */
  Common.formatCurrency = function(direction, value, fieldName, model, elements) {
    var currencyFormat;
    if (elements.length > 0 && $(elements[0]).attr('data-currency-field')) {
      var currency = model.get($(elements[0]).attr('data-currency-field'));
      currencyFormat = CRM.jobTabApp.currencies[currency];
    }
    switch (direction) {
      case 'ModelToView':
        return CRM.formatMoney(value, currencyFormat);
        break;
      case 'ViewToModel':
        return value;
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

    // View - onRender listener
    var onRender = function() {
      var bindings = Backbone.ModelBinder.createDefaultBindings(this.el, view.bindingAttribute || 'name');
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