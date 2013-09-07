<?php
class SelectTermWidget extends WP_Widget {

    function SelectTermWidget() {
        $options = array(
            'classname' => 'SelectTermWidget',
            'description' => 'Select a term with autocomplete and go to term facthseet page',
        );
        $this->WP_Widget('SelectTermWidget', 'Glossary term select', $options);
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
        add_action('js_inject', 'js_inject_widget_select_term');
        $title = empty($instance['title']) ? ' ' : apply_filters('widget_title', $instance['title']);
        ?>
        <li class="widget select-term">
            <?php if (!empty($title)) : ?>
                <h2><?php echo $title; ?></h2>
            <?php endif; ?>
            <div class="content">
                <form action="">
                    <input id="widget-select-term-autocomplete" name="id" type="text" size="28" />
                </form>
            </div>
        </li>
    <?php
    }
}

add_action('widgets_init', create_function('', 'return register_widget("SelectTermWidget");'));

function js_inject_widget_select_term() {
    ?>
    <script type="text/javascript">
        // Global tree_substantives
        $(document).ready(function () {
            $('#widget-select-term-autocomplete').autocomplete({
                source: function (request, response) {
                    $.ajax({
                        url: ajax_url + '?action=suggest_terms',
                        dataType: "json",
                        data: {
                            maxRows: 10,
                            key: request.term
                        },
                        success: function (data) {
                            response($.map(data, function (item) {
                                return {
                                    label: item.term,
                                    value: item.id
                                }
                            }));
                        }
                    });
                },
                delay: 100,
                minLength: 1,
                focus: function (event, ui) {
                    return false;
                },
                select: function (ui, data) {
                    $('#widget-select-term-autocomplete').val(data.item.label);
                    window.location = blog_dir + '/terms/' + data.item.value + '/treaties';
                    return false;
                }
            });

            $('#widget-select-term-autocomplete').keydown(function (e) {
                var search = $.trim($('#widget-select-term-autocomplete').val());
                if (e.keyCode == 13) {
                    if ('tree_substantives' in window) {
                        var selItemId = tree_substantives.getSelectedItemId();
                        var curIdx = $.inArray(selItemId, allnodes_substantives);
                        if (curIdx >= 0) {
                            var new_arr = allnodes_substantives.slice(curIdx + 1);
                            focusNextItem(search, tree_substantives, new_arr);
                        }
                    }
                    return false;
                } else {
                    if ('tree_substantives' in window) {
                        if (search.length > 1) {
                            focusNextItem(search, tree_substantives, allnodes_substantives);
                        }
                    }
                }
                return true;
            });
        });

        function focusNextItem(search, tree, items) {
            var found = false;
            var rgx = new RegExp(search, 'i');
            $.each(items, function(idx, nodeId) {
                var label = tree.getItemText(nodeId);
                if (rgx.exec(label)) {
                    tree.openItem(nodeId);
                    tree.focusItem(nodeId);
                    tree.selectItem(nodeId);
                    found = true;
                }
            });
        }
    </script>
<?php
}
?>
