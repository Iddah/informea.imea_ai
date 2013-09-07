<?php
/**
 * The template for displaying the footer.
 *
 * Contains the closing of the id=main div and all content
 * after.  Calls sidebar-footer.php for bottom widgets.
 *
 * @package WordPress
 * @subpackage Twenty_Ten
 * @since Twenty Ten 1.0
 */
global $page_data;
$show_changelog = get_theme_mod('show_changelog_in_index');
$imea_options = get_option('informea_options');
$changelog_entry = imea_index_page::get_latest_changelog_entry();
?>
        </div><!-- #content -->
    </div><!-- #container -->
    <div class="clear"></div>
    <?php if($show_changelog && $changelog_entry) : ?>
    <div id="changelog" class="round">
        <p>
            <?php echo mysql2date('d F, Y', $changelog_entry->post_date); ?> -
            <?php echo $changelog_entry->post_title; ?>
            <a href="<?php bloginfo('uri'); ?>/changelog">Read more</a>
        </p>
    </div>
    <?php endif; ?>
</div><!-- #main -->
<?php
tengine_footer();
/* Always have wp_footer() just before the closing </body>
 * tag of your theme, or you will break many plugins, which
 * generally use this hook to reference JavaScript files.
 */

wp_footer();
if (!empty($imea_options['js_optimizer']) && $imea_options['js_optimizer'] == true) {

} else {

}
// Execute js_inject to give pages the chance to add JS code at the end of the page
// such as in-place code, including custom scripts etc.
do_action('js_inject');
?>
<script type="text/javascript">
    var _gaq = _gaq || [];
    _gaq.push(['_setAccount', '<?php echo $imea_options["google_key"]; ?>']);
    _gaq.push(['_trackPageview']);

    (function () {
        var ga = document.createElement('script');
        ga.type = 'text/javascript';
        ga.async = true;
        ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
        var s = document.getElementsByTagName('script')[0];
        s.parentNode.insertBefore(ga, s);
    })();
</script>

<script type="text/javascript" src="http://informea.org/clickheat/js/clickheat.js"></script>
<noscript><p><a href="http://www.dugwood.com/clickheat/index.html">ClickHeat</a></p></noscript>
<script type="text/javascript">
    <!--
    clickHeatSite = 'informea.org';
    clickHeatGroup = encodeURIComponent(window.location.pathname + window.location.search);
    clickHeatServer = 'http://informea.org/clickheat/click.php';
    initClickHeat();
    //-->
</script>
<div id="dialog_survey" class="dialog"></div>
</body>
</html>
