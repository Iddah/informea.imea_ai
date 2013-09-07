<?php
$search = new InformeaSearch3($_GET);
$sel_terms = $search->ui_get_selected_terms();
$terms_page = new Thesaurus(NULL);
?>
<script type="text/javascript">
    // This fragment stays here
    var freetext_default = '<?php echo $search->ui_get_freetext_default();?>';
</script>
<div id="search" class="content hidden">
    <ul class="border">
        <li class="section">
            <span class="title">
                <a href="javascript:void(0);" class="minus"></a>
                <?php _e('Quick search all', 'informea'); ?>
            </span>
            <form method="get" action="<?php bloginfo('url'); ?>/search">
                <input type="hidden" name="q_page_size" value="10" />
                <input type="hidden" name="q_page" value="0" />
                <input type="hidden" name="q_use_meetings" value="1" />
                <input type="hidden" name="q_use_treaties" value="1" />
                <input type="hidden" name="q_use_decisions" value="1" />
                <input type="hidden" name="q_tab" value="2"/>
                <?php
                $ts = $search->ui_get_treaties_ids();
                foreach ($ts as $t_id) {
                ?>
                    <input type="hidden" name="q_treaty[]" value="<?php echo $t_id; ?>" />
                <?php
                }
                ?>
                <input type="text" id="explorer_q_freetext" name="q_freetext" class="text left"
                       value="<?php echo $search->get_freetext($search->ui_get_freetext_default()); ?>"/>
                <button class="btn orange" onclick="$(this).closest('form').submit();">Search</button>
            </form>
        </li>
        <li class="section">
            <span class="title">
                <a href="javascript:void(0);" class="plus"></a>
                <?php _e('Treaties and Decisions/Resolutions', 'informea'); ?>
            </span>
            <form method="get" action="<?php bloginfo('url'); ?>/search" class="hidden">
                <input type="hidden" name="q_tab" value="2"/>
                <?php include(dirname(__FILE__) . '/inc.treaties.php'); ?>
                <div class="clear"></div>
                <div class="separator"></div>

                <input id="explorer_q_use_decisions" type="checkbox" name="q_use_decisions" value="1" checked="checked" />
                <label for="explorer_q_use_decisions"><?php _e('Decisions/Resolutions', 'informea'); ?></label>

                <input id="explorer_q_use_treaties" type="checkbox" name="q_use_treaties" value="1" checked="checked" />
                <label for="explorer_q_use_treaties"><?php _e('Treaties', 'informea'); ?></label>

                <div class="block">
                    <label for="free-text">Free search text</label>
                    <br />
                    <input type="text" id="free-text"
                           value="<?php echo $search->get_freetext($search->ui_get_freetext_default()); ?>"
                           name="q_freetext" class="text">
                </div>
                <div class="tagged-terms">
                    <?php render_qterm_autocomplete(); ?>
                </div>
                <?php
                include(dirname(__FILE__) . '/inc.date_interval.php');
                ?>
                <div class="block">
                    <button class="btn orange" onclick="$(this).closest('form').submit();">Search</button>
                </div>
            </form>
        </li>
    </ul>
</div>
