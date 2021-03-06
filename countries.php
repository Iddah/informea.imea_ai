<?php
/**
 * This class is the data provider for the 'Countries' section of the site.
 * @package InforMEA
 * @subpackage Theme
 * @since 1.0
 */

if (!class_exists('imea_countries_page')) {
    class imea_countries_page extends imea_page_base_page {


        protected $id_country = NULL;

        public $country = NULL;
        public $mea_membership = array();

        function __construct($id_country = NULL, $arr_parameters = array()) {
            parent::__construct($arr_parameters);
            if (!empty($id_country)) {
                $this->id_country = $id_country;
                $this->_get_country();
            }
        }


        /**
         * Retrieve the treaty memberships for a country
         * @param integer $id_country Country ID
         * @return array Array with countries and membership information, fields:
         * - short_title - Treaty name
         * - logo_medium - Treaty logo
         * - name - Country name
         * - icon_medium - Country flag
         * - id_country
         * - id_treaty
         * - date
         * - status
         * - legal_instrument_name
         * - legal_instrument_type
         * - parent_legal_instrument
         * - declarations
         * - notes
         */
        static function get_treaty_membership($id_country) {
            global $wpdb;

            return $wpdb->get_results($wpdb->prepare('
            SELECT t.short_title, t.logo_medium, t.odata_name,
            c.name, c.icon_medium,
            a.id_country, a.id_treaty, a.`date`, a.`status`, a.legal_instrument_name,
            a.legal_instrument_type, a.parent_legal_instrument, a.declarations, a.notes
            FROM ai_treaty_country a
            INNER JOIN ai_treaty t ON a.id_treaty = t.id
            INNER JOIN ai_country c ON a.id_country = c.id
            WHERE c.id = %d ORDER BY t.short_title', $id_country)
            );
        }


        /**
         * Determine if a country has membership information
         * @param integer $id_country Country ID
         * @return integer 0 if has not membership information or > 0
         */
        static function count_treaty_membership($id_country) {
            global $wpdb;
            return $wpdb->get_var($wpdb->prepare('
                    SELECT COUNT(*)
                    FROM ai_treaty_country a
                    INNER JOIN ai_treaty t ON a.id_treaty = t.id
                    INNER JOIN ai_country c ON a.id_country = c.id
                    WHERE c.id = %d ORDER BY t.short_title',
                $id_country)
            );
        }


        function format_membership_notes($row) {
            $ret = '';
            if (!empty($row->legal_instrument_name) || !empty($row->legal_instrument_type)
                || !empty($row->parent_legal_instrument)
                || !empty($row->declarations) || !empty($row->notes)
            ) {
                $ret .= '<ul>';
                if (!empty($row->legal_instrument_name)) {
                    $ret .= sprintf('<li>%s: <strong>%s</strong></li>', __('Legal instrument', 'ieg'), $row->legal_instrument_name);
                }
                if (!empty($row->legal_instrument_type)) {
                    $ret .= sprintf('<li>%s: <strong>%s</strong></li>', __('Legal instrument type', 'ieg'), $row->legal_instrument_type);
                }
                if (!empty($row->parent_legal_instrument)) {
                    $ret .= sprintf('<li>%s: <strong>%s</strong></li>', __('Parent legal instrument', 'ieg'), $row->parent_legal_instrument);
                }
                if (!empty($row->declarations)) {
                    $ret .= sprintf('<li>%s: <strong>%s</strong></li>', __('Declarations', 'ieg'), $row->declarations);
                }
                if (!empty($row->notes)) {
                    $ret .= sprintf('<li>%s: <strong>%s</strong></li>', __('Notes', 'ieg'), $row->notes);
                }
                $ret .= '</ul>';
            }
            return $ret;
        }

        /**
         * Retrive table data for index page of terms.
         * @return array with the rows displayable in template
         */
        function index() {
            global $wpdb;
            $ret = new stdClass();
            $ret->countries = $this->get_countries();
            $ret->statistics = $this->get_index_statistics();
            return $ret;
        }


        function get_index_statistics() {
            global $wpdb;

            $key = 'ui.index.table';
            $domain = 'country';

            $cache = new imea_cache();
            $statistics = $cache->get($key, $domain, TRUE);
            if (empty($statistics)) {
                $statistics = array();
                $countries = $this->get_countries();
                foreach ($countries as &$country) {
                    $country->count_decisions = $wpdb->get_var($wpdb->prepare('SELECT COUNT(*) FROM `ai_decision_country` WHERE id_country=%s', $country->id));
                    $country->count_focal_points = $wpdb->get_var($wpdb->prepare('SELECT COUNT(*) FROM `ai_people` WHERE id_country=%s', $country->id));
                    $country->count_national_plans = $wpdb->get_var($wpdb->prepare('SELECT COUNT(*) FROM `ai_country_plan` WHERE id_country=%s', $country->id));
                    $country->count_national_reports = $wpdb->get_var($wpdb->prepare('SELECT COUNT(*) FROM `ai_country_report` WHERE id_country=%s', $country->id));
                    $statistics['id_' . $country->id] = $country;
                }
                $cache->put($key, $domain, $statistics, 'json');
            }
            return $statistics;
        }


        /**
         * Access ai_country
         * @return Rows from the table
         */
        function _get_country() {
            global $wpdb;
            $sql = $wpdb->prepare("SELECT * FROM ai_country WHERE id = '%s'", $this->id_country);
            $this->country = $wpdb->get_row($sql);
            if ($this->country) {

                $sql = "SELECT a.*, b.year FROM ai_treaty a
                            JOIN ai_treaty_country b ON b.id_treaty = a.id
                            WHERE a.enabled = TRUE AND b.id_country = {$this->id_country} ORDER BY a.short_title";
                $this->mea_membership = $wpdb->get_results($sql);
            }
        }

        function get_country_object() {
            global $wpdb;
            $sql = $wpdb->prepare("SELECT * FROM ai_country WHERE id = '%s'", $this->id_country);
            return $wpdb->get_row($sql);
        }

        /**
         * Get the alphabet letters in use
         */
        static function get_alphabet_letters() {
            global $wpdb;
            $sql = "SELECT DISTINCT(UPPER(SUBSTR(name, 1, 1))) AS letter FROM ai_country ORDER BY letter";
            return $wpdb->get_results($sql);
        }

        static function index_alphabetical() {
            global $wpdb;
            $ret = array();
            $rows = $wpdb->get_results('SELECT UPPER(SUBSTR(a.name, 1, 1)) AS letter, a.id, a.name, a.code2l, a.icon_medium FROM ai_country a INNER JOIN ai_treaty_country b ON a.id = b.id_country GROUP BY a.id ORDER BY name');
            foreach($rows as $row) {
                if(empty($ret[$row->letter])) {
                    $ret[$row->letter] = array();
                }
                $ret[$row->letter][] = $row;
            }
            return $ret;
        }

        static function get_view_parties() {
            global $wpdb;
            $ret = array();
            $rows = $wpdb->get_results(
                'SELECT a.code2l, b.id_treaty, c.short_title, b.year, c.odata_name
                    FROM ai_country a
                    INNER JOIN ai_treaty_country b ON a.id = b.id_country
                    INNER JOIN ai_treaty c ON b.id_treaty = c.id
                ORDER BY a.code2l, b.year'
            );
            foreach($rows as $row) {
                if(!isset($ret[$row->code2l])) {
                    $ret[$row->code2l]  = array();
                }
                $ret[$row->code2l][] = $row;
            }
            return $ret;
        }

        static function get_view_treaties() {
            global $wpdb;
            $ret = array();
            $rows = $wpdb->get_results('
                SELECT c.id AS id_treaty, a.name, a.id AS id_country, b.year
                    FROM ai_country a
                    INNER JOIN ai_treaty_country b ON a.id = b.id_country
                    INNER JOIN ai_treaty c ON b.id_treaty = c.id
                ORDER BY c.id, a.name');
            foreach($rows as $row) {
                if(!isset($ret[$row->id_treaty])) {
                    $ret[$row->id_treaty]  = array();
                }
                $ret[$row->id_treaty][] = $row;
            }
            return $ret;
        }

        static function index_grid() {
            global $wpdb;
            $ret = array();
            $columns = $wpdb->get_results("SELECT * FROM ai_treaty WHERE enabled = 1");
            // Get the sign year for each treaty, for each country
            // Retrieve all the signed treaties in format $arr[id_treaty] = [ country1 => year, country2 => year etc. ]
            $tmparr = array();
            $data = $wpdb->get_results("SELECT * FROM ai_treaty_country;");
            foreach ($data as $row) {
                if (!isset($tmparr[$row->id_treaty])) {
                    $tmparr[$row->id_treaty] = array();
                }
                $tmparr[$row->id_treaty][$row->id_country] = $row->year;
            }
            // Filter out columns with no data
            $final_col = array();
            foreach ($columns as $column) {
                if (isset($tmparr[$column->id]) && count($tmparr[$column->id])) {
                    $final_col[] = $column;
                }
            }
            $ret['column'] = $final_col;
            $ret['signatures'] = $tmparr;
            $ret['countries'] = $wpdb->get_results("SELECT a.* FROM ai_country a INNER JOIN ai_treaty_country b ON a.id = b.id_country GROUP BY a.id ORDER BY name ");
            return $ret;
        }


        static function wrap_th($text) {
            $words = explode(' ', $text);
            return implode('<br />', $words);
        }

        /**
         * Count the total available focal points for a country.
         *
         * @param integer $id_country Country ID. If NULL, internal ID is used
         * @return integer Number of focal points
         */
        static function count_focal_points($id_country = NULL) {
            global $wpdb;
            return $wpdb->get_var(
                $wpdb->prepare('SELECT COUNT(*) FROM view_people_treaty WHERE id_country=%d', $id_country)
            );
        }


        /**
         * Format the HTML for the contact column on country profile.
         *
         * @param object $contact Contact database object
         * @return string HTML list with contact information
         */
        function format_focal_point_contact($contact) {
            $ret = '';
            if (!empty($contact->address) || !empty($contact->email)
                || !empty($contact->telephone) || !empty($contact->fax)
            ) {
                $ret .= '<ul>';
                if (!empty($contact->address)) {
                    $ret .= sprintf('<li>Address:<br /><strong><blockquote class="copyable">%s</blockquote></strong><small>Tip: click address to select</small></li>', $contact->address);
                }
                //@todo - Implement contact form
                if (false) {
                    $ret .= sprintf('<li>E-Mail: <strong>%s</strong></li>', '@todo: contact form');
                }
                if (!empty($contact->telephone)) {
                    $ret .= sprintf('<li>Phone: <strong>%s</strong></li>', $contact->telephone);
                }
                if (!empty($contact->fax)) {
                    $ret .= sprintf('<li>Fax: <strong>%s</strong></li>', $contact->fax);
                }
                $ret .= '</ul>';
            }
            return $ret;
        }


        /**
         * Get the related meetings for this country
         */
        function get_related_meetings($limit = 5) {
            global $wpdb;
            $ret = array();
            if ($this->id_country) {
                $sql = "SELECT a.*, b.short_title FROM ai_event a
                        INNER JOIN ai_treaty b ON a.id_treaty = b.id
                        WHERE a.id_country = {$this->id_country} AND (a.start > CURRENT_DATE() OR a.end > CURRENT_DATE())
                        ORDER BY a.start DESC, a.end DESC LIMIT $limit";
                $ret = $wpdb->get_results($sql);
            }
            return $ret;
        }


        /**
         * Return the list of Ramsar sites for a country.
         * @param integer $id_country Country ID. If NULL internal ID is used
         * @return array Array of ai_sites objects
         * @global $wpdb WordPress database
         */
        static function get_ramsar_sites($id_country = NULL) {
            return self::get_sites($id_country, 'ramsar');
        }


        /**
         * Count the total RAMSAR sites for a country.
         *
         * @param integer $id_country Country ID. If NULL, internal ID is used
         * @return integer Number of RAMSAR sites
         * @global $wpdb WordPress database
         */
        static function count_ramsar_sites($id_country = NULL) {
            return self::count_sites($id_country, 'ramsar');
        }


        /**
         * Return the list of WHC sites for a country.
         * @param integer $id_country Country ID. If NULL internal ID is used
         * @return array Array of ai_sites objects
         * @global $wpdb WordPress database
         */
        static function get_whc_sites($id_country = NULL) {
            return self::get_sites($id_country, 'whc');
        }


        /**
         * Count the total WHC sites for a country.
         *
         * @param integer $id_country Country ID. If NULL, internal ID is used
         * @return integer Number of WHC sites
         * @global $wpdb WordPress database
         */
        static function count_whc_sites($id_country = NULL) {
            return self::count_sites($id_country, 'whc');
        }


        /**
         * Get the 'type' of sites for a country. Currently applies to 'ramsar' and 'whc'
         *
         * @param integer $id_country Country ID. If NULL, internal ID is used
         * @param string $type Type of site. 'ramsar' or 'whc'.
         * @return integer Number of RAMSAR sites
         * @global $wpdb WordPress database
         */
        static protected function get_sites($id_country, $type) {
            global $wpdb;
            if (!empty($id_country)) {

                return $wpdb->get_results(
                    $wpdb->prepare(
                        "SELECT a.*, c.name AS country_name FROM ai_country_site a
                            INNER JOIN ai_treaty b ON a.id_treaty = b.id
                            INNER JOIN ai_country c ON a.id_country = c.id
                            WHERE b.odata_name=%s AND a.id_country=%d ORDER BY a.name",
                        $type, $id_country
                    )
                );
            }
            return array();
        }


        /**
         * Count the total 'type' of sites for a country. Currently applies to 'ramsar' and 'whc'
         *
         * @param integer $id_country Country ID. If NULL, internal ID is used
         * @param string $type Type of site. 'ramsar' or 'whc'.
         * @return integer Number of sites
         * @global $wpdb WordPress database
         */
        static protected function count_sites($id_country, $type) {
            global $wpdb;

            if (!empty($id_country)) {
                return $wpdb->get_var(
                    $wpdb->prepare(
                        "SELECT COUNT(*) FROM ai_country_site a INNER JOIN ai_treaty b ON a.id_treaty = b.id WHERE b.odata_name=%s AND a.id_country=%d",
                        $type, $id_country
                    )
                );
            }
            return 0;
        }


        /**
         * Retrieve national reports for a country, group by treaty
         *
         * @param integer $id_country Country ID
         * @return array Array of treaties having property national_reports array with ai_country_report objects
         */
        static function get_national_reports($id_country = NULL) {
            global $wpdb;
            $treaties = $wpdb->get_results(
                $wpdb->prepare("SELECT b.* FROM ai_country_report a INNER JOIN ai_treaty b ON a.id_treaty = b.id WHERE b.enabled = TRUE AND a.id_country=%d GROUP BY b.id", $id_country)
                , OBJECT_K
            );

            $rows = $wpdb->get_results(
                $wpdb->prepare("SELECT a.*, b.title AS meeting_title FROM ai_country_report a LEFT JOIN ai_event b ON a.id_event = b.id WHERE a.id_country=%d ORDER BY a.submission DESC", $id_country)
            );
            foreach ($rows as $row) {
                $treaty = $treaties[$row->id_treaty];
                if (!isset($treaty->national_reports)) {
                    $treaty->national_reports = array();
                }
                $treaty->national_reports[] = $row;
            }
            return $treaties;
        }


        /**
         * Retrieve the count of national reports for a country
         *
         * @param integer $id_country Country ID
         * @return integer Count
         */
        static function count_national_reports($id_country = NULL) {
            global $wpdb;
            return $wpdb->get_var(
                $wpdb->prepare("SELECT COUNT(*) FROM ai_country_report WHERE id_country=%d", $id_country)
            );
        }


        /**
         * Retrieve national plans for a country, group by treaty
         *
         * @param integer $id_country Country ID
         * @return array Array of treaties having property national_plans array with ai_country_plan objects
         */
        static function get_national_plans($id_country = NULL) {
            global $wpdb;
            $treaties = $wpdb->get_results(
                $wpdb->prepare("SELECT b.* FROM ai_country_plan a INNER JOIN ai_treaty b ON a.id_treaty = b.id WHERE b.enabled = TRUE AND a.id_country=%d GROUP BY b.id", $id_country)
                , OBJECT_K
            );

            $rows = $wpdb->get_results(
                $wpdb->prepare("SELECT a.*, b.title AS meeting_title FROM ai_country_plan a LEFT JOIN ai_event b ON a.id_event = b.id WHERE a.id_country=%d ORDER BY a.submission DESC", $id_country)
            );
            foreach ($rows as $row) {
                $treaty = $treaties[$row->id_treaty];
                if (!isset($treaty->national_plans)) {
                    $treaty->national_plans = array();
                }
                $treaty->national_plans[] = $row;
            }
            return $treaties;
        }


        /**
         * Retrieve the count of national plans for a country
         *
         * @param integer $id_country Country ID
         * @return integer Count
         */
        static function count_national_plans($id_country = NULL) {
            global $wpdb;
            return $wpdb->get_var(
                $wpdb->prepare("SELECT COUNT(*) FROM ai_country_plan WHERE id_country=%d", $id_country)
            );
        }


        function filter_national_plans($id_treaty, $id_country) {
            global $wpdb;
            $sql = 'SELECT * FROM `ai_country_plan` WHERE 1 = 1';
            if (!empty($id_treaty)) {
                $sql .= ' AND id_treaty = ' . $wpdb->escape($id_treaty);
            }
            if (!empty($id_country)) {
                $sql .= ' AND id_country = ' . $wpdb->escape($id_country);
            }
            return $wpdb->get_results($sql);
        }

        function get_national_plan($id) {
            global $wpdb;
            return $wpdb->get_row($wpdb->prepare('SELECT * FROM `ai_country_plan` WHERE `id` = %d', $id));
        }


        function filter_national_reports($id_treaty, $id_country) {
            global $wpdb;
            $sql = 'SELECT * FROM `ai_country_report` WHERE 1 = 1';
            if (!empty($id_treaty)) {
                $sql .= ' AND id_treaty = ' . $wpdb->escape($id_treaty);
            }
            if (!empty($id_country)) {
                $sql .= ' AND id_country = ' . $wpdb->escape($id_country);
            }
            return $wpdb->get_results($sql);
        }

        function get_national_report($id) {
            global $wpdb;
            return $wpdb->get_row($wpdb->prepare('SELECT * FROM `ai_country_report` WHERE `id` = %d', $id));
        }


        static function get_treaties_with_membership() {
            global $wpdb;
            return $wpdb->get_results('SELECT b.* FROM ai_treaty_country a
                INNER JOIN ai_treaty b ON b.`id` = a.`id_treaty`
                GROUP BY b.`id` ORDER BY b.`short_title`');
        }


        static function gis_get_membership_filter($mea_id) {
            global $wpdb;
            $rows = $wpdb->get_col($wpdb->prepare('SELECT a.code2l FROM ai_country a
                INNER JOIN ai_treaty_country b ON a.`id` = b.`id_country`
                WHERE b.`id_treaty` = %d', $mea_id));
            return implode('|', $rows);
        }

        /**
         * @return id_country was set on GET but with invalid ID
         */
        function check_404() {
            global $wp_query;
            $code = get_request_variable('code');
            if (!empty($code) && empty($this->country)) {
                $wp_query->set_404();
                require TEMPLATEPATH . '/404.php';
                exit;
            }
        }


        /**
         * Overriding
         * @imea_page_base_page::is_index
         * Called statically by wordpress framework
         */
        function is_index() {
            return $this->id_country == NULL;
        }


        /**
         * Append country to page title
         * Called statically by wordpress framework
         */
        function informea_page_title() {
            global $id_country, $page_data;
            if ($id_country !== NULL) {
                return "{$page_data->country->name} | ";
            }
            return '';
        }

        static function get_countries() {
            global $wpdb;
            return $wpdb->get_results("SELECT * FROM ai_country ORDER BY name");
        }

        static function get_countries_keyed_iso2l() {
            global $wpdb;
            $ret = array();
            $rows = $wpdb->get_results("SELECT * FROM ai_country ORDER BY name");
            foreach($rows as $row) {
                $ret[$row->code2l] = $row;
            }
            return $ret;
        }

        function get_eu_countries() {
            global $wpdb;
            return $wpdb->get_results("SELECT * FROM ai_country WHERE eu_member IS NOT NULL ORDER BY name");
        }


        static function get_country_for_id($id) {
            global $wpdb;
            return $wpdb->get_row("SELECT * FROM ai_country where id=" . $id);
        }


        /**
         * Find country by ISO-2L or ISO-3L code.
         *
         * @global object $wpdb WordPress query object
         * @param string $iso Country ISO 2-letter or 3-letter code
         * @return object Row object or NULL if not found
         */
        static function get_country_by_iso($iso) {
            global $wpdb;

            if (!empty($iso)) {

                return $wpdb->get_row($wpdb->prepare('SELECT * FROM ai_country WHERE LOWER(code2l)=LOWER(%s) OR LOWER(code)=LOWER(%s) LIMIT 1', $iso, $iso));
            }
        }


        /**
         * Find country by ISO-2L code.
         *
         * @global object $wpdb WordPress query object
         * @param string $iso2 Country ISO 2-letter code
         * @return object Row object or NULL if not found
         */
        function get_country_by_iso2($iso2) {
            global $wpdb;

            if (!empty($iso2)) {
                return $wpdb->get_row($wpdb->prepare('SELECT * FROM ai_country WHERE LOWER(code2l)=LOWER(%s) LIMIT 1', $iso2));
            }
        }


        /**
         * Find country by ISO-3L code.
         *
         * @global object $wpdb WordPress query object
         * @param string $iso3 Country ISO 3-letter code
         * @return object Row object or NULL if not found
         */
        function get_country_by_iso3($iso3) {
            global $wpdb;

            if (!empty($iso3)) {
                return $wpdb->get_row($wpdb->prepare('SELECT * FROM ai_country WHERE LOWER(code)=LOWER(%s) LIMIT 1', $iso3));
            }
        }


        function search_countries_by_name($name) {
            global $wpdb;
            return $wpdb->get_results("SELECT * FROM ai_country WHERE name LIKE '%$name%'");
        }


        /**
         * Get the country by its name. Case insensitive search on name and long name
         *
         * @param string $name Country name or official name
         * @return object Country object or NULL if not found
         */
        function get_country_by_name($name) {
            global $wpdb;
            if (!empty($name)) {
                $name = preg_replace('/\s+/', ' ', $name); // Remove unholy characters (multi-spaces, tab, newline etc.)
                $name = trim($name);
                $name = strtolower($name);
                return $wpdb->get_row($wpdb->prepare('SELECT * FROM ai_country WHERE LOWER(name) = %s OR LOWER(long_name) = %s LIMIT 1', $name, $name));
            }
            return NULL;
        }


        function search_nfp_by_name($name) {
            global $wpdb;
            $ret = array();
            $objects = $wpdb->get_results(
                $wpdb->prepare(
                    'SELECT a.*, b.`name` AS country_name FROM `ai_people` a
                        INNER JOIN `ai_country` b ON a.`id_country` = b.id
                        WHERE (`first_name` LIKE %s OR `last_name` LIKE %s) LIMIT 30',
                    $name . '%', $name . '%')
            );
            foreach ($objects as $nfp) {
                $ob = new StdClass();
                $ob->first_name = $nfp->first_name;
                $ob->last_name = $nfp->last_name;
                $ob->country_name = $nfp->country_name;
                $ob->id = $nfp->id;
                $ob->id_country = $nfp->id_country;
                $ob->id_treaty = $wpdb->get_var("SELECT id_treaty FROM `ai_people_treaty` WHERE id_people={$nfp->id} LIMIT 1;");
                $ret[] = $ob;
            }
            return $ret;
        }

        static function get_random_country() {
            global $wpdb;
            return $wpdb->get_row("SELECT * FROM ai_country ORDER BY rand() LIMIT 1");
        }


        static function get_random_country_with_data() {
            global $wpdb;
            return $wpdb->get_row("SELECT a.* FROM ai_country a
                INNER JOIN ai_people b ON a.id = b.id_country
                INNER JOIN ai_treaty_country c ON a.id = c.id_country
                GROUP by a.id
                ORDER BY rand() LIMIT 1;"
            );
        }


        static function get_featured_country() {
            $option = get_option('informea_options');
            $country = null;
            if (isset($option['featured_country'])) {
                $d = $option['featured_country_timestamp'];
                if (time() - $d < 3600 * 24) {
                    $country = $option['featured_country'];
                }
            }
            if ($country == null) {
                $country = self::get_random_country_with_data();
                $option['featured_country'] = $country;
                $option['featured_country_timestamp'] = time();
                update_option('informea_options', $option);
            }
            return $country;
        }


        static function get_country_for_visitor() {
            $client  = @$_SERVER['HTTP_CLIENT_IP'];
            $forward = @$_SERVER['HTTP_X_FORWARDED_FOR'];
            $remote  = $_SERVER['REMOTE_ADDR'];
            $ret = NULL;
            $ip = NULL;
            if(filter_var($remote, FILTER_VALIDATE_IP)) {
                $ip = $remote;
            } elseif(filter_var($forward, FILTER_VALIDATE_IP)) {
                $ip = $forward;
            } elseif(filter_var($client, FILTER_VALIDATE_IP)) {
                $ip = $client;
            }
            $iso = NULL;
            // Check the cache first
            $cache = new imea_cache();
            $cached_ips = $cache->get('user_ip_country', 'global', TRUE);
            if(empty($cached_ips)) {
                $cached_ips = array();
            }
            if(!empty($cached_ips[$ip])) {
                $iso = $cached_ips[$ip];
            } else {
                $ip_data = @json_decode(file_get_contents("http://www.geoplugin.net/json.gp?ip=".$ip));
                if(!empty($ip_data->geoplugin_countryName)) {
                    $cached_ips[$ip] = $ip_data->geoplugin_countryCode;
                    $iso = $cached_ips[$ip];
                    $cache->put('user_ip_country', 'global', $cached_ips, 'json');
                }
            }
            $cache->put('user_ip_country', 'global', $cached_ips, 'json');
            if(!empty($iso)) {
                $ret = informea_countries::get_country_by_iso($iso);
            } else {
                $ret = self::get_featured_country();
            }
            return $ret;
        }

        /**
         * Retrieve data for the peblds tab in country profile
         */
        function get_peblds_data($id_country) {
            global $wpdb;
            $ret = new StdClass();
            $ret->has_data = false;
            $ret->best_practices = array();
            $ret->projects = array();
            $ret->technical_reports = array();

            // Best practice
            $ret->best_practices = $wpdb->get_results($wpdb->prepare("SELECT * FROM peblds_best_practice WHERE id_country = %d", $id_country));
            foreach ($ret->best_practices as &$best_practice) {
                $best_practice->files = $wpdb->get_results($wpdb->prepare("SELECT a.* FROM `peblds_wpfb_file` a
                    INNER JOIN `peblds_best_practice_wpfb_files` b ON b.`id_wpfb_files` = a.`id`
                    WHERE b.`id_best_practice` = %d", $best_practice->id));
                $best_practice->topic = $wpdb->get_row($wpdb->prepare("SELECT * FROM `peblds_topic` WHERE id = %d", $best_practice->id_topic));
                $best_practice->treaty = $wpdb->get_row($wpdb->prepare("SELECT * FROM `ai_treaty` WHERE id = %d", $best_practice->id_treaty));
            }

            // Project
            $ret->projects = $wpdb->get_results($wpdb->prepare("SELECT a.* FROM `peblds_project` a
                INNER JOIN `peblds_project_country` b ON b.`id_project` = a.`id`
                WHERE b.`id_country` = %d", $id_country));
            foreach ($ret->projects as &$project) {
                $project->countries = $wpdb->get_results($wpdb->prepare("SELECT a.* FROM `ai_country` a
                    INNER JOIN `peblds_project_country` b ON b.`id_country` = a.`id`
                    WHERE b.`id_project` = %d", $project->id));
                $project->treaties = $wpdb->get_results($wpdb->prepare("SELECT a.* FROM `ai_treaty` a
                        INNER JOIN `peblds_project_treaty` b ON b.`id_treaty` = a.`id`
                        WHERE b.`id_project` = %d", $project->id));
                $project->files = $wpdb->get_results($wpdb->prepare("SELECT a.* FROM `peblds_wpfb_file` a
                        INNER JOIN `peblds_project_wpfb_files` b ON b.`id_wpfb_files` = a.`id`
                        WHERE b.`id_project` = %d", $project->id));
            }

            // Technical reports
            // Project
            $ret->technical_reports = $wpdb->get_results($wpdb->prepare("SELECT * FROM `peblds_technical_report` WHERE id_country = %d", $id_country));
            foreach ($ret->technical_reports as &$report) {
                $report->topics = $wpdb->get_results($wpdb->prepare("SELECT a.* FROM `peblds_topic` a
                    INNER JOIN `peblds_technical_report_topic` b ON b.`id_topic` = a.`id`
                    WHERE b.`id_technical_report` = %d", $report->id));
                $report->files = $wpdb->get_results($wpdb->prepare("SELECT a.* FROM `peblds_wpfb_file` a
                    INNER JOIN `peblds_technical_report_wpfb_files` b ON b.`id_wpfb_files` = a.`id`
                    WHERE b.`id_technical_report` = %d", $report->id));
                $report->treaty = $wpdb->get_row($wpdb->prepare("SELECT * FROM `ai_treaty` WHERE id = %d", $report->id_treaty));
            }

            $ret->has_data = !empty($ret->best_practices) || !empty($ret->projects) || !empty($ret->technical_reports);
            return $ret;
        }

        // !!!!!!!!!! ADMINISTRATION AREA !!!!!!!!!!!!!!!!!!!!!!
        function get_treaties_w_contacts() {
            global $wpdb;
            return $wpdb->get_results(
                "SELECT a.* FROM `ai_treaty` a
                    INNER JOIN `ai_people_treaty` b ON a.`id` = b.`id_treaty` GROUP BY a.`id`");
        }

        function get_people_for_treaty($id_treaty) {
            global $wpdb;
            return $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT a.*, b.`name` AS country_name FROM `ai_people` a
                        INNER JOIN `ai_country` b ON a.`id_country` = b.`id`
                        INNER JOIN `ai_people_treaty` c ON a.`id` = c.`id_people`
                        WHERE c.`id_treaty` = %d ORDER BY b.`name`", $id_treaty)
            );
        }

        static function get_nfp($id_people) {
            global $wpdb;
            return $wpdb->get_row($wpdb->prepare("SELECT a.*, b.`name` AS country_name, b.`icon_medium`, b.`icon_large` FROM `ai_people` a INNER JOIN `ai_country` b ON a.`id_country` = b.`id` WHERE a.`id` = %d", $id_people));
        }

        function label_contact($contact) {
            $ret = "{$contact->country_name} - ";
            if (!empty($contact->first_name)) {
                $ret .= $contact->first_name . ' ';
            }
            if (!empty($contact->last_name)) {
                $ret .= $contact->last_name . ' ';
            }
            if (empty($ret) && !empty($contact->position)) {
                $ret .= $contact->position . ' ';
            }
            if (empty($ret) && !empty($contact->institution)) {
                $ret .= $contact->institution . ' ';
            }
            return $ret;
        }

        function get_treaties() {
            global $wpdb;
            return $wpdb->get_results("SELECT a.* FROM `ai_treaty` a WHERE `enabled` = 1 ORDER BY `order`");
        }

        function get_treaties_indexed() {
            global $wpdb;
            $ret = array();
            $rows = $wpdb->get_results("SELECT a.* FROM `ai_treaty` a WHERE `enabled` = 1 ORDER BY `order`");
            foreach ($rows as $row) {
                $ret[$row->id] = $row;
            }
            return $ret;
        }

        function get_contact_treaties($contact) {
            global $wpdb;
            return $wpdb->get_col($wpdb->prepare('SELECT id_treaty FROM ai_people_treaty WHERE id_people = %d', $contact->id));
        }

        function validate_nfp_edit() {
            $this->actioned = TRUE;
            $val = new FormValidator();
            $val->addValidation("id_people", "req", "Invalid person");
            $val->addValidation("id_country", "req", "Invalid country");
            $val->addValidation("email", "email", "Invalid email address");
            $valid = $val->ValidateForm();
            if (!$valid) {
                $this->errors = $val->GetErrors();
            }
            if (!check_admin_referer('edit_nfp')) {
                $valid = false;
                $this->errors['security'] = 'Invalid security token';
            }
            $treaties = get_request_value('treaty', array(), false);
            if (empty($treaties)) {
                $valid = false;
                $this->errors['treaty'] = 'Please select treaties';
            }
            return $valid;
        }

        function validate_nfp_add() {
            $this->actioned = TRUE;
            $val = new FormValidator();
            $val->addValidation("id_country", "req", "Invalid country");
            $val->addValidation("email", "email", "Invalid email address");
            $valid = $val->ValidateForm();
            if (!$valid) {
                $this->errors = $val->GetErrors();
            }
            if (!check_admin_referer('add_nfp')) {
                $valid = false;
                $this->errors['security'] = 'Invalid security token';
            }
            $treaties = get_request_value('treaty', array(), false);
            if (empty($treaties)) {
                $valid = false;
                $this->errors['treaty'] = 'Please select treaties';
            }
            return $valid;
        }

        function nfp_edit() {
            global $wpdb;
            global $current_user;
            $this->actioned = TRUE;
            if ($this->validate_nfp_edit()) {
                $user = $current_user->user_login;
                $treaties = get_request_value('treaty', array(), false);
                $id_people = get_request_int('id_people');
                $id_country = get_request_int('id_country');
                $rec_created = date('Y-m-d H:i:s', strtotime("now"));
                @mysql_query("BEGIN", $wpdb->dbh);
                try {
                    // Delete old relationships
                    $wpdb->query($wpdb->prepare("DELETE FROM ai_people_treaty WHERE id_people = %d", $id_people));
                    // Add new relationships
                    foreach ($treaties as $id_treaty) {
                        $success = $wpdb->insert('ai_people_treaty',
                            array(
                                'id_people' => $id_people,
                                'id_treaty' => intval($id_treaty)
                            )
                        );
                    }

                    // Update the person
                    $this->success = $wpdb->update('ai_people', array(
                            'id_country' => get_request_int('id_country'),
                            'prefix' => stripslashes(get_request_value('prefix')),
                            'first_name' => stripslashes(get_request_value('first_name')),
                            'last_name' => stripslashes(get_request_value('last_name')),
                            'position' => stripslashes(get_request_value('position')),
                            'institution' => stripslashes(get_request_value('institution')),
                            'department' => stripslashes(get_request_value('department')),
                            'address' => stripslashes(get_request_value('address')),
                            'email' => get_request_value('email'),
                            'telephone' => get_request_value('telephone'),
                            'fax' => get_request_value('fax'),
                            'is_primary' => get_request_boolean('is_primary'),
                            'rec_updated' => date('Y-m-d H:i:s', strtotime("now")),
                            'rec_updated_author' => $user
                        ),
                        array('id' => $id_people)
                    );
                    @mysql_query("COMMIT", $wpdb->dbh);
                    $this->success = true;

                    // Log the action
                    $contact = $this->get_nfp($id_people);
                    $label = $this->label_contact($contact);
                    $url = sprintf('%s/countries/%d?showall=true#contact-%d', get_bloginfo('url'), $id_country, $id_people);
                    $this->add_activity_log('update', 'focal point', "Updated details for focal point with ID: {$id_people} - $label", null, $url);
                } catch (Exception $e) {
                    $this->success = FALSE;
                    @mysql_query("ROLLBACK", $wpdb->dbh);
                }
            }
        }

        function nfp_duplicates() {
            global $wpdb;
            $group = get_request_value('group', array('first_name', 'last_name'), false);
            if (empty($group)) {
                return array();
            }
            $arr = array();
            foreach ($group as $row) {
                $arr[] = $wpdb->escape($row);
            }
            $ret = $wpdb->get_results('SELECT *, COUNT(*) FROM ai_people WHERE first_name IS NOT NULL AND last_name IS NOT NULL GROUP BY ' . implode(', ', $arr) . ' HAVING COUNT(*) > 1');
            // var_dump($wpdb->last_query);
            return $ret;
        }

        /**
         * @return all clones including itself
         */
        function nfp_clones($contact) {
            global $wpdb;
            $group = get_request_value('group', array('first_name', 'last_name'), false);
            if (empty($group)) {
                return array();
            }
            $arr = array();
            foreach ($group as $row) {
                $arr[] = $wpdb->escape($row);
            }
            $sql = 'SELECT * FROM ai_people WHERE 1=1 ';
            if (in_array('first_name', $group)) {
                $sql .= ' AND first_name = \'' . $contact->first_name . '\'';
            }
            if (in_array('last_name', $group)) {
                $sql .= ' AND last_name = \'' . $contact->last_name . '\'';
            }
            if (in_array('email', $group)) {
                $sql .= ' AND email = \'' . $contact->email . '\'';
            }
            // $sql .= ' AND id <> ' . $contact->id;
            $ret = $wpdb->get_results($sql);
            return $ret;
        }

        function nfp_delete() {
            global $wpdb;
            global $current_user;
            $this->actioned = TRUE;
            $id_people = get_request_value('id_people', array(), false);
            @mysql_query("BEGIN", $wpdb->dbh);
            try {
                $contact = $this->get_nfp($id_people);

                if (!empty($contact)) {
                    $wpdb->query($wpdb->prepare("DELETE FROM ai_people_treaty WHERE id_people = %d", $id_people));
                    $wpdb->query($wpdb->prepare("DELETE FROM ai_people WHERE id = %d", $id_people));

                    $label = $this->label_contact($contact);
                    $this->add_activity_log('delete', 'focal point', "Removed focal point with ID: {$id_people} - '{$label}'", null, null);
                    @mysql_query("COMMIT", $wpdb->dbh);
                    $this->success = true;
                } else {
                    $this->success = false;
                    $this->errors['id_contact'] = 'Contact not found';
                    @mysql_query("ROLLBACK", $wpdb->dbh);
                }
            } catch (Exception $e) {
                $this->success = FALSE;
                @mysql_query("ROLLBACK", $wpdb->dbh);
            }
        }


        function nfp_add() {
            global $wpdb;
            global $current_user;
            $this->actioned = TRUE;
            if ($this->validate_nfp_add()) {
                $user = $current_user->user_login;
                $treaties = get_request_value('treaty', array(), false);
                $id_country = get_request_int('id_country');
                $rec_created = date('Y-m-d H:i:s', strtotime("now"));
                @mysql_query("BEGIN", $wpdb->dbh);
                try {
                    // Find next autoincrement value
                    $next_id = $wpdb->get_row("SHOW TABLE STATUS LIKE 'ai_people'");

                    // Update the person
                    $this->success = $wpdb->insert('ai_people', array(
                            'id_country' => get_request_int('id_country'),
                            'original_id' => 'manual-backend-' . $next_id->Auto_increment,
                            'prefix' => stripslashes(get_request_value('prefix')),
                            'first_name' => stripslashes(get_request_value('first_name')),
                            'last_name' => stripslashes(get_request_value('last_name')),
                            'position' => stripslashes(get_request_value('position')),
                            'institution' => stripslashes(get_request_value('institution')),
                            'department' => stripslashes(get_request_value('department')),
                            'address' => stripslashes(get_request_value('address')),
                            'email' => get_request_value('email'),
                            'telephone' => get_request_value('telephone'),
                            'fax' => get_request_value('fax'),
                            'is_primary' => get_request_boolean('is_primary'),
                            'rec_updated' => date('Y-m-d H:i:s', strtotime("now")),
                            'rec_updated_author' => $user
                        )
                    );
                    $id_people = $wpdb->insert_id;
                    // Add new relationships
                    foreach ($treaties as $id_treaty) {
                        $success = $wpdb->insert('ai_people_treaty',
                            array(
                                'id_people' => $id_people,
                                'id_treaty' => intval($id_treaty)
                            )
                        );
                    }
                    @mysql_query("COMMIT", $wpdb->dbh);
                    $this->success = true;

                    // Log the action
                    $contact = $this->get_nfp($id_people);
                    $label = $this->label_contact($contact);
                    $url = sprintf('%s/countries/%d?showall=true#contact-%d', get_bloginfo('url'), $id_country, $id_people);
                    $this->add_activity_log('insert', 'focal point', "Create new focal point with ID: {$id_people} - '$label'", null, $url);
                } catch (Exception $e) {
                    $this->success = FALSE;
                    @mysql_query("ROLLBACK", $wpdb->dbh);
                }
            }
        }

        function nfp_search() {
            global $wpdb;
            $text = get_request_value('text');
            return $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT a.*, b.`name` AS `country_name` FROM `ai_people` a
                        LEFT JOIN `ai_country` b ON a.`id_country` = b.`id`
                        WHERE first_name LIKE '%%%s%%'
                            OR last_name LIKE '%%%s%%'
                            OR position LIKE '%%%s%%'
                            OR institution LIKE '%%%s%%'
                            OR department LIKE '%%%s%%'
                            OR address LIKE '%%%s%%'
                            OR email LIKE '%%%s%%'
                            OR telephone LIKE '%%%s%%'
                            OR fax LIKE '%%%s%%'
                    ", $text, $text, $text, $text, $text, $text, $text, $text, $text)
            );
        }


        function validate_national_plan_add() {
            $this->actioned = TRUE;
            $val = new FormValidator();
            $val->addValidation("id_treaty", "req", "Please select treaty");
            $val->addValidation("id_country", "req", "Please select country");
            $val->addValidation("title", "req", "Please enter the title");
            $val->addValidation("type", "req", "Please enter type of plan");
            $val->addValidation("submission", "req", "Please enter type of plan");
            $val->addValidation("document_url", "req", "Please enter document URL");
            $valid = $val->ValidateForm();
            if (!$valid) {
                $this->errors = $val->GetErrors();
            }
            if (!check_admin_referer('add_national_plan')) {
                $valid = false;
                $this->errors['security'] = 'Invalid security token';
            }
            return $valid;
        }


        function national_plan_add() {
            global $wpdb;
            global $current_user;
            $this->actioned = TRUE;
            $user = $current_user->user_login;

            $rec_created = date('Y-m-d H:i:s', strtotime("now"));
            $id_country = get_request_int('id_country');
            $id_event = get_request_int('id_event');
            $title = stripslashes(get_request_value('title'));
            @mysql_query("BEGIN", $wpdb->dbh);
            try {
                // Find next autoincrement value
                $next_id = $wpdb->get_row("SHOW TABLE STATUS LIKE 'ai_country_plan'");
                $data = array(
                    'original_id' => 'manual-backend-' . $next_id->Auto_increment,
                    'id_treaty' => get_request_int('id_treaty'),
                    'id_country' => $id_country,
                    'type' => get_request_value('type'),
                    'title' => $title,
                    'submission' => stripslashes(get_request_value('submission')),
                    'document_url' => stripslashes(get_request_value('document_url')),
                    'rec_created' => $rec_created,
                    'rec_author' => $user
                );
                if (!empty($id_event)) {
                    $data['id_event'] = $id_event;
                }

                $success = $wpdb->insert('ai_country_plan', $data);
                @mysql_query("COMMIT", $wpdb->dbh);
                $this->success = true;
                // Log the action
                $country = $this->get_country_for_id($id_country);
                $url = sprintf('%s/countries/%d/plans', get_bloginfo('url'), $id_country);
                $this->add_activity_log('insert', 'national plan', "Created new national plan for {$country->name} - $title", null, $url);
            } catch (Exception $e) {
                $this->success = FALSE;
                @mysql_query("ROLLBACK", $wpdb->dbh);
            }
        }


        function validate_national_plan_edit() {
            $this->actioned = TRUE;
            $val = new FormValidator();
            $val->addValidation("id_national_plan", "req", "Please select the national plan to edit");
            $val->addValidation("id_treaty", "req", "Please select treaty");
            $val->addValidation("id_country", "req", "Please select country");
            $val->addValidation("title", "req", "Please enter the title");
            $val->addValidation("type", "req", "Please enter type of plan");
            $val->addValidation("submission", "req", "Please enter type of plan");
            $val->addValidation("document_url", "req", "Please enter document URL");
            $valid = $val->ValidateForm();
            if (!$valid) {
                $this->errors = $val->GetErrors();
            }
            if (!check_admin_referer('edit_national_plan')) {
                $valid = false;
                $this->errors['security'] = 'Invalid security token';
            }
            return $valid;
        }


        function national_plan_edit() {
            global $wpdb;
            global $current_user;
            $this->actioned = TRUE;
            $user = $current_user->user_login;

            $rec_created = date('Y-m-d H:i:s', strtotime("now"));
            $id_national_plan = get_request_int('id_national_plan');
            $id_country = get_request_int('id_country');
            $id_event = get_request_int('id_event');
            $title = stripslashes(get_request_value('title'));
            @mysql_query("BEGIN", $wpdb->dbh);
            try {
                $data = array(
                    'id_treaty' => get_request_int('id_treaty'),
                    'id_country' => $id_country,
                    'type' => get_request_value('type'),
                    'title' => $title,
                    'submission' => stripslashes(get_request_value('submission')),
                    'document_url' => stripslashes(get_request_value('document_url')),
                    'rec_created' => $rec_created,
                    'rec_author' => $user
                );
                if (!empty($id_event)) {
                    $data['id_event'] = $id_event;
                }
                // Find next autoincrement value
                $next_id = $wpdb->get_row("SHOW TABLE STATUS LIKE 'ai_country_plan'");
                $success = $wpdb->update('ai_country_plan', $data, array('id' => $id_national_plan));
                @mysql_query("COMMIT", $wpdb->dbh);
                $this->success = true;
                // Log the action
                $country = $this->get_country_for_id($id_country);
                $url = sprintf('%s/countries/%d/plans', get_bloginfo('url'), $id_country);
                $this->add_activity_log('update', 'national plan', "Updated national plan for {$country->name} - $title", null, $url);
            } catch (Exception $e) {
                $this->success = FALSE;
                @mysql_query("ROLLBACK", $wpdb->dbh);
            }
        }


        function get_national_plan_types() {
            return array('nama' => 'NAMA', 'nap' => 'NAP', 'napa' => 'NAPA', 'nbsap' => 'NBSAP', 'nip' => 'NIP', 'nwp' => 'NWP', 'cepa' => 'CEPA');
        }

        function validate_national_report_add() {
            $this->actioned = TRUE;
            $val = new FormValidator();
            $val->addValidation("id_treaty", "req", "Please select treaty");
            $val->addValidation("id_country", "req", "Please select country");
            $val->addValidation("title", "req", "Please enter the title");
            $val->addValidation("submission", "req", "Please enter type of report");
            $val->addValidation("document_url", "req", "Please enter document URL");
            $valid = $val->ValidateForm();
            if (!$valid) {
                $this->errors = $val->GetErrors();
            }
            if (!check_admin_referer('add_national_report')) {
                $valid = false;
                $this->errors['security'] = 'Invalid security token';
            }
            return $valid;
        }


        function national_report_add() {
            global $wpdb;
            global $current_user;
            $this->actioned = TRUE;
            $user = $current_user->user_login;

            $rec_created = date('Y-m-d H:i:s', strtotime("now"));
            $id_country = get_request_int('id_country');
            $id_event = get_request_int('id_event');
            $title = stripslashes(get_request_value('title'));
            @mysql_query("BEGIN", $wpdb->dbh);
            try {
                // Find next autoincrement value
                $next_id = $wpdb->get_row("SHOW TABLE STATUS LIKE 'ai_country_report'");
                $data = array(
                    'original_id' => 'manual-backend-' . $next_id->Auto_increment,
                    'id_treaty' => get_request_int('id_treaty'),
                    'id_country' => $id_country,
                    'title' => $title,
                    'submission' => stripslashes(get_request_value('submission')),
                    'document_url' => stripslashes(get_request_value('document_url')),
                    'rec_created' => $rec_created,
                    'rec_author' => $user
                );
                if (!empty($id_event)) {
                    $data['id_event'] = $id_event;
                }

                $success = $wpdb->insert('ai_country_report', $data);
                @mysql_query("COMMIT", $wpdb->dbh);
                $this->success = true;
                // Log the action
                $country = $this->get_country_for_id($id_country);
                $url = sprintf('%s/countries/%d/reports', get_bloginfo('url'), $id_country);
                $this->add_activity_log('insert', 'national report', "Created new national report for {$country->name} - $title", null, $url);
            } catch (Exception $e) {
                $this->success = FALSE;
                @mysql_query("ROLLBACK", $wpdb->dbh);
            }
        }


        function validate_national_report_edit() {
            $this->actioned = TRUE;
            $val = new FormValidator();
            $val->addValidation("id_national_report", "req", "Please select the national report to edit");
            $val->addValidation("id_treaty", "req", "Please select treaty");
            $val->addValidation("id_country", "req", "Please select country");
            $val->addValidation("title", "req", "Please enter the title");
            $val->addValidation("submission", "req", "Please enter type of report");
            $val->addValidation("document_url", "req", "Please enter document URL");
            $valid = $val->ValidateForm();
            if (!$valid) {
                $this->errors = $val->GetErrors();
            }
            if (!check_admin_referer('edit_national_report')) {
                $valid = false;
                $this->errors['security'] = 'Invalid security token';
            }
            return $valid;
        }


        function national_report_edit() {
            global $wpdb;
            global $current_user;
            $this->actioned = TRUE;
            $user = $current_user->user_login;

            $rec_created = date('Y-m-d H:i:s', strtotime("now"));
            $id_national_report = get_request_int('id_national_report');
            $id_country = get_request_int('id_country');
            $id_event = get_request_int('id_event');
            $title = stripslashes(get_request_value('title'));
            @mysql_query("BEGIN", $wpdb->dbh);
            try {
                $data = array(
                    'id_treaty' => get_request_int('id_treaty'),
                    'id_country' => $id_country,
                    'title' => $title,
                    'submission' => stripslashes(get_request_value('submission')),
                    'document_url' => stripslashes(get_request_value('document_url')),
                    'rec_created' => $rec_created,
                    'rec_author' => $user
                );
                if (!empty($id_event)) {
                    $data['id_event'] = $id_event;
                }
                // Find next autoincrement value
                $next_id = $wpdb->get_row("SHOW TABLE STATUS LIKE 'ai_country_report'");
                $success = $wpdb->update('ai_country_report', $data, array('id' => $id_national_report));
                @mysql_query("COMMIT", $wpdb->dbh);
                $this->success = true;
                // Log the action
                $country = $this->get_country_for_id($id_country);
                $url = sprintf('%s/countries/%d/reports', get_bloginfo('url'), $id_country);
                $this->add_activity_log('update', 'national report', "Updated national report for {$country->name} - $title", null, $url);
            } catch (Exception $e) {
                $this->success = FALSE;
                @mysql_query("ROLLBACK", $wpdb->dbh);
            }
        }
    }
}
?>
