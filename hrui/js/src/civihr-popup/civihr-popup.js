/*
Prevent Popups to overflow tables.
*/
(function ($) {
  'use strict';

  $('body').on('click', '.btn-slide', function (e) {
    var $button = $(this);
    var $body = $('body');
    var $popup, buttonOffset, $closestEntity;

    e.preventDefault();

    (function init () {
      openPopupPanel();
      listenToMouseOutEvent();
    })();

    /**
     * Closes the popup panel and removes the .civihr-popup-open class from the
     * body.
     */
    function closePopupPanel () {
      $body
        .children('.civihr-popup')
        .appendTo($button)
        .removeClass('civihr-popup');

      $body.removeClass('civihr-popup-open');
    }

    /**
     * Listens to mouse events happening outside the of the panel in order to
     * close it. The way to detected mouse out is by listening to the
     * .btn-slide-active class change, which is added and removed by CiviCRM.
     * the data *attrchange-is-on* is set to true in order to avoid adding
     * multiple listeners to the same element.
     */
    function listenToMouseOutEvent () {
      // AttrChange event is already listened, skip:
      if ($button.data('attrchange-is-on')) {
        return;
      }

      $button.data('attrchange-is-on', true);

      $button.attrchange(function () {
        // Button is already open, skip:
        if ($button.hasClass('btn-slide-active')) {
          return;
        }

        closePopupPanel();
      });
    }

    /**
     * Opens the pop panel and adds the .civihr-popup-open class to the
     * body.
     */
    function openPopupPanel () {
      $popup = $button.children('ul.panel');
      $closestEntity = $button.closest('.crm-entity')[0];

      $popup.attr('id', $closestEntity.id);
      $popup.addClass($($closestEntity).attr('class'));

      $popup
        .appendTo($body)
        .addClass('civihr-popup');

      buttonOffset = $button.offset();

      $popup
        .css('top', +buttonOffset.top + $button.outerHeight())
        .css('left', +buttonOffset.left - $popup.width() - $button.outerWidth());

      $body.addClass('civihr-popup-open');
    }
  });
})(CRM.$);
