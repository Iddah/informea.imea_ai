<?php
class MeetingsExplorerWidget extends WP_Widget {

    function MeetingsExplorerWidget() {
        $options = array(
            'classname' => 'MeetingsExplorerWidget',
            'description' => 'Widget to search & filter MEA meetings',
        );
        $this->WP_Widget('MeetingsExplorerWidget', 'InforMEA meetings explorer', $options);
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
        $meetings_ob = new informea_meetings();
        $title = empty($instance['title']) ? ' ' : apply_filters('widget_title', $instance['title']);
?>
        <li class="widget event-explorer">
            <h2><?php echo $title; ?></h2>
            <div class="content">
                <form action="/meetings/" method="get">
                    <input type="hidden" name="fe_show_past" value="1" />
                    <input type="hidden" name="do_search" value="1" />
                    <div class="field">
                        <label for="fe_treaty">MEA</label>
                        <select id="fe_treaty" name="fe_treaty">
                            <option value="">-- All --</option>
                            <?php foreach ($meetings_ob->get_treaties() as $treaty) : ?>
                                <option value="<?php echo $treaty->id; ?>"><?php echo $treaty->short_title; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="field">
                        <label for="fe_type">Meeting type</label>
                        <select id="fe_type" name="fe_type">
                            <?php foreach(informea_meetings::get_event_types() as $value => $label) :
                                ?>
                                <option value="<?php echo $value; ?>"><?php echo $label; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="field">
                        <label for="fe_year">Date</label>
                        <select name="fe_year">
                            <option value="-1">-- All years --</option>
                            <?php
                            foreach($meetings_ob->get_years_interval() as $year) :
                                $selected = strftime('%Y') == $year ? ' selected="selected"' : '';
                            ?>
                            <option value="<?php echo $year; ?>"<?php echo $selected; ?>>
                                <?php echo $year; ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <p>
                        <input id="event-explorer-submit" type="submit" name="search" value="Search" class="btn pull-right" />
                    </p>
                </form>
            </div>
        </li>
    <?php
    }
}

add_action('widgets_init', create_function('', 'return register_widget("MeetingsExplorerWidget");'));?>