<?php
$dictionary = Thesaurus::index_alphabetical();
$letters = Thesaurus::get_alphabet_letters();
$hide_definitions = get_request_int('hide_definitions') == 1;

function informea_terms_toolbar_list() {
    $hide_definitions = get_request_int('hide_definitions') == 1 ;
    $checked = $hide_definitions ? ' checked="checked"' : '';
    $letters = Thesaurus::get_alphabet_letters();
?>
    <form action="<?php echo get_permalink(); ?>/list/" id="hide-definitions-toolbar-form">
        <label>
            <input type="checkbox" name="hide_definitions" value="1" <?php echo $checked; ?>
                   onclick="$('#hide-definitions-toolbar-form').submit();" />
            Hide terms definitions
        </label>
    </form>
    <div class="separator-h-20">
        Navigate to letter:
    <?php foreach ($letters as $letter): ?>
        <a href="#letter_<?php echo $letter->letter; ?>"><?php echo $letter->letter; ?></a>
    <?php endforeach; ?>
    </div>
<?php
}
add_action('informea-terms-toolbar-extra', 'informea_terms_toolbar_list');
do_action('informea-terms-toolbar');
?>

<?php if(!$hide_definitions) : ?>
<ul class="terms">
<?php foreach ($letters as $letter): ?>
    <li>
        <a name="letter_<?php echo $letter->letter; ?>"></a>
        <h2><?php echo $letter->letter; ?></h2>
        <table class="content table-hover">
            <?php $terms = $dictionary[$letter->letter]; ?>
            <?php foreach ($terms as $term): ?>
            <tr>
                <td class="text-top" style="width: 200px;">
                    <a href="<?php echo get_permalink() . $term->id; ?>"><?php echo $term->term;?></a>
                </td>
                <td class="text-top">
                    <?php echo $term->description; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>
    </li>
<?php endforeach; ?>
</ul>

<?php else: ?>
<ul class="terms inline">
    <?php foreach ($letters as $letter): ?>
        <li>
            <a name="letter_<?php echo $letter->letter; ?>"></a>
            <h2><?php echo $letter->letter; ?></h2>
            <?php $terms = $dictionary[$letter->letter]; ?>
            <?php foreach ($terms as $term): ?>
            <a href="<?php echo get_permalink() . $term->id; ?>"><?php echo $term->term;?></a>
            <?php endforeach; ?>
        </li>
    <?php endforeach; ?>
</ul>
<?php endif; ?>
