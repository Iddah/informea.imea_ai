<?php
$search = InformeaSearch3::get_searcher();
$terms_page = new Thesaurus(NULL);
$sel_terms = $search->ui_get_selected_terms();
$tab = get_request_int('q_tab', 2);
if(!defined('INFORMEA_SEARCH_PAGE')) {
    define('INFORMEA_SEARCH_PAGE', TRUE);
}
?>
<ul class="sidebar">
<li class="widget search">
    <h2>Filter by: </h2>
    <form method="get" action="<?php bloginfo('url'); ?>/search" id="filter">
        <input type="hidden" id="q_page_size_filters" name="q_page_size" value="<?php echo $search->get_page_size(); ?>" />
        <input type="hidden" id="q_tab_filters" name="q_tab" value="<?php echo $search->get_q_tab(); ?>" />
        <input type="hidden" id="q_page_filters" name="q_page" value="<?php $search->get_page(); ?>" />
        <input type="hidden" id="q_sort_direction_filters" name="q_sort_direction" value="<?php $search->get_sort_direction(); ?>" />
    <ul>
        <li>
            <h3><i class="icon icon-minus-sign pull-left"></i> free text</h3>
            <div class="content">
                <label>Free search text
                    <input type="text" size="31" id="q_freetext" name="q_freetext" placeholder="Type a term or phrase"
                           value="<?php echo esc_attr($search->ui_get_freetext()); ?>" />
                </label>
                <button id="left_search" class="orange" onclick="$(this).closest('form').submit();">Search</button>
            </div>
        </li>

        <?php if($tab == 1) : ?>
        <li>
            <h3><i class="icon icon-minus-sign pull-left"></i> timeline</h3>
            <div class="content">
                <label>Between
                    <br />
                    <select name="q_start_year">
                        <option value="">-- Start year --</option>
                        <?php
                            foreach ($search->ui_compute_years() as $y):
                                $search->ui_write_option($y, $y, $y == $search->ui_get_start_year());
                            endforeach;
                        ?>
                    </select>
                </label>
                <br />
                <label>and
                    <br />
                    <select name="q_end_year">
                        <option value="">-- End year --</option>
                        <?php
                            foreach (array_reverse($search->ui_compute_years()) as $y):
                                $search->ui_write_option($y, $y, $y == $search->ui_get_end_year());
                            endforeach;
                        ?>
                    </select>
                </label>
            </div>
        </li>
        <?php endif; ?>

        <li>
            <h3><i class="icon icon-minus-sign pull-left"></i> keyword</h3>
            <div class="content">
                <?php render_qterm_autocomplete(); ?>
            </div>
        </li>

        <li>
            <h3><i class="icon icon-minus-sign pull-left"></i> type</h3>
            <div class="content">
                <label>
                    <input type="checkbox" name="q_use_decisions" value="1" <?php $search->ui_check_use_decisions();?> />
                    <?php _e('Decisions/Resolutions', 'informea'); ?>
                </label>
                <br/>
                <label>
                    <input type="checkbox" name="q_use_treaties" value="1" <?php $search->ui_check_use_treaties();?> />
                    <?php _e('Treaties', 'informea'); ?>
                </label>
            </div>
        </li>

        <li>
            <h3><i class="icon icon-minus-sign pull-left"></i> instrument</h3>
            <div class="content">
            <?php
                $count = 0;
                $themes = $search->ui_get_treaties();
                foreach ($themes as $theme => $treaties) {
                    $checked = $search->ui_is_checked_treaty('theme-' . $count);
            ?>
                    <ul class="instrument-list">
                        <li>
                            <input id="<?php echo slugify($theme); ?>"
                                   type="checkbox" <?php echo $checked ? 'checked="checked"' : '';?>
                                   class="explorer-treaty-click-children"/>
                            <label for="<?php echo slugify($theme); ?>"><?php echo $theme; ?></label>
                            <ul class="sublist">
                                <?php
                                foreach ($treaties as $id => $data) {
                                    $children = $data['children'];
                                    $title = $data['title'];
                                    $theme = $data['theme'];
                                    $checked = $search->ui_is_checked_treaty($id) ? 'checked="checked"' : '';
                                    ?>
                                    <li>
                                        <input type="checkbox" id="filter_treaty_<?php echo $id; ?>" name="q_treaty[]"
                                               value="<?php echo $id; ?>" <?php echo $checked; ?>
                                               class="explorer-treaty-click-children"/>
                                        <label for="filter_treaty_<?php echo $id; ?>">
                                            <?php echo $title; ?>
                                        </label>
                                        <?php if (count($children)) { ?>
                                            <ul class="sublist">
                                                <?php
                                                foreach ($children as $id => $child) {
                                                    $title = $child['title'];
                                                    $theme = $child['theme'];
                                                    $checked = $search->ui_is_checked_treaty($id) ? 'checked="checked"' : '';
                                                    ?>
                                                    <li>
                                                        <input type="checkbox" id="filter_treaty_<?php echo $id; ?>"
                                                               name="q_treaty[]" value="<?php echo $id; ?>" <?php echo $checked; ?>
                                                               class="explorer-treaty-click-children"/>
                                                        <label for="filter_treaty_<?php echo $id; ?>">
                                                            <?php echo $title; ?>
                                                        </label>
                                                    </li>
                                                <?php } ?>
                                            </ul>
                                        <?php } ?>
                                    </li>
                                <?php } ?>
                            </ul>
                        </li>
                    </ul>
                <?php }  ?>
                <ul class="spacing-zero">
                    <li class="spacing-zero">
                    <?php
                        $ot = $search->ui_get_other_treaties();
                        $checked = '';
                        foreach ($ot as $id) {
                            if ($search->ui_is_checked_treaty($id)) {
                                $checked = 'checked="checked"';
                                break;
                            }
                        }
                    ?>
                        <input id="explorer_other_treaties" type="checkbox" <?php echo $checked; ?>
                               class="explorer-treaty-click-children"/>
                        <label for="explorer_other_treaties">Other<span
                                class="description gray">(Regional, etc.)</span></label>
                        <ul class="sublist hidden">
                            <li>
                                <?php
                                foreach ($ot as $id) {
                                    $checked = $search->ui_is_checked_treaty($id) ? 'checked="checked"' : '';
                                    ?>
                                    <input type="checkbox"
                                            id="explorer_treaty_<?php echo $id; ?>" name="q_treaty[]"
                                           value="<?php echo $id; ?>" <?php echo $checked; ?> class="hidden"/>
                                <?php } ?>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </li>
        <li>
            <h3 class="text-right"><i class="icon icon-minus-sign pull-left"></i> date</h3>
            <div class="content">
            <?php include(dirname(__FILE__) . '/../../pages/explorer/inc.date_interval.php'); ?>
            </div>
        </li>
    </ul>
</li>
</ul>
</form>