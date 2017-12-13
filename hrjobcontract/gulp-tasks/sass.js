var exec = require('child_process').exec;

module.exports = function (SubTask) {
  return {
    full: full
  };

  /**
   * The app style relies on compass's gems, so we need to rely on it
   * for the time being
   */
  function full (cb) {
    exec('compass compile', { cwd: __dirname + '/../' }, function (_, stdout, stderr) {
      console.log(stdout);
      cb();
    });
  }
};
