/* eslint-env amd */

define([
  'common/lodash'
], function (_) {
  'use strict';

  contractRevisionListService.$inject = [
    '$filter', '$q', '$log', 'contractService', 'contractFilesService',
    'contractRevisionService'
  ];

  function contractRevisionListService ($filter, $q, $log, contractService,
    contractFilesService, contractRevisionService) {
    $log.debug('Service: contractRevisionListService');

    return {
      fetchRevisions: fetchRevisions
    };

    /**
     * Takes in a bulk of data, the result of a joined api call, and
     * returns an object containing such data divided in different properties
     *
     * @param  {Object} aggregated
     * @return {Object}
     */
    function expandAggregatedRevisionDetails (aggregated) {
      return {
        details: {
          position: aggregated['details_revision_id.position'],
          location: aggregated['details_revision_id.location']
        },
        hour: {
          hours_type: aggregated['hour_revision_id.hours_type']
        },
        pay: {
          pay_annualized_est: aggregated['pay_revision_id.pay_annualized_est'],
          pay_currency: aggregated['pay_revision_id.pay_currency'],
          pay_scale: aggregated['pay_revision_id.pay_scale']
        }
      };
    }

    /**
     * Fetches the details of the given revision, like files, pay, etc
     *
     * The bulk of the data is fetched via a joined api call, whose aggregated
     * result is then expanded before being returned
     *
     * @param  {Object} revision
     * @return {Promise} resolves to an object containing the details
     */
    function fetchRevisionDetails (revision) {
      revision.effective_date = revision.effective_date || '';

      return $q.all({
        files: {
          details: contractFilesService.get(revision.details_revision_id, 'civicrm_hrjobcontract_details')
        },
        aggregated: contractRevisionService.get({
          action: 'getsingle',
          json: {
            sequential: 1,
            id: revision.id,
            return: [
              'details_revision_id.position',
              'details_revision_id.location',
              'hour_revision_id.hours_type',
              'pay_revision_id.pay_scale',
              'pay_revision_id.pay_annualized_est',
              'pay_revision_id.pay_currency'
            ]
          }
        })
          .$promise.then(function (aggregated) {
            return aggregated;
          })
      })
        .then(function (results) {
          return _.assign({
            revisionEntityIdObj: revision,
            files: results.files
          }, expandAggregatedRevisionDetails(results.aggregated));
        });
    }

    /**
     * Fetches available revision list for acontract
     *
     * @param  {integer | string} contractId
     * @return {object}
     */
    function fetchRevisions (contractId) {
      var RevisionList = [];
      var deferred = $q.defer();

      contractService.getRevision(contractId)
        .then(function (revisionList) {
          RevisionList = $filter('orderBy')(revisionList, ['-effective_date', '-id']);

          return $q.all(RevisionList.map(fetchRevisionDetails));
        })
        .then(function (results) {
          deferred.resolve({
            revisionList: RevisionList,
            revisionDataList: results
          });
        });

      return deferred.promise;
    }
  }

  return { contractRevisionListService: contractRevisionListService };
});
