SELECT @caseCompId := id FROM `civicrm_component` where `name` like 'CiviCase';
SELECT @option_group_id_activity_type        := max(id) from civicrm_option_group where name = 'activity_type';
SELECT @max_val := MAX(ROUND(op.value)) FROM civicrm_option_value op WHERE op.option_group_id  =
   @option_group_id_activity_type;

INSERT INTO
   `civicrm_option_value` (`option_group_id`, `label`,`name`, `value`,  `grouping`, `filter`, `is_default`,
   `weight`, `description`, `is_optgroup`, `is_reserved`, `is_active`, `component_id` )
VALUES

(@option_group_id_activity_type, 'Send Termination Letter','Send Termination Letter',
  (SELECT @max_val := @max_val+1), NULL, 0,  0, (SELECT @max_val := @max_val+1), '',  0, 0, 1, @caseCompId ),
(@option_group_id_activity_type, 'Exit Interview','Exit Interview',
  (SELECT @max_val := @max_val+1), NULL, 0,  0, (SELECT @max_val := @max_val+1), '',  0, 0, 1, @caseCompId );
  
