<div class="clear"></div>

Interval between years
<br />
<select name="q_start_year">
    <option value="">Start</option>
    <?php
    foreach ($search->ui_compute_years() as $y) {
        $search->ui_write_option($y, $y, $y == $search->ui_get_start_year());
    }
    ?>
</select>
and
<select name="q_end_year">
    <option value="">End</option>
    <?php
    foreach (array_reverse($search->ui_compute_years()) as $y) {
        $search->ui_write_option($y, $y, $y == $search->ui_get_end_year());
    }
    ?>
</select>
<div class="clear"></div>
