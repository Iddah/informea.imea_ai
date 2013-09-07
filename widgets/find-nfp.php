<?php
class FindNFPWidget extends WP_Widget {

    function FindNFPWidget() {
        $options = array(
            'classname' => 'FindNFPWidget',
            'description' => 'Find national focal point with autocomplete',
        );
        $this->WP_Widget('FindNFPWidget', 'Find NFP', $options);
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
                    <input type="text" class="find-nfp-input" />
                </form>
            </div>
        </li>
    <?php
    }
}

add_action('widgets_init', create_function('', 'return register_widget("FindNFPWidget");'));?>