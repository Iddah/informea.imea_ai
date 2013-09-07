<?php
$page_data = new informea_treaties();
$treaty = informea_treaties::get_treaty_from_request();
?>
    <img src="<?php echo $treaty->logo_medium; ?>" alt="<?php _e('Convention logo', 'informea'); ?>" />
    <h1><?php echo $treaty->short_title; ?></h1>
    <p><?php echo $treaty->long_title; ?></p>