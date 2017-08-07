/* eslint-env amd */

define([], function () {
  return ['$delegate', function ($delegate) {
    $delegate.sequence = executePromisesInSequence;

    /**
     * Executes an array of promises sequentially
     *
     * @param {Array} promises collection of promises
     * @return {Promise}
     */
    function executePromisesInSequence (promises) {
      var sequence = promises.reduce(function (promiseChain, promise) {
        return promiseChain.then(promise);
      }, $delegate.resolve());

      return $delegate.all(sequence);
    }

    return $delegate;
  }];
});
