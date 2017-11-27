/* eslint-env amd */

/**
 * ControllerOnChanges is a helper to mock calls to the $onChanges method of
 * controllers.
 */
define(function () {
  return {
    /**
     * Stores the controller to use when making the $onChanges calls.
     *
     * @param {Object} controller - the referece to the controller.
     */
    setupController: function (controller) {
      this.controller = controller;
    },

    /**
     * Executes the $onChanges call on the stored controller.
     *
     * @param {String} bindingName - The name of the binding.
     * @param {Any} bindingValue - The new value for the binding.
     */
    mockChange: function (bindingName, bindingValue) {
      var changes = {};
      var previousValue = this.controller[bindingName];

      this.controller[bindingName] = bindingValue;
      changes[bindingName] = {
        currentValue: bindingValue,
        previousValue: previousValue
      };

      this.controller.$onChanges(changes);
    }
  };
});
