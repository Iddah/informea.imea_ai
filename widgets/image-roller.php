<?php
/**
 * Author: Cristian Romanescu <cristi _at_ eaudeweb dot ro>
 * Created: 201304172208
 */

class ImageRollerWidget extends WP_Widget {

    function ImageRollerWidget() {
        $options = array(
            'classname' => 'ImageRollerWidget',
            'description' => 'Roll multiple images of same size'
        );
        $this->WP_Widget('ImageRollerWidget', 'Image roller', $options);
    }


    function form($instance) {
        $instance = wp_parse_args((array)$instance, array(
            'title' => '', 'images' => '',
            'duration' => '3000',
            'width' => '', 'height' => '')
        );
        $title = $instance['title'];
        $images = $instance['images'];
        $duration = $instance['duration'];
        $width = $instance['width'];
        $height = $instance['height'];
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>">Title:</label>
            <input type="text" id="<?php echo $this->get_field_id('title'); ?>"
                   name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo esc_attr($title); ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('images'); ?>">Image URLs (one, each line)</label>
            <textarea class="widefat"
                      id="<?php echo $this->get_field_id('images'); ?>"
                      name="<?php echo $this->get_field_name('images'); ?>"
                      rows="5" cols="30"><?php echo $images; ?></textarea>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('duration'); ?>">Duration:</label>
            <input type="text" id="<?php echo $this->get_field_id('duration'); ?>" style="width: 60px;"
                   name="<?php echo $this->get_field_name('duration'); ?>" value="<?php echo esc_attr($duration); ?>" /> sec
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('width'); ?>">Image width:</label>
            <input type="text" id="<?php echo $this->get_field_id('width'); ?>" style="width: 80px;"
                   name="<?php echo $this->get_field_name('width'); ?>" value="<?php echo esc_attr($width); ?>" /> px
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('height'); ?>">Image height:</label>
            <input type="text" id="<?php echo $this->get_field_id('height'); ?>" style="width: 80px;"
                   name="<?php echo $this->get_field_name('height'); ?>" value="<?php echo esc_attr($height); ?>" /> px
        </p>
    <?php
    }


    function update($new_instance, $old_instance) {
        $instance = $old_instance;
        $instance['title'] = $new_instance['title'];
        $instance['images'] = $new_instance['images'];
        $instance['duration'] = $new_instance['duration'];
        $instance['width'] = $new_instance['width'];
        $instance['height'] = $new_instance['height'];
        return $instance;
    }


    function widget($args, $instance) {
        $title = empty($instance['title']) ? '' : apply_filters('widget_text', $instance['title']);
        $images = empty($instance['images']) ? array() : explode("\n", $instance['images']);
        $duration = intval($instance['duration']);
        $width = intval($instance['width']);
        $height = intval($instance['height']);
        if(count($images)) :
            wp_enqueue_script('jquery-cycle-lite', get_bloginfo('template_directory') . '/scripts/jquery.cycle.lite.js', array(), FALSE, TRUE);
            add_action('js_inject', 'widget_image_roller_inject_js');
    ?>
            <li class="widget image-roller">
            <?php if (!empty($title)) : ?>
                <h2><?php echo $title; ?></h2>
            <?php endif; ?>
                <div class="content">
                <?php foreach($images as $image) :
                    if(empty($image)) { continue; }
                ?>
                        <img src="<?php echo $image; ?>" data-duration="<?php echo $duration; ?>"
                             width="<?php echo $width; ?>" height="<?php echo $height; ?>" />
                <?php endforeach; ?>
                </div>
            </li>
    <?php
        endif;
    }
}

add_action('widgets_init', create_function('', 'return register_widget("ImageRollerWidget");'));

function widget_image_roller_inject_js() {
?>
    <script type="text/javascript">
        $(document).ready(function() {
            var ctrl = $('li.widget.image-roller>div.content');
            var img = $($('img', ctrl)[0]);
            var width = img.attr('width');
            var height = img.attr('height');
            ctrl.cycle({
                fx: 'fade',
                speed: 'fast',
                width: width,
                height: height,
                timeoutFn: function (current, next, options, forwardFlag) {
                    return parseInt($(current).attr('data-duration'));
                }
            });
        });
    </script>
<?php
}