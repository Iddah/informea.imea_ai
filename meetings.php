<?php
/**
 * This class is the data provider for the 'meetings' section of the site.
 * @package InforMEA
 * @subpackage Theme
 * @since 1.0
 */

add_action('wp_ajax_nopriv_load_meetings', array('imea_meetings_page', 'ajax_load_meetings'));
add_action('wp_ajax_load_meetings', array('imea_meetings_page', 'ajax_load_meetings'));


if (!class_exists('imea_meetings_page')) {
    class imea_meetings_page extends imea_page_base_page {

        /**
         * Retrieve the list of conventions that have meetings
         * @return array list of ai_treaty objects
         */
        public static function get_treaties() {
            global $wpdb;
            $sql = "SELECT a.* FROM ai_treaty a
                INNER JOIN ai_event b ON b.id_treaty = a.id
                WHERE a.enabled = 1 OR short_title='UNEP' GROUP BY a.id
            ";
            $rows = $wpdb->get_results($sql);
            $ret = array();
            foreach ($rows as $row) {
                $ret[$row->id] = $row;
            }
            return $ret;
        }


        function empty_search() {
            return get_request_value('filter') === NULL && get_request_value('start') === NULL && get_request_value('end') === NULL;
        }


        static function get_years_interval() {
            global $wpdb;
            $row = $wpdb->get_row("SELECT MIN(YEAR(start)) AS min_year, MAX(YEAR(start)) AS max_year FROM ai_event");
            $min = intval($row->min_year);
            $max = intval($row->max_year);
            return array_reverse(range($min, $max));
        }


        /**
         * Retrieve the COP meetings
         *
         * @param integer $id_treaty ID of the treaty to retrieve data
         * @return array List of meetings with most recent first
         */
        function get_cop_meetings($id_treaty) {
            global $wpdb;
            $sql = $wpdb->prepare('SELECT a.* FROM ai_event a WHERE id_treaty=%d AND type=%s ORDER BY start DESC', $id_treaty, 'cop');
            return $wpdb->get_results($sql);
        }


        public static function decode_kind($e) {
            if(empty($e->kind)) {
                return '';
            }
            switch ($e->event) {
                case 'official':
                    return __('Official', 'informea');
                case 'partner':
                    return __('Partner', 'informea');
                case 'interest':
                    return __('Interest', 'informea');
                default:
                    return ucfirst($e->kind);
            }
        }

        function decode_status($status) {
            switch ($status) {
                case 'tentative':
                    return __('Tentative', 'informea');
                case 'confirmed':
                    return __('Confirmed', 'informea');
                case 'postponed':
                    return __('Postponed', 'informea');
                case 'cancelled':
                    return __('Cancelled', 'informea');
                case 'nodate':
                    return __('No date', 'informea');
                default:
                    return '';
            }
        }


        public static function event_place($event) {
            $ret = '';
            if (!empty($event)) {
                if (!empty($event->location)) {
                    $ret = $event->location;
                }
                if (!empty($event->city)) {
                    if (!empty($event->location)) {
                        $ret .= ', ';
                    }
                    $ret .= $event->city;
                }
            }
            return $ret;
        }


        /**
         * @todo not used, can be removed. Left in case we need it again
         * Make it more flexible
         */
        function get_upcoming_meetings($limit = 5) {
            global $wpdb;
            $sql = "SELECT a.*, b.short_title, b.logo_medium FROM ai_event a
                    INNER JOIN ai_treaty b ON a.id_treaty = b.id
                WHERE a.start > CURRENT_DATE() AND a.start < ADDDATE(CURRENT_DATE(), INTERVAL 90 DAY)
                ORDER BY a.start ASC LIMIT $limit";
            $ret = array();
            $rows = $wpdb->get_results($sql);
            foreach ($rows as $row) {
                $ob = new StdClass();
                $ob->title = $row->title;
                $ob->event_url = $row->event_url;
                $ob->logo_medium = $row->logo_medium;

                $ob->interval = show_event_interval($row);

                $city = '';
                if ($row->city !== NULL) {
                    $city = $row->city;
                }
                $ob->city = $city;

                $ret[] = $ob;
            }
            return $ret;
        }

        static function get_months_indexed() {
            return array(1 => __('Jan', 'informea'), 2 => __('Feb', 'informea'), 3 => __('Mar', 'informea'), 4 => __('Apr', 'informea'),
                5 => __('May', 'informea'), 6 => __('Jun', 'informea'), 7 => __('Jul', 'informea'), 8 => __('Aug', 'informea'), 9 => __('Sep', 'informea'),
                10 => __('Oct', 'informea'), 11 => __('Nov', 'informea'), 12 => __('Dec', 'informea'));
        }

        static function get_months_fullname() {
            return array(1 => __('January', 'informea'), 2 => __('February', 'informea'), 3 => __('March', 'informea'), 4 => __('April', 'informea'),
                5 => __('May', 'informea'), 6 => __('June', 'informea'), 7 => __('July', 'informea'), 8 => __('August', 'informea'), 9 => __('September', 'informea'),
                10 => __('October', 'informea'), 11 => __('November', 'informea'), 12 => __('December', 'informea'));
        }


        function link_options_bar_list_view($additional_css_classes = '') {
            $p = $this;
            $expand = get_request_value('expand', 'list');
            echo get_imea_anchor(array('title' => __('List view', 'informea'),
                'href' => 'meetings/' . $expand,
                'css_cb' => function () use ($p, $additional_css_classes, $expand) {
                    $ret = $additional_css_classes;
                    if ($expand == 'icon') {
                        $ret .= ' disabled';
                    }
                    return $ret;
                }));
        }

        function link_options_bar_calendar_view($additional_css_classes = '') {
            $p = $this;
            $expand = get_request_value('expand', 'calendar');
            echo get_imea_anchor(array('title' => __('Calendar view', 'informea'),
                'href' => 'meetings/' . $expand,
                'css_cb' => function () use ($p, $additional_css_classes, $expand) {
                    $ret = $additional_css_classes;
                    if ($expand == 'icon') {
                        $ret .= ' disabled';
                    }
                    return $ret;
                }));
        }

//****************
        /**
         * Access ai_treaty
         * @return a row from the table
         */
        function get_event($id_event) {
            global $wpdb;
            return $wpdb->get_row($wpdb->prepare('SELECT * FROM ai_event  WHERE id = %d', intval($id_event)));
        }

        /**
         * Access ai_treaty
         * @return a row from the table
         */
        function get_treaty($id_treaty) {
            global $wpdb;
            return $wpdb->get_row($wpdb->prepare("SELECT * FROM ai_treaty WHERE id = %d", intval($id_treaty)));
        }

        /**
         * Access voc_concept
         * @return Rows from the table
         * @todo replace with Thesaurus.get_voc_concept_informea
         */
        function get_voc_concept() {
            global $wpdb;
            return $wpdb->get_results("SELECT * FROM voc_concept WHERE id_source = 9 ORDER BY term");
        }

        /**
         * Access ai_treaty
         * @return Rows from the table
         */
        function get_all_treaties() {
            global $wpdb;
            return $wpdb->get_results("SELECT * FROM ai_treaty ORDER BY short_title");
        }

        /**
         * Get enabled treaties from ai_treaty
         * @return Rows from the table
         */
        function get_enabled_treaties() {
            global $wpdb;
            return $wpdb->get_results("SELECT * FROM ai_treaty WHERE enabled = 1 ORDER BY short_title");
        }

        /**
         * @todo replace with Thesaurus.get_voc_concept_by_id ...
         */
        function get_term($id) {
            global $wpdb;
            return $wpdb->get_row($wpdb->prepare('SELECT * FROM voc_concept WHERE id = %d', intval($id)));
        }


        /**
         * Access voc_concept
         * @return Rows from the table
         */
        function get_event_concept() {
            global $wpdb;
            return $wpdb->get_results("SELECT * FROM ai_event ORDER BY title");
        }


        /**
         * Access ai_country
         * @return Rows from the table
         */
        function get_countries() {
            global $wpdb;
            return $wpdb->get_results("SELECT * FROM ai_country ORDER BY name");
        }

        /**
         * Get
         * @return Rows from the table
         */
        function get_meetings($id_treaty, $order_by = 'a.`title`') {
            global $wpdb;
            $sql = $wpdb->prepare("SELECT a.* FROM ai_event a WHERE a.`id_treaty` = %d ORDER BY $order_by", $id_treaty);
            return $wpdb->get_results($sql);
        }


        function list_meetings_admin() {
            global $wpdb;
            $sql = $wpdb->prepare(
                "SELECT a.*, b.short_title, c.name FROM ai_event a
                    LEFT JOIN ai_treaty b ON a.id_treaty = b.id
                    LEFT JOIN ai_organization c ON a.id_organization = c.id
                    ORDER BY c.name, b.short_title, a.title");
            return $wpdb->get_results($sql);
        }

        /**
         * Retrieve COP meetings
         *
         * @global object $wpdb WordPress database
         * @param integer $id_treaty Treaty ID
         * @return array Meetings as stdClass
         */
        function get_meetings_cop($id_treaty) {
            global $wpdb;
            $sql = $wpdb->prepare("SELECT a.* FROM ai_event a WHERE a.`id_treaty` = %d AND a.`type` = 'cop' ORDER BY a.`start` DESC", $id_treaty);
            return $wpdb->get_results($sql);
        }

        //****************

        /* @param date is in MySQL format: YYYYMMDD */
        function get_day_meetings($date) {
            global $wpdb;
            $sql = "SELECT a.*, b.short_title, b.logo_medium FROM ai_event a
                    INNER JOIN ai_treaty b ON a.id_treaty = b.id
                WHERE a.start = '$date' ORDER BY b.short_title ASC";
            return $wpdb->get_results($sql);
        }

        function get_day_running_meetings($date) {
            global $wpdb;
            $sql = "SELECT a.*, b.short_title, b.logo_medium FROM ai_event a
                    INNER JOIN ai_treaty b ON a.id_treaty = b.id
                WHERE a.start <= '$date' AND a.end >= '$date' ORDER BY b.short_title ASC";
            return $wpdb->get_results($sql);
        }


        function ajax_load_meetings() {
            $year = get_request_value('year');
            $month = get_request_value('month');
            $day = get_request_value('day');
            $d = $year . sprintf('%02d', $month) . sprintf('%02d', $day);
            $page_data = new imea_meetings_page();
            $meetings = $page_data->get_day_running_meetings($d);
            foreach ($meetings as &$event) {
                $event->interval = show_event_interval($event);
            }
            header('Content-Type:application/json');
            echo json_encode($meetings);
            die();
        }


        function get_repetition_enum() {
            return array('weekly' => 'Weekly', 'monthly' => 'Monthly', 'yearly' => 'Yearly');
        }

        function get_kind_enum() {
            return array('official' => 'MEA official event', 'partner' => 'MEA partner event', 'interest' => 'Interest event');
        }

        function get_type_enum() {
            return array('cop' => 'COP', 'subsidiary' => 'Subsidiary', 'expert' => 'Expert', 'working' => 'Working',
                'symposia' => 'Symposia', 'conference' => 'Conference', 'workshop' => 'Workshop', 'informal' => 'Informal');
        }

        function get_access_enum() {
            return array('public' => 'Public', 'invitation' => 'Invitation');
        }

        function get_status_enum() {
            return array('tentative' => 'Tentative', 'confirmed' => 'Confirmed', 'postponed' => 'Postponed',
                'cancelled' => 'Cancelled', 'nodate' => 'No date');
        }

        /************          Administrative functions   ************************/
        /**
         * Validate the validate_event_aedit_event form
         * @return TRUE If form successfully validated
         */
        function validate_event_edit_event() {
            $this->actioned = TRUE;
            if (check_admin_referer('informea-admin_event_edit_event')) {
                $val = new FormValidator();
                $val->addValidation("id_treaty", "req", "Please select the treaty"); // TODO validate id_treaty integer > 0
                $val->addValidation("title", "req", "Please fill in the title");
                $val->addValidation("start", "req", "Please enter start date"); // TODO validat start it's a date
                $val->addValidation("id_country", "req", "Please pick a country");
                $valid = $val->ValidateForm();
                if (!$valid) {
                    $this->errors = $val->GetErrors();
                }
                return $valid;
            }
            return FALSE;
        }

        /**
         * Insert new event into the database
         * @return TRUE if successfully added
         */
        function event_edit_event() {
            global $wpdb;
            global $current_user;
            $user = $current_user->user_login;
            $treaties = new imea_treaties_page();
            try {
                $id_event = get_request_int('id_event');
                $treaty = $treaties->get_treaty_by_id(get_request_int('id_treaty'));
                $title = stripslashes(get_request_value('title'));
                $data['id_treaty'] = $treaty->id;
                $data['id_organization'] = get_request_int('id_organization');
                $data['event_url'] = stripslashes(get_request_value('event_url'));
                $data['title'] = $title;
                $data['description'] = stripslashes(get_request_value('description'));
                $data['start'] = get_request_value('start');
                $data['end'] = get_request_value('end');
                $data['repetition'] = get_request_value('repetition');
                $data['kind'] = get_request_value('kind');
                $data['type'] = get_request_value('type');
                $data['access'] = get_request_value('access');
                $data['status'] = get_request_value('status');
                $data['image_copyright'] = get_request_value('image_copyright');
                $data['location'] = get_request_value('location');
                $data['city'] = get_request_value('city');
                $data['id_country'] = get_request_int('id_country');
                $data['image'] = get_request_value('image');
                $data['rec_author'] = $user;
                $data['rec_created'] = date('Y-m-d H:i:s', strtotime("now"));
                $this->success = $wpdb->update('ai_event', $data, array('id' => $id_event));
                if ($this->success) {
                    // Log the action
                    $this->add_activity_log('update', 'meetings', "Updated event - $title ");
                } else {
                    $this->success = FALSE;
                    $this->errors = array('DB' => $wpdb->last_error);
                }
            } catch (Exception $e) {
            }
        }

        /************          Administrative functions   ************************/
        /**
         * Validate the validate_event_add_event form
         * @return TRUE If form successfully validated
         */
        function validate_event_add_event() {
            $this->actioned = TRUE;
            if (check_admin_referer('informea-admin_event_add_event')) {
                $val = new FormValidator();
                $val->addValidation("title", "req", "Please fill in the title");
                $val->addValidation("start", "req", "Please enter start date"); // TODO validat start it's a date
                $val->addValidation("id_country", "req", "Please pick a country");
                $valid = $val->ValidateForm();
                if (!$valid) {
                    $this->errors = $val->GetErrors();
                }
                $id_treaty = get_request_int('id_treaty');
                $id_organization = get_request_int('id_organization');
                if (empty($id_treaty) && empty($id_organization)) {
                    $valid = FALSE;
                    $this->errors['id_treaty'] = 'Please select either organization, treaty or both';
                }
                return $valid;
            }
            return FALSE;
        }

        function can_delete($event) {
            global $wpdb;
            return $wpdb->get_var($wpdb->prepare('SELECT COUNT(*) FROM
                (
                    SELECT id_meeting AS id_event FROM ai_decision a WHERE `id_meeting` IS NOT NULL
                    UNION
                    SELECT id_event FROM ai_country_plan a WHERE `id_event` IS NOT NULL
                    UNION
                    SELECT id_event FROM ai_country_report a WHERE `id_event` IS NOT NULL
                ) c WHERE c.`id_event` =%d', $event->id)) == '0';
        }

        function delete_event() {
            global $wpdb;
            global $current_user;
            $user = $current_user->user_login;
            $id_event = get_request_value('id_event');
            @mysql_query("BEGIN", $wpdb->dbh);
            try {
                $event = $this->get_event($id_event);
                $wpdb->query($wpdb->prepare("DELETE FROM ai_event WHERE id = %d", $id_event));
                $this->add_activity_log('delete', 'event', "Removed event with ID: {$event->id} - '{$event->title}'", null, null);
                @mysql_query("COMMIT", $wpdb->dbh);
                $this->success = true;
            } catch (Exception $e) {
                $this->success = FALSE;
                @mysql_query("ROLLBACK", $wpdb->dbh);
            }
        }

        /**
         * Insert new event into the database
         * @return TRUE if successfully added
         */
        function event_add_event() {
            global $wpdb;
            global $current_user;
            $user = $current_user->user_login;
            $treaties = new imea_treaties_page();
            try {
                $treaty = $treaties->get_treaty_by_id(get_request_int('id_treaty'));
                $title = stripslashes(get_request_value('title'));
                $data['id_treaty'] = $treaty->id;
                $data['id_organization'] = get_request_int('id_organization');
                # $data['original_id'] = NULL; // meetings created manually have original_id set to NULL
                $data['event_url'] = stripslashes(get_request_value('event_url'));
                $data['title'] = $title;
                $data['description'] = stripslashes(get_request_value('description'));
                $data['start'] = get_request_value('start');
                $data['end'] = get_request_value('end');
                $data['repetition'] = get_request_value('repetition');
                $data['kind'] = get_request_value('kind');
                $data['type'] = get_request_value('type');
                $data['access'] = get_request_value('access');
                $data['status'] = get_request_value('status');
                $data['image'] = get_request_value('image');
                $data['image_copyright'] = get_request_value('image_copyright');
                $data['location'] = get_request_value('location');
                $data['city'] = get_request_value('city');
                $data['id_country'] = get_request_int('id_country');
                $data['image'] = get_request_value('image');
                $data['rec_author'] = $user;
                $data['rec_created'] = date('Y-m-d H:i:s', strtotime("now"));
                $this->success = $wpdb->insert('ai_event', $data);

                if ($this->success) {
                    // Log the action
                    $this->add_activity_log('insert', 'meetings', "Created new event - $title ");
                } else {
                    $this->success = FALSE;
                    $this->errors = array('DB' => $wpdb->last_error);
                }
            } catch (Exception $e) {
            }
        }

        function get_treaties_with_meetings() {
            global $wpdb;
            $sql = "SELECT a.* FROM ai_treaty a INNER JOIN ai_event b ON a.`id` = b.`id_treaty` WHERE a.enabled = 1 OR a.short_title='UNEP' GROUP BY a.`id` ORDER BY a.short_title";
            return $wpdb->get_results($sql);
        }
    }
}
?>
