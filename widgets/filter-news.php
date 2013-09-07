<?php

$featured_country = NULL;

class FilterNewsWidget extends WP_Widget {

    function FilterNewsWidget() {
        $options = array(
            'classname' => 'FilterNewsWidget',
            'description' => 'Filter MEA news using Ajax and show the results',
        );
        $this->WP_Widget('FilterNewsWidget', 'Filter news', $options);
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
        $news = new imea_news_page();

        $q = get_request_value('q');
        $topic = get_request_variable('topic');
        if(empty($topic)) {
            $topic = get_request_value('topic');
        }
        ?>
        <li class="widget filter-news">
            <?php if (!empty($title)) : ?>
                <h2>
                    <?php echo $title; ?>
                </h2>
                <div class="clear"></div>
            <?php endif; ?>
            <div class="content">
                <form action="<?php echo get_permalink(); ?>">
                    <div class="field">
                        <label for="search">Containing text</label>
                        <input id="search" type="text" name="q" value="<?php echo $q; ?>" />
                    </div>

                    <div class="field">
                        <label for="topic">Topic</label>
                        <select name="topic" id="topic">
                            <option value="">-- All topics --</option>
                        <?php
                            foreach ($news->non_empty_categories as $c):
                                $select = $c->slug == $topic ? ' selected="selected"' : '';
                        ?>
                            <option value="<?php echo $c->slug; ?>"<?php echo $select; ?>>
                                <?php echo $c->title; ?>
                            </option>
                        <?php endforeach; ?>
                        </select>
                    </div>

                    <p>
                        <a href="<?php echo get_permalink(); ?>" class="pull-left">Reset</a>
                        <input type="submit" value="Search" class="pull-right" />
                    </p>
                </form>
            </div>
        </li>
        <li>
        <?php
            $item = $news->get_category_by_slug($topic);
            if(!empty($item)) {
                echo sprintf('<div class="text-center"><p><img src="%s" class="news-category-image" /></p></div>', $item->image);
            }
        ?>
        </li>
    <?php
    }
}
add_action('widgets_init', create_function('', 'return register_widget("FilterNewsWidget");'));
