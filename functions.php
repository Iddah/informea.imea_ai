<?php
function build_breadcrumbtrail($items) {
    $ret =  '<div id="breadcrumbtrail">';
    $ret .= '<ul>';
    $ret .= sprintf('<li>%s</li>', __('You are here:', 'informea'));
    $c = count($items);
    $i = 0;
    foreach($items as $label => $url) {
        $raquo = $i < $c - 1 ? '<span class="separator">&raquo;</span>' : '';
        if(!empty($url)) {
            $ret .= sprintf('<li><a href="%s">%s</a>%s</li>', $url, $label, $raquo);
        } else {
            $ret .= sprintf('<li>%s%s</li>', $label, $raquo);
        }
        $i++;
    }
    $ret .= '</ul>';
    $ret .= '</div>';
    return $ret;
}

function informea_breadcrumbtrail() {
    global $post;
    $ret = '';
    if ($post !== NULL && $post->post_name != 'index') {
        return '<div id="breadcrumb" class="clear">
            <span class="breadcrumb-name">' . __('You are here:', 'informea') . '</span>
            <a href="' . get_bloginfo('url') . '" title="">' . __('Home', 'informea') . '</a>' . apply_filters('breadcrumbtrail', '') .
            '</div>';
    }
    return $ret;
}


function informea_is_staging() {
    return get_bloginfo('url') == 'http://test.informea.org';
}


function informea2_primary_nav_menu_args($args) {
    $args['show_home'] = FALSE;
    return $args;
}
add_filter('wp_nav_menu_args', 'informea2_primary_nav_menu_args');


function informea2_setup() {
    register_nav_menus(array(
        'primary' => __('Primary Navigation', 'informea'),
        'secondary' => __('Secondary Navigation', 'informea'),
        'footer' => __('Footer menu', 'informea'),
    ));
}
add_action('after_setup_theme', 'informea2_setup');


/* Theme customization */
require_once( ABSPATH . WPINC . '/class-wp-customize-setting.php' );
require_once( ABSPATH . WPINC . '/class-wp-customize-section.php' );
require_once( ABSPATH . WPINC . '/class-wp-customize-control.php' );


class Checkbox_Customize_Control extends WP_Customize_Control {
    public $type = 'checkbox';

    public function render_content() {
        $checked = $this->value() == 1 ? ' checked="checked"' : ' ';
?>
        <label>
            <input type="checkbox" <?php $this->link(); ?><?php echo $checked; ?>/>
            <?php echo esc_html( $this->label ); ?>
        </label>
<?php
    }
}


/* Breadcrumbtrails */
function informea_treaties_breadcrumbtrail() {
    global $post;
    $items = array();
    $id = get_request_variable('id');
    $home = get_bloginfo('url');
    $items[__('Home', 'informea')] = $home;
    if($id) {
        $expand = get_request_variable('expand', 'str', 'treaty');
        $treaty = informea_treaties::get_treaty_by_odata_name($id);
        $items[__('Treaties', 'informea')] = sprintf('%s/%s', $home, $post->post_name);
        if(in_array($expand, array('', 'treaty'))) {
            $items[$treaty->short_title] = '';
        } else {
            $items[$treaty->short_title] = sprintf('%s/%s/%s', $home, $post->post_name, $id);
            $label = ucfirst($expand);
            switch(strtolower($expand)) {
                case 'nfp';
                    $label = 'National Focal Points';
                    break;
                case 'coverage';
                    $label = 'Map and Membership';
                    break;
            }
            $items[$label] = '';
        }

    } else {
        $items[__('Treaties', 'informea')] = '';
    }
    echo build_breadcrumbtrail($items);
}


function informea_decisions_breadcrumbtrail() {
    $items = array();
    $home = get_bloginfo('url');
    $items[__('Home', 'informea')] = $home;
    $items[__('Decisions', 'informea')] = '';
    echo build_breadcrumbtrail($items);
}


function informea_countries_breadcrumbtrail() {
    global $post;
    $items = array();
    $id = informea_countries::get_id_from_request();
    $home = get_bloginfo('url');
    $items[__('Home', 'informea')] = $home;
    if($id) {
        $items[__('Countries', 'informea')] = sprintf('%s/%s', $home, $post->post_name);
        $country = informea_countries::get_country_for_id($id);
        $items[$country->name] = '';
    } else {
        $items[__('Countries', 'informea')] = '';
    }
    echo build_breadcrumbtrail($items);
}


function informea_customize_register( $wp_customize ) {
    //All our sections, settings, and controls will be added here
    $wp_customize->add_setting('show_changelog_in_index',
        array('default' => 1)
    );
    $wp_customize->add_section('informea_index_section' , array('title'      => __('Index page','informea'), 'priority'   => 30));
    $wp_customize->add_control(
        new Checkbox_Customize_Control($wp_customize, 'show_changelog_in_index', array(
               'label' => __('Show changelog above footer', 'informea'),
                'section' => 'informea_index_section', 'settings' => 'show_changelog_in_index',
            )
        )
    );
}
add_action('customize_register', 'informea_customize_register');


