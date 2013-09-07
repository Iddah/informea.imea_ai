<?php
$dictionary = Thesaurus::index_alphabetical();
$letters = Thesaurus::get_alphabet_letters();
$definitions = get_request_int('definitions');

wp_enqueue_script('dhtmlxcommon', get_bloginfo('template_directory') . '/scripts/dhtmlxtree/dhtmlxcommon.js');
wp_enqueue_script('dhtmlxtree', get_bloginfo('template_directory') . '/scripts/dhtmlxtree/dhtmlxtree.js');
wp_enqueue_style('dhtmlxcommon-css', get_bloginfo('template_directory') . '/scripts/dhtmlxtree/dhtmlxtree.css');
wp_enqueue_style('dhtmlxcommon-css', get_bloginfo('template_directory') . '/scripts/dhtmlxmenu/skins/dhtmlxmenu_dhx_skyblue.css');


function informea_terms_toolbar_hierarchical() {
?>
    <button id="expand-all" onclick="tree_expandAll();"><i class="icon-plus-sign"></i> Expand all</button>
    <button id="collapse-all" onclick="tree_collapseAll();"><i class="icon-minus-sign"></i> Collapse all</button>
<?php
}
add_action('informea-terms-toolbar-extra', 'informea_terms_toolbar_hierarchical');
do_action('informea-terms-toolbar');
?>

<div id="termsTree">
    <div class="loading">
        <img src="<?php bloginfo('template_directory'); ?>/images/loading-big.gif"/><br/>Loading, please wait ...
    </div>
</div>
<div class="clear"></div>
<?php function informea_terms_jsinject_hierarchical() { ?>
<script type="text/javascript">
    var treeXMLUrl = ajax_url + '?action=generate_terms_tree_public';
    var treeImagePath = "<?php bloginfo('template_directory'); ?>/scripts/dhtmlxtree/imgs/terms_blue/";
    var allnodes_substantives = Array();
    var tree_substantives;

    $(document).ready(function () {
        treeImagePath = "<?php bloginfo('template_directory'); ?>/scripts/dhtmlxtree/imgs/terms_blue/";

        tree_substantives = new dhtmlXTreeObject('termsTree', '100%', '100%', 0);
        tree_substantives.setImagePath(treeImagePath); // Global variable
        tree_substantives.enableTreeImages(false);
        tree_substantives.enableDragAndDrop(false);
        tree_substantives.loadXML(treeXMLUrl + '&substantives=1', function () {
            $('.loading').hide();
            allnodes_substantives = tree_substantives.getAllSubItems(0).split(',');
            expandRoots();
        });

        tree_substantives.attachEvent('onClick', function (id, prevId) {
            var termId = tree_substantives.getUserData(id, 'term_id');
            window.location = blog_dir + '/terms/' + termId;
            return false;
        });
    });

    function tree_expandAll() {
        tree_substantives.openAllItems(0);
    }

    function tree_collapseAll() {
        tree_substantives.closeAllItems(0);
        expandRoots();
    }

    function expandRoots() {
        tree_substantives.openItem('generic');
        tree_substantives.openItem('substantives');
    }
</script>
<?php
}
add_action('js_inject', 'informea_terms_jsinject_hierarchical');
?>

