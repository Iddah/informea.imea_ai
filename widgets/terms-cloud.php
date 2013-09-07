<?php
/**
 * Author: Cristian Romanescu <cristi _at_ eaudeweb dot ro>
 * Created: 201304161328
 */

class TermCloudWidget extends WP_Widget {

    function TermCloudWidget() {
        $options = array(
            'classname' => 'TermCloudWidget',
            'description' => 'Show relevant InforMEA terms in context of the displayed data',
        );
        $this->WP_Widget('TermCloudWidget', 'InforMEA terms cloud', $options);
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
        global $cloud_terms;
        $title = empty($instance['title']) ? '' : apply_filters('widget_title', $instance['title']);
        if(!empty($cloud_terms)) {
            $terms = $cloud_terms;
        } else {
            $terms = Thesaurus::get_top_concepts();
        }
        $terms = imea_page_base_page::compute_popularity($terms);
?>
        <li class="widget tag-cloud">
            <?php if (!empty($title)) : ?>
                <h2><?php echo $title; ?></h2>
            <?php endif; ?>
            <div class="tags">
                <ul>
                    <?php foreach($terms as $_term) : ?>
                    <li>
                        <a class="tag<?php echo $_term->popularity; ?>"
                           href="<?php bloginfo('url'); ?>/terms/<?php echo $_term->id; ?>"><?php echo $_term->tag; ?></a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="clear"></div>
        </li>
<?php
    }
}

add_action('widgets_init', create_function('', 'return register_widget("TermCloudWidget");'));