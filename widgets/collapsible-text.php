<?php
class CollapsibleTextWidget extends WP_Widget {

    function CollapsibleTextWidget() {
        $options = array(
            'classname' => 'CollapsibleTextWidget',
            'description' => 'Display text default collapsed with expand ribbon',
        );
        $this->WP_Widget('CollapsibleTextWidget', 'Collapsible text', $options);
    }


    function form($instance) {
        $instance = wp_parse_args((array)$instance, array('title' => '', 'content' => '', 'expanded' => 0));
        $title = $instance['title'];
        $content = $instance['content'];
        $expanded = $instance['expanded'] == 1;
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>">Title:</label>
            <input type="text" class="widefat"
                   id="<?php echo $this->get_field_id('title'); ?>"
                   name="<?php echo $this->get_field_name('title'); ?>"
                   value="<?php echo esc_attr($title); ?>"
                />
        </p>
        <p>
            <input type="checkbox"
                   id="<?php echo $this->get_field_id('expanded'); ?>"
                   name="<?php echo $this->get_field_name('expanded'); ?>" value="1"
                   <?php echo $expanded == 1 ? 'checked="checked"' : ''; ?>
                />
            <label for="<?php echo $this->get_field_id('expanded'); ?>">Expanded</label>
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
        $instance['title'] = $new_instance['title'];
        $instance['content'] = $new_instance['content'];
        $instance['expanded'] = $new_instance['expanded'];
        return $instance;
    }


    function widget($args, $instance) {
        $title = empty($instance['title']) ? ' ' : apply_filters('widget_title', $instance['title']);
        $content = empty($instance['content']) ? ' ' : apply_filters('widget_text', $instance['content']);
        $expanded = @$instance['expanded'] == 1;
?>
        <li class="widget round whatisinformea" id="<?php echo @$args['widget_id']; ?>">
            <h2><?php echo $title; ?></h2>
            <div class="content<?php echo $expanded ? '' : ' hidden'; ?>">
                <?php echo $content; ?>
            </div>
            <a href="javascript:void(0);" class="ribbon-click">
                <img class="ribbon" src="<?php bloginfo('template_directory'); ?>/images/expand.png" alt="arrow for expanding this widget">
            </a>
        </li>
        <?php
    }
}

add_action('widgets_init', create_function('', 'return register_widget("CollapsibleTextWidget");'));?>