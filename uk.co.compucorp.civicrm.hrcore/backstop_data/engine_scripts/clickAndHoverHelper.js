module.exports = async (puppet, scenario) => {
  var hoverSelector = scenario.hoverSelector;
  var clickSelector = scenario.clickSelector;
  var postInteractionWait = scenario.postInteractionWait; // selector [str] | ms [int]

  if (hoverSelector) {
    await puppet.waitFor(hoverSelector);
    await puppet.hover(hoverSelector);
  }

  if (clickSelector) {
    await puppet.waitFor(clickSelector);
    await puppet.click(clickSelector);
  }

  if (postInteractionWait) {
    await puppet.waitFor(postInteractionWait);
  }
};
