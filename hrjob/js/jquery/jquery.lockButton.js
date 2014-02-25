// Copyright CiviCRM LLC 2013. See http://civicrm.org/licensing
(function($) {
  // Display a 'lock/unlock' button. When locked, the value of another
  // element is programmatically dictated.
  //
  // Usage:
  //  <input name="my_text" type="text" />
  //  <input name="is_locked" type="hidden" value="1" />
  //  $('[name=is_locked]').lockButton({
  //    for: '[name=my_text]',
  //    value: 'Hard Coded Value'
  //  });

  var isFunction = function(obj) {
    return !!(obj && obj.constructor && obj.call && obj.apply);
  };

  $.widget("custom.lockButton", {
    options: {
      // A reference to the element which is managed by this lock-button
      for: null,
      // The value to put into the managed element; or a function which computes the value
      value: null,
      lockedText: 'Locked',
      unlockedText: 'Unlocked'
    },
    _create: function() {
      var widget = this;
      this.icon = $('<a href="#"></a>')
        .addClass('ui-icon lock-button')
        .click(function() {
          widget.toggle();
          return false;
        });
      this.element.after(this.icon);
      this.element.on('change', function() {
        widget.refresh();
      })
      this.refresh();
    },
    _destroy: function() {
      if (this.options.for) {
        $(this.options.for).prop('disabled', false);
      }
      this.icon.remove();
      delete this.icon;
    },
    _setOptions: function(options) {
      this._super(options);
      this.refresh();
    },
    isLocked: function() {
      return this.element.val() == '1';
    },
    toggle: function() {
      this.element.val(this.isLocked() ? '0' : '1');
      this.element.trigger('change');
    },
    refresh: function() {
      if (this.isLocked()) {
        this.icon
          .removeClass('ui-icon-unlocked')
          .addClass('ui-icon-locked')
          .prop('title', this.options.lockedText)
        ;
        if (this.options.for) {
          var $for = $(this.options.for);
          var newValue = isFunction(this.options.value) ? this.options.value() : this.options.value;
          if (newValue != null && $for.val() != newValue) {
            $for.val(newValue);
            $for.trigger('change');
          }
          $for.prop('disabled', true);
        }
      } else {
        this.icon
          .removeClass('ui-icon-locked')
          .addClass('ui-icon-unlocked')
          .prop('title', this.options.unlockedText)
        ;
        if (this.options.for) {
          $(this.options.for).prop('disabled', false);
        }
      }
    }
  });
})(jQuery);
