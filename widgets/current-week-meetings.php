<?php
class CurrentWeekMeetingsWidget extends WP_Widget {

    function CurrentWeekMeetingsWidget() {
        $options = array(
            'classname' => 'CurrentWeekMeetingsWidget',
            'description' => 'Show the MEA meetings from the current week',
        );
        $this->WP_Widget('CurrentWeekMeetingsWidget', 'InforMEA meetings', $options);
    }


    function form($instance) {
        $instance = wp_parse_args((array)$instance, array('title' => '', 'count' => 3));
        $title = $instance['title'];
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>">Title:</label>
            <input class="widefat"
                   id="<?php echo $this->get_field_id('title'); ?>"
                   name="<?php echo $this->get_field_name('title'); ?>" type="text"
                   value="<?php echo esc_attr($title); ?>"
                />
        </p>
    <?php
    }

    function update($new_instance, $old_instance) {
        $instance = $old_instance;
        $instance['title'] = $new_instance['title'];
        return $instance;
    }


    function widget($args, $instance) {
        $title = empty($instance['title']) ? ' ' : apply_filters('widget_title', $instance['title']);
        $meetings = informea_meetings::get_meetings_current_week();
        if(count($meetings)) :
        ?>
        <li class="widget upcoming-meetings">
            <?php if (!empty($title)) : ?>
            <h2><?php echo $title; ?></h2>
            <?php endif; ?>
            <div class="content">
                <ul class="items">
                    <?php
                    foreach ($meetings as $idx => $row) :
                        $cssClass = $idx > 0 ? ' class="hidden"' : '';
                    ?>
                        <li<?php echo $cssClass; ?>>
                            <div class="thumbnail <?php echo $row->odata_name; ?>"></div>
                            <div class="item-title pull-left">
                                <?php echo $row->title; ?>
                                <br />
                                <strong><?php echo show_event_interval($row); ?></strong>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <div class="clear"></div>
                <?php if (count($meetings) > 1) : ?>
                    <div class="paginator">
                        <a class="prev" href="javascript:void(0);">&laquo;</a>
                        <span class="current">1</span> of <?php echo count($meetings); ?>
                        <a class="next" href="javascript:void(0);">&raquo;</a>
                    </div>
                <?php endif; ?>
            </div>
        </li>
    <?php
        endif;
    }
}

add_action('widgets_init', create_function('', 'return register_widget("CurrentWeekMeetingsWidget");'));?>