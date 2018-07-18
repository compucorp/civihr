define([], function() {
  'use strict';

  return function ModalCtrl($rootScope, $modalInstance) {
    this.errorMsg = '';
    this.loading = true;
    this.formFields = [];

    /**
     * Controller initialization
     * @param  {Function} fnGetFormFields Function responsible for loading the form fields
     */
    this.init = function(fnGetFormFields) {
      fnGetFormFields()
        .then(function(data) {
          this.loading = false;
          this.formFields = data;
        }.bind(this))
        .catch(function() {
          this.loading = false;
          this.errorMsg = 'Error while loading form fields';
        }.bind(this));
    };

    /**
     * Closes the modal
     */
    this.cancel = function() {
      $modalInstance.dismiss('cancel');
    };

    /**
     * Saves data and closes the modal
     * @param  {Function} fnSave              Function responsible for saving the data
     * @param  {string}   newContactEventName The name of the event to be broadcasted when new data is created
     */
    this.save = function(fnSave, newContactEventName) {
      this.loading = true;
      var formData = this.formFields.reduce(function(prev, curr) {
        prev[curr.field_name] = curr.value;
        return prev;
      }, {});
      fnSave(formData)
        .then(function(data) {
          this.loading = false;
          $rootScope.$broadcast(newContactEventName, data);
          $modalInstance.dismiss('cancel');
        }.bind(this))
        .catch(function() {
          this.loading = false;
          this.errorMsg = 'Error while saving data';
        }.bind(this));
    };
  };
});
