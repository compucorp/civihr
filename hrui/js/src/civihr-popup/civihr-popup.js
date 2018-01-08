/* globals CRM */

/*
Prevent Popups to overflow tables.
*/
(function ($) {
  'use strict';
  var $body = $('body');

  $body.on('click', '.btn-slide', function () {
    var $popupClone;
    var $button = $(this);
    var $popup = $button.children('ul.panel');

    (function init () {
      closePopupPanels();
      createPopupClone();
      openPopupClone();
      mapCloneClickEventsToOrigin();
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
      $popupClone = $popup.clone(true)
        .appendTo($body)
        .addClass('civihr-popup');
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
     */
    function mapCloneClickEventsToOrigin () {
      $popupClone.find('a').click(function () {
        var actionIndex = $(this).parent().index();

        $popup.find('li:nth(' + actionIndex + ') a').click();
      });
    }

    /**
     * Opens the popup panel clone
     * and adds the .civihr-popup-open class to the body
     */
    function openPopupClone () {
      var buttonOffset = $button.offset();

      $popupClone.css({
        left: +buttonOffset.left - ($popupClone.width() - $button.outerWidth()),
        top: +buttonOffset.top + $button.outerHeight()
      });
      $popupClone.show();
      $body.addClass('civihr-popup-open');
    }
  });
})(CRM.$);
