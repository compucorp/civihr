/*
Prevent Popups to overflow tables.
*/
(function ($) {
  'use strict';

  // .btn-slide is a continer of popup and a button triggering it
  $('.btn-slide').attrchange(function (e) {
    var $button = $(this), $body = $('body'), $popup, buttonOffset, $closestEntity;

    // check if popup is open
    if ($button.hasClass('btn-slide-active')) {
      $popup = $button.children('ul.panel');
      $closestEntity = $button.closest('.crm-entity')[0];

      $popup.attr("id",$closestEntity.id);
      $popup.addClass($($closestEntity).attr("class"));

      $popup
        .appendTo($body)
        .addClass('civihr-popup');

      buttonOffset = $button.offset();

      $popup
        .css('top', parseInt(buttonOffset.top, 10) + $button.outerHeight())
        .css('left', parseInt(buttonOffset.left, 10) - ($popup.width() - $button.outerWidth()));

      $body.addClass('civihr-popup-open');
    } else {

      $body
        .children('.civihr-popup')
        .appendTo($button)
        .removeClass('civihr-popup');

      $body.removeClass('civihr-popup-open');
    }

  });
})(CRM.$);
