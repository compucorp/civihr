<?php

// A list of filters. In the sample data set, you could use any of these filters,
// and the results would include *only* contact #213 (job 6).
return array(
  array('title_op' => 'like', 'title_value' => 'Title-213-6'),
  array('position_op' => 'like', 'position_value' => 'Position-213-6'),
  array('hours_type_op' => 'in', 'hours_type_value' => 'part'),
  array('hours_type_op' => 'notin', 'hours_type_value' => 'full,casual'),
);
