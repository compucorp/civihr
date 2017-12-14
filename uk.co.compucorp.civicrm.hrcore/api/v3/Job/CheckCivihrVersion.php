<?php

/**
 * Job.send_civihr_stats API
 *
 * @return array API result
 */
function civicrm_api3_job_check_civihr_version() {
  $container = Civi::container();
  $currentStats = Civi::container()->get('civihr_stats_cache')->fetchCurrent();
  $container->get('civihr_stats_sender')->send($currentStats);

  return civicrm_api3_create_success();
}
