<?php
class FeaturedTreatyWidget extends WP_Widget {

    function FeaturedTreatyWidget() {
        $options = array(
            'classname' => 'FeaturedTreatyWidget',
            'description' => 'Rotate one of the treaties daily',
        );
        $this->WP_Widget('FeaturedTreatyWidget', 'Featured Treaty', $options);
    }


    function form($instance) {
        $instance = wp_parse_args((array)$instance, array('title' => '', 'count_decisions' => FALSE, 'count_nfp' => FALSE));
        $title = $instance['title'];
        $count_decisions = $instance['count_decisions'];
        $count_nfp = $instance['count_nfp'];
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>">Title:</label>
            <input class="widefat"
                   id="<?php echo $this->get_field_id('title'); ?>"
                   name="<?php echo $this->get_field_name('title'); ?>" type="text"
                   value="<?php echo esc_attr($title); ?>"
                />
        </p>
        <p>
            <input type="checkbox"
                   id="<?php echo $this->get_field_id('count_decisions'); ?>"
                   name="<?php echo $this->get_field_name('count_decisions'); ?>" value="1"
                <?php echo $count_decisions == 1 ? 'checked="checked"' : ''; ?>
                />
            <label for="<?php echo $this->get_field_id('count_decisions'); ?>">Show decisions count (if
                available)</label>
        </p>
        <p>
            <input type="checkbox"
                   id="<?php echo $this->get_field_id('count_nfp'); ?>"
                   name="<?php echo $this->get_field_name('count_nfp'); ?>" value="1"
                <?php echo $count_nfp == 1 ? 'checked="checked"' : ''; ?>
                />
            <label for="<?php echo $this->get_field_id('count_nfp'); ?>">Show NFP count (if available)</label>
        </p>
    <?php
    }

    function update($new_instance, $old_instance) {
        $instance = $old_instance;
        $instance['title'] = $new_instance['title'];
        $instance['count_decisions'] = $new_instance['count_decisions'];
        $instance['count_nfp'] = $new_instance['count_nfp'];
        return $instance;
    }

    function widget($args, $instance) {
        $title = empty($instance['title']) ? ' ' : apply_filters('widget_title', $instance['title']);
        $count_decisions = $instance['count_decisions'] == 1;
        $count_nfp = $instance['count_nfp'] == 1;
        $treaty = informea_treaties::get_featured_treaty();
        if ($treaty) {
?>
            <li class="widget featured-treaty shadow">
                <?php if (!empty($title)) : ?>
                <h2><?php echo $title; ?></h2>
                <?php endif; ?>
                <div class="content">
                    <div class="thumbnail <?php echo $treaty->odata_name; ?>"></div>
                    <div class="item-title pull-left">
                        <a href="<?php echo sprintf('%s/treaties/%s', get_bloginfo('url'), $treaty->odata_name); ?>">
                            <?php echo $treaty->short_title; ?>
                        </a>
                        <?php if(!empty($treaty->theme)): ?>
                        <div class="gray">(<?php echo $treaty->theme; ?>)</div>
                        <?php endif; ?>
                    </div>
                    <p>
                        <?php
                        if ($count_decisions) :
                            $ob = new informea_treaties();
                            $c = $ob->get_decisions_count_2($treaty->id);
                            if ($c > 0) :
                                ?>
                                Decisions: <a
                                href="<?php echo sprintf('%s/treaties/%s/decisions', get_bloginfo('url'), $treaty->odata_name); ?>"><?php echo $c; ?></a>
                            <?php endif; ?>
                        <?php endif; ?>
                        <?php
                        if ($count_nfp) :
                            $c = informea_treaties::treaty_count_nfp($treaty->id);
                            if ($c > 0) :
                                ?>
                                Focal points: <a
                                href="<?php echo sprintf('%s/treaties/%s/nfp', get_bloginfo('url'), $treaty->odata_name); ?>"><?php echo $c; ?></a>
                            <?php endif; ?>
                        <?php endif; ?>
                        <?php if (!empty($treaty->region)) :
                            echo sprintf('<div>Region: %s</div>', $treaty->region);
                        endif; ?>
                    </p>
                </div>
                <div class="clear"></div>
            </li>
        <?php
        }
    }
}

add_action('widgets_init', create_function('', 'return register_widget("FeaturedTreatyWidget");'));?>