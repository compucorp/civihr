/* eslint-env amd */

(function ($, _) {
  define(function () {
    'use strict';

    // for contact edit screen
    $(document).ready(function () {
      // Call contactImLink function on page load
      contactImLink();
    });

    // for inline edit
    $(document).ajaxSuccess(function (event, xhr, settings) {
      // Call contactImLink function if url has class name CRM_Contact_Page_Inline_IM
      if (settings.url.search('class_name=CRM_Contact_Page_Inline_IM') > 0) {
        contactImLink();
      }
    });

    function contactImLink () {
      // build array for IM and its protocol
      var params = {
        'Yahoo': 'ymsgr:sendIM?',
        'Skype': 'skype:',
        // 'GTalk':'gtalk:chat?jid=', // error message doesn't work in FF/OSX
        'AIM': 'aim:goim?screenname=',
        'Jabber': 'xmpp:',
        'MSN': 'skype:'
      };
      $('#crm-im-content .crm-summary-row').each(function () {
        if (this) {
          // get providerlabels
          var providerLabel = $(this).find('.crm-label').text();
          // get IM address
          var imName = $(this).find('.crm-contact_im').text();
          if (imName) {
            var providerName = providerLabel.substr(0, providerLabel.match(/\s[(]/).index);
            if (params[providerName]) {
              // build links of IM address
              var clickableIM = '<a href="' + params[providerName] + '' + imName + '">' + imName + '</a>';
              $(this).find('.crm-contact_im').html(clickableIM);
            }
          }
        }
      });
      $('.crm-contact_im a').on('click', function () {
        CRM.alert("Having trouble? <a href='https://civicrm.org/go/im-support' target='_blank'>Click here to discuss</a>", 'Experimental: Instant Messaging', 'notice');
      });
    }
  });
}(CRM.$, CRM._));
