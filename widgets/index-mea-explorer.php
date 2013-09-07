<?php
class MEAExplorerWidget extends WP_Widget {

    function MEAExplorerWidget() {
        $options = array(
            'classname' => 'MEAExplorerWidget',
            'description' => 'MEA Explorer index page widget',
        );
        $this->WP_Widget('MEAExplorerWidget', 'MEA Explorer', $options);
    }


    function form($instance) {
        $instance = wp_parse_args((array)$instance, array());
    }

    function update($new_instance, $old_instance) {
        $instance = $old_instance;
        return $instance;
    }

    function widget($args, $instance) {
        $search = new InformeaSearch3($_GET);
        ?>
        <li class="widget index-explorer round noshadow">
            <form action="/search" method="get" id="search_index">
                <input type="hidden" name="q_tab" value="2" />
                <h2></h2>
                <div class="content">
                    <label for="q_freetext">
                        <input type="text" id="q_freetext" name="q_freetext" class="freetext" size="40"
                               placeholder="Search agreement texts and decisions" />
                    </label>

                    <ul class="advanced hidden">
                        <li class="section">
                            <p>Topics</p>
                            <div class="filters">
                                <ul>
                                    <li>
                                        <input id="index_treaty_biodiversity" type="checkbox" checked="checked" />
                                        <label for="index_treaty_biodiversity">Biodiversity</label>
                                    </li>
                                    <li>
                                        <input id="index_treaty_chemicals" type="checkbox" checked="checked" />
                                        <label for="index_treaty_chemicals">Chemicals / Waste</label>
                                    </li>
                                    <li>
                                        <input id="index_treaty_climate" type="checkbox" checked="checked" />
                                        <label for="index_treaty_climate">Climate / Ozone / Deserts</label>
                                    </li>
                                    <li>
                                        <input id="index_treaty_other" type="checkbox" checked="checked" />
                                        <label for="index_treaty_other">Other (Regional, etc)</label>
                                    </li>
                                </ul>
                                <?php
                                $ts = $search->ui_get_treaties_ids();
                                foreach ($ts as $t_id) {
                                    ?>
                                    <input type="checkbox" id="q_treaty_index_<?php echo $t_id; ?>" name="q_treaty[]"
                                           value="<?php echo $t_id; ?>" checked="checked" class="hidden" />
                                <?php
                                }
                                ?>
                            </div>
                        </li>
                        <li class="section">
                            <p>Search in</p>
                            <div class="filters">
                                <ul>
                                    <li>
                                        <input type="checkbox" id="index_q_use_decisions" name="q_use_decisions" checked="checked" value="1"/>
                                        <label for="index_q_use_decisions">Decisions &amp; Resolutions</label>
                                    </li>
                                    <li>
                                        <input type="checkbox" id="index_q_use_treaties" name="q_use_treaties" checked="checked" value="1"/>
                                        <label for="index_q_use_treaties">Treaties</label>
                                    </li>
                                    <li>
                                        <input type="checkbox" id="index_q_use_meetings" name="q_use_meetings" value="1" class="checkbox" checked="checked"/>
                                        <label for="index_q_use_meetings" class="check-label label click-filters-use-meetings">Meetings</label>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        <li class="section">
                            <?php render_qterm_autocomplete(); ?>
                        </li>
                        <li class="section">
                            <p>Time filter</p>
                            <div class="filters">
                                <?php include(dirname(__FILE__) . '/../pages/explorer/inc.date_interval.php'); ?>
                            </div>
                        </li>
                    </ul>
                    <div class="up">
                        <a href="javascript:void(0);" class="index-explorer-advanced-search-click">
                            <span>Advanced search &raquo;</span>
                        </a>
                        <input id="btn_search_top" type="submit" class="btn pull-right orange" value="Search" />
                    </div>
                </div>
            </form>
        </li>
    <?php
    }
}

add_action('widgets_init', create_function('', 'return register_widget("MEAExplorerWidget");'));?>