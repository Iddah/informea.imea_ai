<?php
$country_data = imea_countries_page::index_alphabetical();

function informea_countries_toolbar_alphabetical() {
    $country_data = imea_countries_page::index_alphabetical();
?>
    <div class="letters pull-left">Go to countries starting with
    <?php foreach($country_data as $letter => $countries): ?>
    <a href="#letter_<?php echo $letter; ?>"><?php echo $letter; ?></a>
    <?php endforeach; ?>
    </div>
<?php
}
add_action('informea-countries-toolbar-extra', 'informea_countries_toolbar_alphabetical');
do_action('informea-countries-toolbar');

?>
<div class="alert alert-info">
    <button class="close" data-dismiss="alert">×</button>
    Click on country name to see membership info
</div>
<ul class="countries">
<?php
    foreach ($country_data as $letter => $countries) :
?>
    <li>
        <a name="letter_<?php echo $letter; ?>"></a>
        <h2>
            <?php echo $letter; ?>
        </h2>
        <ul class="country-list">
            <?php foreach($countries as $country): ?>
            <li class="text-center">
                <a href="<?php echo sprintf('%s/countries/%s', get_bloginfo('url'), $country->code2l); ?>">
                <img src="<?php echo sprintf('%s/%s', get_bloginfo('template_directory'), $country->icon_medium); ?>" />
                <p>
                    <?php echo $country->name; ?>
                </p>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
    </li>
<?php
    endforeach;
?>
</ul>

