<?php

// A list of filters. In the sample data set, you could use any of these filters,
// and the results would include *only* contact #211 (job 1).
return array(
  array('title_op' => 'like', 'title_value' => 'Title-211-1'),
  array('position_op' => 'like', 'position_value' => 'Position-211-1'),
  array('hours_type_op' => 'in', 'hours_type_value' => 'full'),
  array('hours_type_op' => 'in', 'hours_type_value' => 'full,casual'),
  array('hours_type_op' => 'notin', 'hours_type_value' => 'part,casual'),
);
