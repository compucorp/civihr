var exec = require('child_process').exec;

module.exports = function () {
  return {
    /**
     * The app style relies on compass's gems, so we need to rely on it
     * for the time being
     *
     * @NOTE: will be removed when hrjobcontract is migrated to Shoreditch
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
