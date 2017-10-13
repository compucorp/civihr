CRM.HRLeaveAndAbsencesApp = {};

/**
 * A ListPage is built around a table list of entities,
 * and it adds actions, like "Set as Default" and "Delete",
 * to each of its items
 */
CRM.HRLeaveAndAbsencesApp.ListPage = (function($){

    var messages = {
        'setAsDefault': {
            'confirmation': 'Are you sure you want to set "%1" as default?',
            'success': '"%1" is now the default item'
        },
        'delete': {
            'confirmation': 'Are you sure you want to delete "%1"?',
            'success': '"%1" was deleted'
        }
    };

    /**
     * Constructs the list attach event listeners to the
     * "Set as Default" and "Delete" actions
     *
     * @param {Object} listElement - a jQuery element containing the list of entities
     * @constructor
     */
    function ListPage(listElement) {
        attachEventListeners(listElement);
    }

    /**
     * Attach the event listeners to the actions.
     *
     * Since the list can be updated, we remove any event
     * handler that might have been attached before
     *
     * @param {Object} listElement - a jQuery element containing the list of entities
     */
    function attachEventListeners(listElement) {
        listElement
            .off('click.civihrSetAsDefault')
            .on('click.civihrSetAsDefault', '.action-item.civihr-set-as-default', setAsDefaultAction)
            .off('click.civihrDelete')
            .on('click.civihrDelete', '.action-item.civihr-delete', deleteAction);
    }

    /**
     * This is the "Set As Default" event handler.
     *
     * It instantiates a new SerAsDefaultAction and execute it.
     *
     * @param {Object} event
     */
    function setAsDefaultAction(event) {
        var $target = $(event.target);
        var action = new CRM.HRLeaveAndAbsencesApp.ListPage.SetAsDefaultAction(
            $target,
            messages.setAsDefault.confirmation,
            messages.setAsDefault.success
        );
        action.execute();

        event.preventDefault();
    }

    /**
     * This is the "Delete" event handler.
     *
     * It instantiates a new Delete and execute it.
     *
     * @param {Object} event
     */
    function deleteAction(event) {
        var $target = $(event.target);
        var action = new CRM.HRLeaveAndAbsencesApp.ListPage.DeleteAction(
            $target,
            messages.delete.confirmation,
            messages.delete.success
        );
        action.execute();

        event.preventDefault();
    }

    return ListPage;

})(CRM.$);


/**
 * This is the base class used by any action executed
 * by the ListPage.
 *
 * It wraps all logic to execute an action, including:
 * display a confirmation action, display a success
 * message and refresh the list after it has been
 * updated.
 *
 * Child classes need only to implement the _executeAction,
 * which is called when the user confirms the action
 * execution.
 */
CRM.HRLeaveAndAbsencesApp.ListPage.Action = (function() {

    /**
     * Creates a new Action
     *
     * The title and the messages are translated before
     * they are displayed. You can add the entity' title
     * to the confirmation and success messages by adding
     * the %1 placeholder.
     *
     * @param {Object} target - this jQuery object of the element that triggered the action
     * @param {string} title - the title of the confirmation message dialog
     * @param {string} confirmationMessage - the confirmation message to this action
     * @param {string} successMessage - the message displayed when the action is successful
     *
     * @constructor
     */
    function Action(target, title, confirmationMessage, successMessage) {
        this._target = target;
        this._listRow = target.closest('.crm-entity');
        this._entity = target.crmEditableEntity();
        this._title = title;
        this._confirmationMessage = confirmationMessage;
        this._successMessage = successMessage;
    }

    Action.prototype.execute = function() {
        this._showConfirmation();
    };

    /**
     * Shows the action's confirmation message and, if the
     * user confirms it, executes the action.
     *
     * @private
     */
    Action.prototype._showConfirmation = function() {
        CRM.confirm({
            title: ts(this._title),
            message: ts(this._confirmationMessage, { 1: this._entity.title }),
            options: {
                yes: ts('Yes'),
                no: ts('No')
            }
        })
        .on('crmConfirm:yes', this._executeAction.bind(this));
    };

    /**
     * Refresh the list to show after the item has
     * been updated
     *
     * @private
     */
    Action.prototype._refresh = function() {
        CRM.refreshParent(this._listRow);
    };

    /**
     * Returns the processed success message.
     *
     * The message is translated and the title of
     * entity being processed by the action can be
     * added to the message with a %1
     *
     * @returns {string} the processed message
     * @private
     */
    Action.prototype._getSuccessMessage = function() {
        return ts(this._successMessage, {1: this._entity.title})
    };

    return Action;
})();

/**
 * This is the Action implementation to set an item as default.
 *
 * It will use the API to change the entity is_default to 1.
 */
CRM.HRLeaveAndAbsencesApp.ListPage.SetAsDefaultAction = (function() {

    function SetAsDefaultAction(target, confirmationMessage, successMessage) {
        CRM.HRLeaveAndAbsencesApp.ListPage.Action.call(
            this, target, 'Set as default', confirmationMessage, successMessage
        );
    }

    SetAsDefaultAction.prototype = Object.create(CRM.HRLeaveAndAbsencesApp.ListPage.Action.prototype);

    SetAsDefaultAction.prototype._executeAction = function() {
        CRM.api3(
            this._entity.entity,
            'create',
            { id: this._entity.id, is_default: 1 },
            { success: this._getSuccessMessage.bind(this) }
        ).done(this._refresh.bind(this));
    };

    return SetAsDefaultAction;
})();

/**
 * This is the Action implementation to delete an item.
 *
 * It will use the API to delete the entity.
 */
CRM.HRLeaveAndAbsencesApp.ListPage.DeleteAction = (function() {

    function DeleteAction(target, confirmationMessage, successMessage) {
        CRM.HRLeaveAndAbsencesApp.ListPage.Action.call(
            this, target, 'Delete', confirmationMessage, successMessage
        );
    }

    DeleteAction.prototype = Object.create(CRM.HRLeaveAndAbsencesApp.ListPage.Action.prototype);

    DeleteAction.prototype._executeAction = function() {
        CRM.api3(
            this._entity.entity,
            'delete',
            { id: this._entity.id },
            { success: this._getSuccessMessage.bind(this) }
        ).done(this._refresh.bind(this));
    };

    return DeleteAction;
})();
