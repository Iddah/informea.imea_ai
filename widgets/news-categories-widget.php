<?php

class NewsCategoriesWidget extends WP_Widget {

    function NewsCategoriesWidget() {
        $options = array(
            'classname' => 'NewsCategoriesWidget',
            'description' => 'Show images with news categories',
        );
        $this->WP_Widget('NewsCategoriesWidget', 'News categories', $options);
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
        $title = trim($title);
?>
            <li class="widget news-categories noshadow">
                <?php if (!empty($title)) : ?>
                    <h2><?php echo $title; ?></h2>
                <?php endif; ?>
                <div class="content">
                    <ul>
                        <?php
                            $hob = new imea_news_page();
                            $categories = $hob->categories;
                            foreach($categories as $row):
                        ?>
                            <li>
                                <a href="<?php echo $row->link; ?>"
                                    style="background-image: url(<?php echo $row->image; ?>)">
                                    <span>
                                        <?php echo $row->title; ?> News
                                    </span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </li>
        <?php
    }
}

add_action('widgets_init', create_function('', 'return register_widget("NewsCategoriesWidget");'));