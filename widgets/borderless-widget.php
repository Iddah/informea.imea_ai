<?php

class BorderlessWidget extends WP_Widget {

    function BorderlessWidget() {
        $options = array(
            'classname' => 'BorderlessWidget',
            'description' => 'Plain widget with no border, no title',
        );
        $this->WP_Widget('BorderlessWidget', 'Plain, borderless widget', $options);
    }


    function form($instance) {
        $instance = wp_parse_args((array)$instance, array('content' => '', 'background-image' => '', 'width' => '', 'height' => ''));
        $height = $instance['height'];
        $width = $instance['width'];
        $background = $instance['background'];
        $content = $instance['content'];
?>
        <p>
            <label for="<?php echo $this->get_field_id('width'); ?>" style="display: inline-block; width: 80px;">Width:</label>
            <input type="text" id="<?php echo $this->get_field_id('width'); ?>" style="width: 80px;"
                   name="<?php echo $this->get_field_name('width'); ?>"
                   value="<?php echo esc_attr($width); ?>" /> px
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('height'); ?>" style="display: inline-block; width: 80px;">Height:</label>
            <input type="text" id="<?php echo $this->get_field_id('height'); ?>" style="width: 80px;"
                   name="<?php echo $this->get_field_name('height'); ?>"
                   value="<?php echo esc_attr($height); ?>" /> px
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('background'); ?>">Background image URL:</label>
            <input type="text" id="<?php echo $this->get_field_id('background'); ?>"
                   name="<?php echo $this->get_field_name('background'); ?>" style="width: 220px;"
                   value="<?php echo esc_attr($background); ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('content'); ?>">Content:</label>
            <textarea class="widefat"
                      id="<?php echo $this->get_field_id('content'); ?>" name="<?php echo $this->get_field_name('content'); ?>"
                      rows="5" cols="30"><?php echo $content; ?></textarea>
        </p>
    <?php
    }


    function update($new_instance, $old_instance) {
        $instance = $old_instance;
        $instance['content'] = $new_instance['content'];
        $instance['background'] = $new_instance['background'];
        $instance['width'] = $new_instance['width'];
        $instance['height'] = $new_instance['height'];
        return $instance;
    }


    function widget($args, $instance) {
        $content = empty($instance['content']) ? ' ' : apply_filters('widget_text', $instance['content']);
        $css_width = empty($instance['width']) ? '' : sprintf('width: %spx;', trim($instance['width']));
        $css_height = empty($instance['height']) ? '' : sprintf('height: %spx;', trim($instance['height']));
        $css_background = empty($instance['background']) ? '' : sprintf("background: url('%s') no-repeat;", $instance['background']);
        ?>
        <li class="widget borderless-widget" id="<?php echo @$args['widget_id']; ?>"
             style="<?php echo $css_background . $css_width . $css_height; ?>">
            <?php echo $content; ?>
        </li>
    <?php
    }
}

add_action('widgets_init', create_function('', 'return register_widget("BorderlessWidget");'));?>