function render_qterm_autocomplete($show_label_keywords=TRUE) {
    $search = new InformeaSearch3($_GET);
    $terms_page = new Thesaurus(NULL);
    $terms = $terms_page->suggest_vocabulary_terms();
    $selected = $search->get_terms();
    $andor_hidden = count($selected) > 1 ? '' : 'hidden';
    $or_checked = $search->is_terms_or() ? 'checked="checked"' : '';
    $and_checked = $search->is_terms_or() ? '' : 'checked="checked"';
?>
    <?php if($show_label_keywords): ?>
    <label id="q_term_label" for="q_term">Type keywords</label>
    <?php endif; ?>
    <div class="q_term_container">
        <select id="q_term" name="q_term[]" multiple="multiple" class="hidden">
        <?php
            foreach ($terms as $term) {
                $search->ui_write_option($term->id, $term->term, in_array(intval($term->id), $selected));
            }
        ?>
        </select>
        <div id="q_term_andor" class="<?php echo $andor_hidden; ?>">
            <label>
                <input type="radio" name="q_term_or" value="and" <?php echo $and_checked; ?>/>
                AND
            </label>
            <label>
                <input type="radio" name="q_term_or" value="or" <?php echo $or_checked; ?>/>
                OR
            </label>
        </div>
        <ul id="q_term_holder">
        <?php
            foreach($selected as $id_term):
                $term = $terms_page->get_term($id_term);
        ?>
            <li class="round">
                <?php echo $term->term; ?>
                <a href="javascript:void(0);" onclick="qTermRemove(<?php echo $id_term; ?>, this);"><i class="icon icon-remove"></i></a>
            </li>
        <?php endforeach; ?>
        </ul>
    </div>
<?php
}

function informea2_widgets_init() {
    register_sidebar(array(
        'id' => 'index-page-left', 'name' => __('Index page left column', 'informea'),
        'description' => __('Index page first column widgets', 'informea'),
    ));
    register_sidebar(array(
        'name' => __('Index page center column', 'informea'), 'id' => 'index-page-center',
        'description' => __('Index page center column widgets', 'informea'),
    ));
    register_sidebar(array(
        'id' => 'index-page-right', 'name' => __('Index page right column', 'informea'),
        'description' => __('Index page third column widgets', 'informea'),
    ));
    register_sidebar(array(
        'id' => 'about-page-left', 'name' => __('About page widgets', 'informea'),
        'description' => __('Widgets on the left column of the about page', 'informea'),
    ));
    register_sidebar(array(
        'id' => 'treaties-sidebar', 'name' => __('Treaties page sidebar', 'informea'),
        'description' => __('Widgets on the treaties listing page', 'informea'),
    ));
    register_sidebar(array(
        'id' => 'treaties-sidebar-item', 'name' => __('Treaty index sidebar', 'informea'),
        'description' => __('Widgets on the treaty factsheet page', 'informea'),
    ));
    register_sidebar(array(
        'id' => 'decisions-sidebar', 'name' => __('Decisions page sidebar', 'informea'),
        'description' => __('Widgets on the decisions listing page', 'informea'),
    ));
    register_sidebar(array(
        'id' => 'countries-sidebar', 'name' => __('Countries page sidebar', 'informea'),
        'description' => __('Widgets on the countries listing page', 'informea'),
    ));
    register_sidebar(array(
        'id' => 'countries-sidebar-item', 'name' => __('Country index sidebar', 'informea'),
        'description' => __('Widgets on the country factsheet page', 'informea'),
    ));
    register_sidebar(array(
        'id' => 'terms-sidebar', 'name' => __('Terms page sidebar', 'informea'),
        'description' => __('Widgets on the terms listing page', 'informea'),
    ));
    register_sidebar(array(
        'id' => 'terms-sidebar-item', 'name' => __('Term index sidebar', 'informea'),
        'description' => __('Widgets on the term factsheet page', 'informea'),
    ));
    register_sidebar(array(
        'id' => 'meetings-sidebar', 'name' => __('Meetings page sidebar', 'informea'),
        'description' => __('Widgets on the meetings listing page', 'informea'),
    ));
    register_sidebar(array(
        'id' => 'news-sidebar', 'name' => __('News page sidebar', 'informea'),
        'description' => __('Widgets on the news listing page', 'informea'),
    ));
}
add_action('widgets_init', 'informea2_widgets_init');


require_once(dirname(__FILE__) . '/widgets/collapsible-text.php');
require_once(dirname(__FILE__) . '/widgets/latest-news.php');
require_once(dirname(__FILE__) . '/widgets/featured-country.php');
require_once(dirname(__FILE__) . '/widgets/featured-treaty.php');
require_once(dirname(__FILE__) . '/widgets/current-week-meetings.php');
require_once(dirname(__FILE__) . '/widgets/index-mea-explorer.php');
require_once(dirname(__FILE__) . '/widgets/event-explorer.php');
require_once(dirname(__FILE__) . '/widgets/borderless-widget.php');
require_once(dirname(__FILE__) . '/widgets/terms-cloud.php');
require_once(dirname(__FILE__) . '/widgets/image-roller.php');
require_once(dirname(__FILE__) . '/widgets/search-treaties.php');
require_once(dirname(__FILE__) . '/widgets/select-country.php');
require_once(dirname(__FILE__) . '/widgets/find-nfp.php');
require_once(dirname(__FILE__) . '/widgets/mea-coverage.php');
require_once(dirname(__FILE__) . '/widgets/select-term.php');
require_once(dirname(__FILE__) . '/widgets/filter-meetings.php');
require_once(dirname(__FILE__) . '/widgets/filter-news.php');
require_once(dirname(__FILE__) . '/widgets/news-categories-widget.php');


