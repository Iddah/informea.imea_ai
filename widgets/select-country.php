<?php
class SelectCountryWidget extends WP_Widget {

    function SelectCountryWidget() {
        $options = array(
            'classname' => 'SelectCountryWidget',
            'description' => 'Jump to a country profile',
        );
        $this->WP_Widget('SelectCountryWidget', 'Country profile select', $options);
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
?>
        <li class="widget select-country">
            <?php if (!empty($title)) : ?>
            <h2><?php echo $title; ?></h2>
            <?php endif; ?>
            <div class="content">
                <form action="">
                    <select class="selectCountry" onchange="selectCountryWidgetOnChange(this);">
                        <option value="">-- Select country --</option>
                        <?php
                            foreach(informea_countries::get_countries() as $country) :
                                $url = sprintf('%s/countries/%s', get_bloginfo('url'), $country->code2l);
                        ?>
                            <option data-url="<?php echo $url; ?>" value="<?php echo $country->id; ?>"><?php echo $country->name; ?></option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>
        </li>
        <?php
    }
}

add_action('widgets_init', create_function('', 'return register_widget("SelectCountryWidget");'));?>