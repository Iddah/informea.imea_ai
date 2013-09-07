<?php
// Template engine functions
function tengine_head() {
    $imea_options = get_option('informea_options');
    ?>
    <?php
    if (@$imea_options['css_optimizer']) {
        ?>
        <link rel="stylesheet" type="text/css" media="screen"
              href="<?php bloginfo('template_directory'); ?>/style_min.css"/>
    <?php
    } else {
        ?>
        <link rel="stylesheet" type="text/css" media="screen" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.10.2/themes/sunny/jquery-ui.css" />
        <link rel="stylesheet" type="text/css" media="screen" href="<?php bloginfo('template_directory'); ?>/style.css" />
        <link rel="stylesheet" type="text/css" media="print" href="<?php bloginfo('template_directory'); ?>/styles/print.css" />
<?php
    }
?>
    <!--[if IE 7]>
    <link href="<?php bloginfo('template_directory'); ?>/styles/fix-IE7.css" rel="stylesheet" type="text/css" media="screen" />
    <![endif]-->
    <!--[if gte IE 8]>
    <link href="<?php bloginfo('template_directory'); ?>/styles/fix-IE8.css" rel="stylesheet" type="text/css" media="screen" />
    <![endif]-->
    <?php
    // Execute css_inject to give pages the chance to add CSS code in html header of the page
    do_action('css_inject');
    ?>
    <!-- link rel="stylesheet" type="text/css" media="print" href="<?php bloginfo('template_directory'); ?>/styles/print.css" / -->
<?php
}
