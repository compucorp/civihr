<?php

/**
 * Class CRM_HRProfile_BAO_Query
 *
 * This class is a little bit different from the regular Query classes, as it's
 * not related to a BAO.
 *
 * The query that loads the data for the Staff Directory is the same one that is
 * used for the AdvancedSearch (with the exception that is uses the Directory
 * Profile to get the fields to be displayed). So this class injects the JOINS
 * and fields required to load the information for the "Managers" field of this
 * profile.
 */
class CRM_HRProfile_BAO_Query extends CRM_Contact_BAO_Query_Interface {

  private $fields = [];

  /**
   * The managers is not a real BAO/DAO field, so we manually add it to the
   * fields list.
   *
   * @return array
   */
  public function &getFields() {
    if(empty($this->fields)) {
      $this->fields['manager_contact'] = array(
        'name'  => 'manager_contact',
        'title' => 'Manager',
        'type'  => CRM_Utils_Type::T_STRING,
        'where' => ''
      );
    }

    return $this->fields;
  }

  /**
   * This method adds the manager_contact field to the query.
   *
   * One contact can have multiple managers, so we use GROUP_CONCAT to group all
   * of them into a single field, separated by commas.
   *
   * @param $query
   */
  public function select(&$query) {
    $query->_select['manager_contact']  = "GROUP_CONCAT(DISTINCT manager_contact.sort_name SEPARATOR ', ') AS manager_contact";
    $query->_element['manager_contact'] = 1;
    $query->_tables['civicrm_relationship']  = $query->_whereTables['civicrm_relationship'] = 1;
  }

  /**
   * This method adds the required statements to fetch the managers of a
   * contact.
   *
   * @param string $name
   * @param $mode
   * @param $side
   *
   * @return string
   */
  public function from($name, $mode, $side) {
    $currentDate = date('Y-m-d');
    $from = '';

    switch ($name) {
      case 'civicrm_contact':
        $from .= "$side JOIN civicrm_relationship cr
                    ON cr.contact_id_a = contact_a.id AND
                       (cr.end_date IS NULL OR cr.end_date >= '{$currentDate}') AND
                       (cr.start_date IS NULL OR cr.start_date <= '{$currentDate}') AND
                       cr.is_active = 1 AND
                       cr.relationship_type_id = (SELECT id FROM civicrm_relationship_type WHERE name_a_b = 'Line Manager Is')
                  $side JOIN civicrm_contact manager_contact
                    ON cr.contact_id_b = manager_contact.id AND manager_contact.is_deleted = 0";
        break;
    }

    return $from;
  }

  /**
   * Even though the CRM_Contact_BAO_Query_Interface doesn't declare this method
   * on its interface, CRM_Contact_BAO_Query still tries to call it. So this
   * empty implementation here is just to avoid an undeclared method error.
   *
   * @param $panes
   */
  public function getPanesMapper(&$panes) {}
}
