<?php

/**
 * Display the CiviHR version
 *
 * Usage:
 * @code
 * The version is {crmVersion}.
 * @endcode
 *
 * @param array $params
 * @param Smarty $smarty
 *
 * @return string
 */
function smarty_function_civihrVersion($params, &$smarty) {
  return CRM_HRCore_Info::getVersion() ?: t('unknown');
}
