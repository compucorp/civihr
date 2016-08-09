jQuery(function() {
  'use strict';
  /**
   * The customized radio buttons / checkboxes depend on the existence of the
   * "for" attribute in the respective labels.
   * This is a workaround to add any missing "for" attributes to the markup.
   * @see: org.civicrm.bootstrapcivicrm/scss/civicrm/common/_radio-checkbox.scss
   */
  jQuery('.crm-container input[type=checkbox], .crm-container input[type=radio]').each(function(checkbox) {
    var $this = jQuery(this);
    if (!$this.attr('id')) {
      // There's no id. Add a unique random value as the id
      $this.attr('id', new Date().valueOf());
    }
    var label = $this.next('label');
    if (label.length === 0) {
      // There's sibling label. Let's just show the checkbox / radio button, in order to avoid feature loss
      $this.show();
    } else if (!label.attr('for')) {
      label.attr('for', $this.attr('id'));
    }
  });
});
