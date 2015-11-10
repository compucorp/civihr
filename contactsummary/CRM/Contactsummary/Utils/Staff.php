<?php

class CRM_Contactsummary_Utils_Staff {
  /**
   * Get the total number of staff.
   *
   * @return int
   */
  public static function getStaffNum() {
    $query = "
    SELECT COUNT(DISTINCT c.id) count
    FROM civicrm_contact c

    INNER JOIN civicrm_hrjobcontract jc
    ON (c.id = jc.contact_id AND jc.is_primary = 1)

    INNER JOIN civicrm_hrjobcontract_revision jcr
    ON (jc.id = jcr.jobcontract_id AND jcr.effective_date <= NOW())

    INNER JOIN civicrm_hrjobcontract_details jcd
    ON (jcr.id = jcd.jobcontract_revision_id AND (jcd.period_end_date >= NOW() OR jcd.period_end_date IS NULL))

    INNER JOIN civicrm_relationship civicrm_relationship ON c.id = civicrm_relationship.contact_id_a

    INNER JOIN civicrm_contact c_b
    ON civicrm_relationship.contact_id_b = c_b.id

    INNER JOIN civicrm_relationship c_b_r
    ON (c_b.id = c_b_r.contact_id_a AND (c_b_r.is_active <> 0 OR c_b_r.relationship_type_id = 18))

    WHERE c.contact_type = 'Individual'";

    $total = 0;

    $dao = CRM_Core_DAO::executeQuery($query);
    if ($dao->fetch()) {
      $total = $dao->count;
    }

    return $total;
  }
}