/* globals CRM */

/*
Prevent Popups to overflow tables.
*/
(function ($) {
  'use strict';
  var $body = $('body');

  $body.on('click', '.btn-slide', function () {
    var $button = $(this);
    var $closestEntity = $button.closest('.crm-entity')[0];
    var $popup = $button.children('ul.panel');

    (function init () {
      openPopupPanel();
      listenToMouseOutEvent();
    })();

    /**
     * Closes the any popup panel that is currently open and removes the
     * `.civihr-popup-open` class from the body.
     */
    function closePopupPanels () {
      $('.civihr-popup').remove();
      $body.removeClass('civihr-popup-open');
    }

    /**
     * Creates a clone of the popup element and appends it to the document body.
     * This prevents the popup to be hidden by any `overflow: hidden;` rule.
     */
    function createPopupClone () {
      var buttonOffset = $button.offset();
      var $clone = $popup.clone(true)
        .appendTo($body)
        .addClass('civihr-popup')
        .attr('id', $closestEntity.id)
        .addClass($($closestEntity).attr('class'));

      $clone.css({
        left: +buttonOffset.left - ($clone.width() - $button.outerWidth()),
        top: +buttonOffset.top + $button.outerHeight()
      });
      $clone.show();
      mapCloneClickEventsToOrigin($clone);
    }

    /**
     * Listens to mouse events happening outside the of the panel in order to
     * close it. The way to detected mouse out is by listening to the
     * .btn-slide-active class change, which is added and removed by CiviCRM.
     * the data *attrchange-is-on* is set to true in order to avoid adding
     * multiple listeners to the same element.
     */
    function listenToMouseOutEvent () {
      // If AttrChange event is already listened, then skip:
      if ($button.data('attrchange-is-on')) {
        return;
      }

      $button.data('attrchange-is-on', true);
      $button.attrchange(function () {
        // If button is already open, then skip:
        if ($button.hasClass('btn-slide-active')) {
          return;
        }

        closePopupPanels();
      });
    }

    /**
     * Maps click events on the popup options back to their original source.
     * This is done because popup actions are executed as delegated events and
     * the listener is not the *body* element.
     *
     * @param {Object} $clone - The jQuery clone element to map
     * click events from.
     */
    function mapCloneClickEventsToOrigin ($clone) {
      $clone.find('a').click(function () {
        var actionIndex = $(this).parent().index();

        $popup.find('li:nth(' + actionIndex + ') a').click();
      });
    }

    /**
     * Opens the popup panel
     * and adds the .civihr-popup-open class to the body
     */
    function openPopupPanel () {
      closePopupPanels();
      createPopupClone();
      $body.addClass('civihr-popup-open');
    }
  });
})(CRM.$);
