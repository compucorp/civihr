var modal = require('./page');

module.exports = modal.extend({
  /**
   * The Iframe embedding the SSP reports in the CiviCRM admin adapts its height
   * dynamically whenever the viewport resizes. This can lead to false positives
   * due to the resize that headless Chrome performs just before taking the screenshot
   * (the iframe would not resize consistently every time, leading BackstopJS to
   * report height differences of a handful of pixels)
   *
   * In order to avoid false positives, the height of the iframe is fixed
   * by applying a generated-on-the-fly style to it, so that the height can't
   * change when Chrome resizes the viewport.
   */
  waitForReady: function () {
    this.chromy.evaluate(function () {
      var tempStyle = document.createElement('style');
      tempStyle.type = 'text/css';
      tempStyle.innerHTML = '#reportsIframe { height: 1000px !important; }';

      document.getElementsByTagName('head')[0].appendChild(tempStyle);
    });
  }
});
