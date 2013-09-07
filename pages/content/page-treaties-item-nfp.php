<?php
$page_data = new informea_treaties();
$treaty = informea_treaties::get_treaty_from_request();
$contact_data = informea_treaties::get_contacts($treaty->id);
$countries_contacts = $contact_data['countries'];
$all_contacts = $contact_data['contacts'];
?>
<div class="content">
<div class="toolbar toolbar-nfp">
    <button id="expand-all"><i class="icon-plus-sign"></i> Expand all</button>
    <button id="collapse-all"><i class="icon-minus-sign"></i> Collapse all</button>
    <div class="pull-right">
        Search: <input id="nfp-filter" type="text" name="search" placeholder="Find country ..." />
    </div>
    <div class="clear"></div>
</div>

<div class="alert alert-info">
    <button class="close" data-dismiss="alert">×</button>
    Click on the country to see the national focal points
</div>

<ul class="nfp">
<?php
    foreach ($countries_contacts as $country) {
        $contacts = (isset($all_contacts[$country->id_country])) ? $all_contacts[$country->id_country] : array();
        $c = count($contacts);
?>
    <li id="treaty-<?php echo $country->id_country; ?>">
        <h2>
            <i class="icon icon-plus-sign"></i>
            <img class="middle" src="<?php echo sprintf('%s/%s', get_bloginfo('template_directory'), $country->country_flag_medium); ?>" />
            <?php echo $country->country_name; ?>
        </h2>
        <div class="content-nfp hidden">
            <?php if(count($contacts)) : ?>
            <ul class="contacts">
            <?php foreach ($contacts as $contact) { ?>
                <li class="round">
                    <div class="user-icon"></div>
                    <h3>
                        <?php echo $contact->prefix; ?> <?php echo $contact->first_name; ?> <?php echo $contact->last_name; ?>
                        <i>
                            <?php if ($contact->position !== NULL) :
                                echo '<em>' . $contact->position . '</em><br />';
                            endif; ?>
                        </i>
                    </h3>
                    <div class="clear"></div>
                    <?php if (!empty($contact->email)) : ?>
                        <a class="btn blue small"
                           href="<?php informea_treaties::nfp_contact_url($contact); ?>">
                           <i class="icon icon-envelope"></i> <?php _e('e-mail', 'informea'); ?>
                        </a>
                    <?php endif; ?>
                    <a class="btn white small" href="<?php informea_treaties::nfp_vcard_url($contact); ?>"
                       <i class="icon icon-user"></i> <?php _e('vCard', 'informea'); ?>
                    </a>
                    <div class="details">
                    <?php if ($contact->department !== NULL) :
                            echo '<em>' . $contact->department . '</em><br />';
                        endif;
                        if ($contact->institution !== NULL) :
                            echo '<em>' . $contact->institution . '</em><br />';
                        endif;
                    ?>
                    <?php if ($contact->address !== NULL) : ?>
                        <div class="nfp-address">
                            <strong><?php _e('Address:', 'informea'); ?></strong> <?php echo replace_enter_br($contact->address); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($contact->telephone !== NULL) { ?>
                        <div class="nfp-phone">
                            <strong><?php _e('Phone:', 'informea'); ?></strong> <?php echo $contact->telephone; ?>
                        </div>
                    <?php } ?>

                    <?php if ($contact->fax !== NULL) { ?>
                        <div class="nfp-fax">
                            <strong><?php _e('Fax:', 'informea'); ?></strong> <?php echo $contact->fax; ?>
                        </div>
                    <?php } ?>
                    </div>
                </li>
            <?php } ?>
            </ul>
            <?php endif; ?>
        </div>
    </li>
    <?php } ?>
</ul>
</div>