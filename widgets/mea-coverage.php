<?php
class MEACoverageWidget extends WP_Widget {

    function MEACoverageWidget() {
        $options = array(
            'classname' => 'MEACoverageWidget',
            'description' => 'Select an MEA to see its geographical coverage',
        );
        $this->WP_Widget('MEACoverageWidget', 'MEA Coverage', $options);
    }


    function form($instance) {
        $instance = wp_parse_args((array)$instance, array('title' => ''));
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
        $mea_id = get_request_int('coverage');
        $treaties = informea_countries::get_treaties_with_membership();
?>
        <li class="widget select-country">
            <?php if (!empty($title)) : ?>
                <h2><?php echo $title; ?></h2>
            <?php endif; ?>
            <div class="content">
                <form action="<?php bloginfo('url'); ?>/countries" method="get" id="country_map_mea_membership">
                    <select name="coverage" class="mea-coverage" onchange="$('#country_map_mea_membership').submit();">
                        <option value="">-- Please select --</option>
<?php
                        foreach ($treaties as $treaty) :
                            $selected = $treaty->id == $mea_id ? ' selected="selected"' : '';
                            echo sprintf('<option value="%s"%s>%s</option>', $treaty->id, $selected, $treaty->short_title);
                        endforeach;
?>
                    </select>
                </form>
            </div>
        </li>
<?php
    }
}

add_action('widgets_init', create_function('', 'return register_widget("MEACoverageWidget");'));?>