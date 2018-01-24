var exec = require('child_process').exec;

module.exports = function () {
  return {
    /**
     * The app style relies on compass's gems, so we need to rely on it
     * for the time being
     *
     * @param {Function} done
     */
    main: function (done) {
      exec('compass compile', { cwd: __dirname + '/../' }, function (_, stdout, stderr) {
        console.log(stdout);
        done();
      });
    }
  };
};
