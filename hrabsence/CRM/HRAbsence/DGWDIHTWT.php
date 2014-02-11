<?php

/**
 * Dear God Why Do I Have To Write This (Dumb SQL Builder)
 *
 * Usage:
 * $select = new CRM_HRAbsence_DGWDIHTWT('civicrm_activity act');
 * $select
 *     ->join('absence', 'inner join civicrm_activity absence on absence.id = act.source_record_id')
 *     ->where('activity_type_id = #type', array('#type' => 234))
 *     ->where('status_id IN (#statuses)', array('#statuses' => array(1,2,3))
 *     ->where('subject like @subj', array('@subj' => '%hello%'))
 *     ->where('!dynamicColumn = 1', array('!dynamicColumn' => 'coalesce(is_active,0)'))
 * echo $select->toSQL();
 *
 * Design principles:
 *  - No particular knowledge of the underlying SQL system (except for escaping rules)
 *  - No particular knowledge of the underlying data model
 *  - SQL clauses correspond to PHP functions ("WHERE foo = bar" => $select->where("foo=bar"))
 *  - PHP functions accept individual expressions or lists of expressions
 *  - PHP functions accept variables for interpolation into the SQL expression
 *  - Variables use prefixing - eg "@varname" for escaped strings; "#varname" for ints; "!varname" for raw strings
 *  - Variables may be individual values or arrays
 *  - Conditionals are AND'd; if you need OR's, do it yourself
 */
class CRM_HRAbsence_DGWDIHTWT {
  private $selects = array();
  private $from;
  private $joins = array();
  private $wheres = array();
  private $groupBys = array();
  private $havings = array();
  private $orderBys = array();

  public function __construct($from) {
    $this->from = $from;
  }

  public function join($name, $expr, $args = NULL) {
    $this->joins[$name] = $this->interpolate($expr, $args);
    return $this;
  }

  /**
   * @param string|array $exprs list of SQL expressions
   * @param null|array $args use NULL to disable interpolation; use an array of variables to enable
   * @return CRM_HRAbsence_DGWDIHTWT
   */
  public function select($exprs, $args = NULL) {
    $exprs = (array) $exprs;
    foreach ($exprs as $expr) {
      $this->selects[$expr] = $this->interpolate($expr, $args);
    }
    return $this;
  }

  /**
   * @param string|array $exprs list of SQL expressions
   * @param null|array $args use NULL to disable interpolation; use an array of variables to enable
   * @return CRM_HRAbsence_DGWDIHTWT
   */
  public function where($exprs, $args = NULL) {
    $exprs = (array) $exprs;
    foreach ($exprs as $expr) {
      $this->wheres[$expr] = $this->interpolate($expr, $args);
    }
    return $this;
  }

  /**
   * @param string|array $exprs list of SQL expressions
   * @param null|array $args use NULL to disable interpolation; use an array of variables to enable
   * @return CRM_HRAbsence_DGWDIHTWT
   */
  public function groupBy($exprs, $args = NULL) {
    $exprs = (array) $exprs;
    foreach ($exprs as $expr) {
      $this->groupBys[$expr] = $this->interpolate($expr, $args);
    }
    return $this;
  }

  /**
   * @param string|array $exprs list of SQL expressions
   * @param null|array $args use NULL to disable interpolation; use an array of variables to enable
   * @return CRM_HRAbsence_DGWDIHTWT
   */
  public function having($exprs, $args = NULL) {
    $exprs = (array) $exprs;
    foreach ($exprs as $expr) {
      $this->havings[$expr] = $this->interpolate($expr, $args);
    }
    return $this;
  }

  /**
   * @param string|array $exprs list of SQL expressions
   * @param null|array $args use NULL to disable interpolation; use an array of variables to enable
   * @return CRM_HRAbsence_DGWDIHTWT
   */
  public function orderBy($exprs, $args = NULL) {
    $exprs = (array) $exprs;
    foreach ($exprs as $expr) {
      $this->orderBys[$expr] = $this->interpolate($expr, $args);
    }
    return $this;
  }

  /**
   * Given a string like "field_name = @value", replace "@value" with an escaped SQL string
   *
   * @param string SQL expression
   * @param null|array $args a list of values to insert into the SQL expression; keys are prefix-coded:
   *   prefix '@' => escape SQL
   *   prefix '#' => literal number, skip escaping but do validation
   *   prefix '!' => literal, skip escaping and validation
   *   if a value is an array, then it will be imploded
   * @return CRM_HRAbsence_DGWDIHTWT
   */
  public function interpolate($expr, $args) {
    if ($args === NULL) {
      return $expr;
    }
    else {
      foreach (array_keys($args) as $key) {
        $values = is_array($args[$key]) ? $args[$key] : array($args[$key]);
        if ($key{0} == '@') {
          $parts = array_map(array('CRM_Core_DAO', 'escapeString'), $values);
          $args[$key] = '"' . implode('", "', $parts) . '"';
        }
        elseif ($key{0} == '!') {
          $args[$key] = implode(', ', $values);
        }
        elseif ($key{0} == '#') {
          foreach ($values as $value) {
            if (!is_numeric($value)) {
              //throw new API_Exception("Failed encoding non-numeric value" . var_export(array($key => $args[$key]), TRUE));
              throw new API_Exception("Failed encoding non-numeric value");
            }
          }
          $args[$key] = implode(', ', $values);
        }
        else {
          throw new API_Exception("Bad SQL parameter key: $key");
        }
      }
      return strtr($expr, $args);
    }
  }

  public function toSQL() {
    if ($this->selects) {
      $sql = 'SELECT ' . implode(', ', $this->selects) . "\n";
    }
    else {
      $sql = 'SELECT *' . "\n";
    }
    $sql .= 'FROM ' . $this->from . "\n";
    foreach ($this->joins as $join) {
      $sql .= $join . "\n";
    }
    if ($this->wheres) {
      $sql .= 'WHERE (' . implode(') AND (', $this->wheres) . ")\n";
    }
    if ($this->groupBys) {
      $sql .= 'GROUP BY ' . implode(', ', $this->groupBys) . "\n";
    }
    if ($this->havings) {
      $sql .= 'HAVING (' . implode(') AND (', $this->havings) . ")\n";
    }
    if ($this->orderBys) {
      $sql .= 'ORDER BY ' . implode(', ', $this->orderBys) . "\n";
    }
    return $sql;
  }
}