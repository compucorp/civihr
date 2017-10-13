// Create the namespaces if they don't exist
CRM.HRLeaveAndAbsencesApp = CRM.HRLeaveAndAbsencesApp || {};
CRM.HRLeaveAndAbsencesApp.List = CRM.HRLeaveAndAbsencesApp.List || {};

/**
 * The AbsencePeriod list has a specific action not available on
 * other list pages of this extension, hence we need this specialized class.
 *
 * As the ListPage class cannot be easily extended, we just wrap it and
 * add the new action needed for this list.
 */
CRM.HRLeaveAndAbsencesApp.List.AbsencePeriod = (function($) {

  /**
   * Creates a new instance of the AbsencePeriod list
   *
   * @param {Object} listElement - a jQuery element containing the list of entities
   * @constructor
   */
  function AbsencePeriod(listElement) {
    this._listElement = listElement;
    this._listPage = new CRM.HRLeaveAndAbsencesApp.ListPage(listElement);
    this._addEventListeners();
  }

  /**
   * Add event listeners to events triggered by actions specific to this
   * list
   *
   * @private
   */
  AbsencePeriod.prototype._addEventListeners = function() {
    this._listElement.find('.civihr-manage-entitlements')
      .on('click', this._onManageEntitlementsClick.bind(this));
  };

  /**
   * This is the event handler for when the user clicks on the "Manage Entitlements"
   * action on the Absence Period list
   *
   * It instantiates a new ManageEntitlementAction and executes it.
   *
   * @param {Object} event
   * @private
   */
  AbsencePeriod.prototype._onManageEntitlementsClick = function(event) {
    event.preventDefault();
    var $target = $(event.target);
    var action = new CRM.HRLeaveAndAbsencesApp.List.AbsencePeriod.ManageEntitlementAction(
      $target,
      'The system will now update the staff members leave entitlement.'
    );
    action.execute();
  };

  return AbsencePeriod;

})($);

/**
 * This is the List Action implementation to the "Manage Entitlement" action.
 *
 * It will show a confirmation to the user, saying that all entitlements will be
 * updated and, if the user confirms, redirects they to the Entitlement Calculation
 * page.
 */
CRM.HRLeaveAndAbsencesApp.List.AbsencePeriod.ManageEntitlementAction = (function() {

  /**
   * Creates a new action instance
   *
   * @param {Object} target - The element that triggered this action
   * @param {String} confirmationMessage - The confirmation message to be displayed to the user
   * @constructor
   */
  function ManageEntitlementAction(target, confirmationMessage) {
    CRM.HRLeaveAndAbsencesApp.ListPage.Action.call(
      this, target, 'Update leave entitlement?', confirmationMessage, ''
    );
  }

  ManageEntitlementAction.prototype = Object.create(CRM.HRLeaveAndAbsencesApp.ListPage.Action.prototype);

  /**
   * Executes this action by redirecting the user to the Entitlement Calculation page
   *
   * @private
   */
  ManageEntitlementAction.prototype._executeAction = function() {
    var manageEntitlementsURL = this._target.attr('href');
    if(manageEntitlementsURL) {
      window.location = manageEntitlementsURL;
    }
  };

  return ManageEntitlementAction;
})();
