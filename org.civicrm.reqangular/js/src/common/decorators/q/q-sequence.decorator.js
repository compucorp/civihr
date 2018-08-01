/* eslint-env amd */

define([], function () {
  return ['$delegate', function ($delegate) {
    $delegate.sequence = executePromisesInSequence;

    /**
     * Executes an array of promises sequentially
     *
     * @param {Array} wrappedPromises collection of promises wrapped into functions
     * @return {Promise} resolved to the value of the last promise in the chain
     */
    function executePromisesInSequence (wrappedPromises) {
      var sequence = wrappedPromises.reduce(function (promiseChain, wrappedPromise) {
        if (typeof wrappedPromise !== 'function') {
          throw (new Error('All promises must be wrapped in functions'));
        }

        return promiseChain.then(wrappedPromise);
      }, $delegate.resolve());

      return sequence;
    }

    return $delegate;
  }];
});
