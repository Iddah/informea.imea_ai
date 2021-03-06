<?php
/**
 * This class is the data provider for the 'Decisions' section of the site.
 * @package InforMEA
 * @subpackage Theme
 * @since 1.0
 */

add_action('wp_ajax_tag_decision_paragraph', array('imea_decisions_page', 'ajax_tag_decision_paragraph'));
add_action('wp_ajax_delete_decision_paragraph_ajaxurl', array('imea_decisions_page', 'delete_decision_paragraph_ajaxurl'));
add_action('wp_ajax_order_decisions', array('imea_decisions_page', 'ajax_order_decisions'));

add_action('wp_ajax_nopriv_get_cop_meetings', 'ajax_informea_get_cop_meetings');
add_action('wp_ajax_get_cop_meetings', 'ajax_informea_get_cop_meetings');

add_action('wp_ajax_nopriv_get_decisions', 'ajax_informea_get_decisions');
add_action('wp_ajax_get_decisions', 'ajax_informea_get_decisions');

add_action('wp_ajax_nopriv_get_decision', 'ajax_informea_get_decision');
add_action('wp_ajax_get_decision', 'ajax_informea_get_decision');


/**
 * Ajax function to retrieve the list of decisions.
 * @param string $odata_name Treaty OData name. (use get_treaties()[0]->odata_name)
 */
function ajax_informea_get_cop_meetings() {
    $treaty = null;
    $ret = array();

    $obTreaty = new imea_treaties_page();
    $obDec = new imea_decisions_page();

    $odata_name = get_request_value('odata_name');
    $treaties = $obTreaty->get_all_treaties();
    foreach ($treaties as $row) {
        if ($row->odata_name == $odata_name) {
            $treaty = $row;
            break;
        }
    }
    if (empty($treaty)) {
        header('Content-Type:application/json');
        echo '{ "error" : 1, "usage" : "Incorrect usage. Unknown value for odata_name. You can retrieve the list of treaties using /wp-admin/admin-ajax.php?action=get_treaties" }';
        die();
    } else {
        $ob = new imea_meetings_page();
        $meetings = $ob->get_cop_meetings($treaty->id);
        foreach ($meetings as $meeting) {
            $copy = stdclass_copy($meeting,
                array(
                    'id', 'id_treaty', 'event_url', 'title', 'description', 'start', 'end',
                    'repetition', 'kind', 'type', 'access', 'status', 'image', 'image_copyright',
                    'location', 'city', 'latitude', 'longitude', 'id_country'
                )
            );
            $copy->decisions_count = $obDec->get_meeting_decision_count($meeting->id);
            if (isset($meeting->id_country)) {
                $country = new imea_countries_page($meeting->id_country);
                $copy->country = $country->country->name;
            }
            $ret[] = $copy;
        }
    }

    // var_dump($ret);
    header('Content-Type:application/json');
    echo json_encode($ret);
    die();
}


/**
 * Ajax function to retrieve the list of decisions for a COP meeting
 * @param string $odata_name OData name for the meeting. For CITES, $id_meeting is optional
 * @param integer $id_meeting ID of the meeting
 */
function ajax_informea_get_decisions() {
    $odata_name = get_request_value('odata_name');
    $id_meeting = get_request_int('id_meeting');
    $decisions = array();
    $ret = array();

    $ob = new imea_treaties_page();
    $treaties = $ob->get_all_treaties();
    foreach ($treaties as $row) {
        if ($row->odata_name == $odata_name) {
            $treaty = $row;
            break;
        }
    }
    if (empty($treaty) || ($id_meeting == 0 && strtolower($odata_name) != 'cites')) {
        header('Content-Type:application/json');
        echo '{ "error" : 1, "usage" : "Incorrect usage. Unknown value for odata_name. You can retrieve the list of treaties using /wp-admin/admin-ajax.php?action=get_treaties" }';
        die();
    } else {
        $ob = new imea_decisions_page();
        if (strtolower($odata_name) == 'cites') {
            $decisions = $ob->get_decisions_for_treaty($treaty->id, 'a.display_order');
        } else {
            $decisions = $ob->get_decisions_for_meeting($id_meeting);
        }
    }

    foreach ($decisions as $decision) {
        $copy = stdclass_copy($decision, array(
            'id', 'link', 'short_title', 'type', 'status', 'number', 'id_treaty', 'published', 'id_meeting'
        ));
        $ret[] = $copy;
    }

    header('Content-Type:application/json');
    echo json_encode($ret);
    die();
}


/**
 * Ajax function to retrieve the details for a decision
 * @param integer $id Decision id
 */
function ajax_informea_get_decision() {
    $id = get_request_int('id');
    $ret = NULL;
    if (empty($id)) {
        header('Content-Type:application/json');
        echo '{ "error" : 1, "usage" : "Incorrect usage. Unknown decision id" }';
        die();
    } else {
        $ob = new imea_decisions_page();
        $decision = $ob->get_decision($id);
        if (empty($decision)) {
            header('Content-Type:application/json');
            echo '{ "error" : 1, "usage" : "Unknown decision" }';
            die();
        }
        $copy = stdclass_copy($decision, array(
            'id', 'link', 'short_title', 'type', 'status', 'number', 'id_treaty', 'published', 'id_meeting'
        ));
        $copy->documents = array();

        $documents = $ob->get_decision_documents($decision->id);
        foreach ($documents as $doc) {
            $dc = stdclass_copy($doc, array(
                'id', 'mime', 'id_decision', 'size', 'filename'

            ));
            $dc->url = get_bloginfo('url') . '/download?entity=decision_document&id=' . $doc->id;
            $copy->documents[] = $dc;
        }
        $ret = $copy;
    }
    header('Content-Type:application/json');
    echo json_encode($ret);
    die();
}


if (!class_exists('imea_decisions_page')) {
    class imea_decisions_page extends imea_page_base_page {

        public $expand = NULL;

        function __construct($arr_parameters = array()) {
            parent::__construct($arr_parameters);
            $this->expand = get_request_variable('expand', 'str', 'treaty'); // or term
        }

        /**
         * Retrieve the list of treaties
         */
        function get_treaties_list() {
            global $wpdb;
            $ret = array();
            // Get the themes
            $sql = "SELECT DISTINCT a.theme FROM ai_treaty a INNER JOIN ai_decision b ON b.id_treaty = a.id WHERE a.enabled = 1 ORDER BY a.theme";
            $rows = $wpdb->get_results($sql);
            foreach ($rows as $row) {
                $ret[$row->theme] = array();
            }

            $sql = "SELECT a.*, a.logo_medium, a.theme FROM ai_treaty a INNER JOIN ai_decision c ON c.id_treaty = a.id WHERE enabled = 1 GROUP BY a.id ORDER BY a.order";
            $rows = $wpdb->get_results($sql);
            foreach ($rows as $row) {
                $ret[$row->theme][] = $row;
            }
            return $ret;
        }

        function get_meeting_by_decision($decision) {
            if (!empty($decision->id_meeting)) {
                global $wpdb;
                return $wpdb->get_row($wpdb->prepare('SELECT * FROM ai_event WHERE id=%d', $decision->id_meeting));
            }
            return FALSE;
        }

        /**
         * Retrieve the list of terms that are linked to decisions
         */
        function get_terms_for_decisions() {
            $ret = array();
            global $wpdb;
            $sql = "SELECT a.* FROM voc_concept a
                    INNER JOIN ai_decision_paragraph_vocabulary b ON a.id = b.id_concept
                UNION
                    SELECT a.* FROM voc_concept a
                        INNER JOIN ai_decision_vocabulary b ON a.id = b.id_concept
                 ORDER BY term";
            $terms = $wpdb->get_results($sql);
            foreach ($terms as $row) {
                $letter = strtoupper(substr($row->term, 0, 1));
                if (!isset($ret[$letter])) {
                    $ret[$letter] = array();
                }
                $ret[$letter][] = $row;
            }
            return $ret;
        }

        /**
         * Return the list of themes from the vocabulary
         */
        function get_themes() {
            global $wpdb;
            return $wpdb->get_results('SELECT * FROM voc_concept WHERE id_source = 9 AND top_concept = 1 ORDER BY term');
        }

        function expand_theme_terms($id_theme) {
            global $wpdb;
            // Retrieve full sub-tree of narrower terms
            $arr = array($id_theme);
            $rows = $this->_rec_get_subterms($arr);
            $p = implode(',', $rows);
            if ($p) {
                $ret = $wpdb->get_results("SELECT a.* FROM voc_concept a INNER JOIN view_terms_decisions b ON a.id = b.id WHERE a.id_source = 9 AND a.id IN ($p) ORDER BY a.term");
                return $ret;
            }
            return array();
        }

        function _rec_get_subterms(&$root_nodes) {
            if (count($root_nodes)) {
                global $wpdb;
                $p = implode(',', $root_nodes);
                $sql = "SELECT target_term FROM voc_relation WHERE id_concept IN ($p) AND `relation` = 2";
                $rows = $wpdb->get_results($sql);
                $ret = array();
                foreach ($rows as $row) {
                    $ret[] = $row->target_term;
                }
                $nrows = $this->_rec_get_subterms($ret);
                foreach ($rows as $row) {
                    $nrows[] = $row->target_term;
                }
                if (count($nrows)) {
                    $ret = array_merge($ret, $nrows);
                }
                return array_unique($ret);
            }
        }


        /**
         * Retrieve the count of decisions for a meeting
         *
         * @global object $wpdb WordPress db connection
         * @param integer $id_meeting Meeting ID from ai_event
         * @return integer Number of decisions for the given meeting
         */
        function get_meeting_decision_count($id_meeting) {
            global $wpdb;
            $ret = $wpdb->get_var(
                $wpdb->prepare('SELECT COUNT(*) FROM ai_decision WHERE id_meeting=%d', $id_meeting)
            );
            return $ret + 0;
        }


        function document_icon_img($doc) {
            $ret = '';
            $img_url = '';
            if ($doc->mime == 'doc' || $doc->mime == 'application/msword') {
                $img_url = '<img class="middle" src="' . get_bloginfo('template_directory') . '/images/doc.png" /> ';
            } else {
                if ($doc->mime == 'pdf' || $doc->mime == 'application/pdf' || $doc->mime == 'application/x-pdf') {
                    $img_url = '<img class="middle" src="' . get_bloginfo('template_directory') . '/images/pdf.png" /> ';
                }
            }
            if (!empty($doc->url)) {
                $ret .= '<a target="_blank" href="' . $doc->url . '">' . $img_url . $doc->filename . '</a>';
            } else {
                $ret .= $doc->filename;
            }
            return $ret;
        }

        /** !!!!!!!!!!!!!!!!!!!!!! ADMINISTRATION AREA SPECIFIC !!!!!!!!!!!!!!!!!!!!!! */
        function get_treaties_w_decisions() {
            global $wpdb;
            return $wpdb->get_results("SELECT a.* FROM ai_treaty a INNER JOIN ai_decision b ON b.id_treaty = a.id WHERE a.enabled = 1 GROUP BY a.id ORDER BY a.short_title");
        }

        function get_decisions_for_treaty($id_treaty, $order_by = 'a.`display_order`, a.published DESC') {
            if ($id_treaty) {
                global $wpdb;
                return $wpdb->get_results(
                    $wpdb->prepare(
                        "SELECT a.*, b.title AS cop_title FROM ai_decision a
                        LEFT JOIN ai_event b ON a.id_meeting = b.id
                        WHERE a.id_treaty = %s AND a.status <> 'retired'
                        GROUP BY a.id ORDER BY $order_by", $id_treaty
                    )
                );
            }
            return array();
        }


        function get_decisions_for_meeting($id_meeting) {
            global $wpdb;
            return $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT a.* FROM ai_decision a WHERE a.id_meeting=%s AND a.status <> 'retired' GROUP BY a.id ORDER BY display_order", $id_meeting
                )
            );
        }

        function get_decisions_for_treaty_meeting($id_treaty, $order_by = 'a.number, a.published DESC') {
            if ($id_treaty) {
                global $wpdb;
                return $wpdb->get_results(
                    $wpdb->prepare("SELECT a.*, b.title AS ob_meeting_title FROM ai_decision a
                    LEFT JOIN ai_event b ON a.id_meeting = b.id
                    WHERE a.id_treaty = %d AND a.status <> 'retired'
                        GROUP BY a.id ORDER BY %s", $id_treaty, $order_by));
            }
            return array();
        }


        /**
         * Retrieve the decisions associated with a country (i.e. Ecolex decisions)
         * @param integer $id_country Country ID
         * @param string $type (Optional) Type of decision - ai_decision.type
         * @return array Array of decision objects
         */
        function get_decisions_for_country($id_country, $type = NULL) {
            global $wpdb;

            if (empty($type)) {
                return $wpdb->get_results($wpdb->prepare(
                    "SELECT a.* FROM ai_decision a
                        INNER JOIN ai_decision_country b ON a.id = b.id_decision
                        WHERE b.id_country=%d AND a.status <> 'retired''
                        ORDER BY a.display_order", $id_country));
            } else {
                return $wpdb->get_results($wpdb->prepare(
                    "SELECT a.* FROM ai_decision a
                        INNER JOIN ai_decision_country b ON a.id = b.id_decision
                        WHERE b.id_country=%d AND a.`type`=%s AND a.status <> 'retired'
                        ORDER BY a.display_order", $id_country, $type));
            }
        }


        /**
         * Retrieve the number of decisions associated with a country (i.e. Ecolex decisions)
         * @param integer $id_country Country ID
         * @param string $type (Optional) Type of decision - ai_decision.type
         * @return integer Number of decisions
         */
        function count_decisions_for_country($id_country, $type = NULL) {
            global $wpdb;

            if (empty($type)) {
                return $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM ai_decision a
                        INNER JOIN ai_decision_country b ON a.id = b.id_decision
                        WHERE b.id_country=%d
                        ORDER BY a.display_order", $id_country));
            } else {
                return $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM ai_decision a
                        INNER JOIN ai_decision_country b ON a.id = b.id_decision
                        WHERE b.id_country=%d AND a.`type`=%s AND a.status <> 'retired'
                        ORDER BY a.display_order", $id_country, $type));
            }
        }


        function get_decision($id_decision) {
            if ($id_decision) {
                global $wpdb;
                return $wpdb->get_row(
                    $wpdb->prepare(
                        "SELECT a.*, b.title AS `event_title`
                            FROM `ai_decision` a
                            LEFT JOIN ai_event b ON a.id_meeting = b.id
                            LEFT JOIN ai_treaty c ON a.id_treaty = c.id
                            WHERE a.id = %d
                        ",
                        $id_decision
                    )
                );
            }
        }

        function get_decision_documents($id_decision) {
            if ($id_decision) {
                global $wpdb;
                return $wpdb->get_results(
                    $wpdb->prepare(
                        "SELECT a.*
                            FROM `ai_document` a
                            WHERE a.id_decision = %d
                            ORDER BY `filename`
                        ",
                        $id_decision
                    )
                );
            }
        }

        function get_decision_paragraphs($id_decision) {
            $ret = array();
            if ($id_decision) {
                global $wpdb;
                $rows = $wpdb->get_results(
                    $wpdb->prepare(
                        "SELECT a.`content`, a.`id` AS `id_paragraph`, c.*
                            FROM `ai_decision_paragraph` a
                            INNER JOIN `ai_decision_paragraph_vocabulary` b ON b.`id_decision_paragraph` = a.`id`
                            INNER JOIN `voc_concept` c ON b.`id_concept` = c.`id`
                            WHERE a.id_decision = %d
                        ",
                        $id_decision
                    )
                );
                // var_dump($wpdb->last_query);
                foreach ($rows as $row) {
                    if (!isset($ret[$row->content])) {
                        $ret[$row->content] = array();
                    }
                    $ret[$row->content][] = $row;
                }
            }
            return $ret;
        }


        function get_paragraphs($id_decision) {
            global $wpdb;
            return $wpdb->get_results($wpdb->prepare('SELECT * FROM ai_decision_paragraph WHERE id_decision=%d', $id_decision));
        }

        function get_decision_content($decision, $documents) {
            $ret = null;
            foreach ($documents as $doc) {
                if ($doc->language == 'en') {
                    // var_dump($doc->id);
                    // Pull content from Solr
                    $s = $this->get_solr_document($doc->id);
                    if (!empty($s)) {
                        $ret = $s;
                    }
                    break;
                }
            }
            if ($ret == null) {
                // Try to pull content from Solr 'text' field
                $goptions = get_option('informea_options');
                $options = array(
                    'hostname' => $goptions['solr_server'],
                    'path' => $goptions['solr_path'],
                    'port' => $goptions['solr_port']
                );
                $solr = new SolrClient($options);
                $q = new SolrQuery("unique_id:\"{$decision->id} decision\"");
                $q->addField('text');
                $q_resp = $solr->query($q);
                $q_resp->setParseMode(SolrQueryResponse::PARSE_SOLR_DOC);
                $resp = $q_resp->getResponse();
                if (isset($resp->response->docs[0])) {
                    $sdoc = $resp->response->docs[0];
                    $s = $sdoc->offsetGet('text')->values[0];
                    $s = strip_tags($s);
                    if (!empty($s)) {
                        $ret = $s;
                    }
                }
            }

            if ($ret == null) {
                // Finally try to pull content from the decision->body sql field.
                if (!empty($decision->body)) {
                    $ret = strip_tags($decision->body);
                }
            }
            return $ret;
        }


        function get_decision_content_decorated($decision, $documents) {
            $content = $this->get_decision_content($decision, $documents);
            $paragraphs = $this->get_decision_paragraphs($decision->id);
            foreach ($paragraphs as $paragraph => $terms) {
                $tooltip = 'This paragraph was tagged with: ';
                $c = count($terms);
                $id_paragraph = $terms[0]->id_paragraph;
                foreach ($terms as $idx => $term) {
                    $tooltip .= $term->term;
                    if ($idx < $c - 1) {
                        $tooltip .= ', ';
                    }
                }
                $content = str_replace($paragraph, '<span class="tagged-paragraph tooltip" id="' . $id_paragraph . '" class="tooltip" title="' . esc_attr($tooltip) . '">' . $paragraph . '</span>', $content);
            }
            return $content;
        }


        function get_solr_document($id_document) {
            $ret = '';
            $goptions = get_option('informea_options');
            $options = array(
                'hostname' => $goptions['solr_server'],
                'path' => $goptions['solr_path'],
                'port' => $goptions['solr_port']
            );
            $solr = new SolrClient($options);
            $q = new SolrQuery("unique_id:\"$id_document decision_document\"");
            // $q->addFilterQuery();
            $q->addField('text');
            $q_resp = $solr->query($q);
            $q_resp->setParseMode(SolrQueryResponse::PARSE_SOLR_DOC);
            $resp = $q_resp->getResponse();
            if (isset($resp->response->docs[0])) {
                $sdoc = $resp->response->docs[0];
                $ret = $sdoc->offsetGet('text')->values[0];
                if (!empty($ret)) {
                    $ret = strip_tags($ret);
                }
            }
            return $ret;
        }


        function delete_decision_paragraph_ajaxurl() {
            global $wpdb;
            global $current_user;
            $ret = array('success' => false, 'message' => 'Unknown failure');

            $THIS = new imea_decisions_page();
            $THIS->security_check('delete_decision_tags');

            //
            $id_decision = get_request_int('id_decision');
            $id_paragraph = get_request_int('id_paragraph');
            if ($id_decision <= 0) {
                $ret['message'] = 'Invalid decision';
            } else {
                if ($id_paragraph <= 0) {
                    $ret['message'] = 'Invalid paragraph';
                } else {
                    // Success
                    $ret['message'] = 'Successful request received';
                    @mysql_query("BEGIN", $wpdb->dbh);
                    try {
                        $order = $wpdb->get_var($wpdb->prepare('SELECT MAX(`order`) + 1 FROM ai_decision_paragraph WHERE id_decision = %d', $id_decision));
                        $wpdb->query($wpdb->prepare('DELETE FROM ai_decision_paragraph_vocabulary WHERE id_decision_paragraph=%d', $id_paragraph));
                        $wpdb->query($wpdb->prepare('DELETE FROM ai_decision_paragraph WHERE id_decision=%d AND id=%d', $id_decision, $id_paragraph));
                        @mysql_query("COMMIT", $wpdb->dbh);
                        $ret['message'] = 'Paragraph was successful untagged';
                    } catch (Exception $e) {
                        $ret['message'] = 'Exception occurred during database operation';
                        @mysql_query("ROLLBACK", $wpdb->dbh);
                    }
                }
            }
            header('Content-Type:application/json');
            echo json_encode($ret);
            die();
        }


        function ajax_tag_decision_paragraph() {
            global $wpdb;
            global $current_user;
            $ret = array('success' => false, 'message' => 'Unknown failure');

            $THIS = new imea_decisions_page();
            check_ajax_referer('edit_decision_tags');

            $id_decision = get_request_int('id_decision');
            $paragraph = get_request_value('paragraph');
            $tags = get_request_value('tags', array(), false);
            if ($id_decision <= 0) {
                $ret['message'] = 'Invalid decision';
            } else {
                if (empty($paragraph)) {
                    $ret['message'] = 'Invalid paragraph';
                } else {
                    if (empty($tags)) {
                        $ret['message'] = 'No tag specified';
                    } else {
                        // Success
                        $ret['message'] = 'Successful request received';
                        $user = $current_user->user_login;
                        $rec_created = date('Y-m-d H:i:s', strtotime("now"));
                        @mysql_query("BEGIN", $wpdb->dbh);
                        try {
                            $order = $wpdb->get_var($wpdb->prepare('SELECT MAX(`order`) + 1 FROM ai_decision_paragraph WHERE id_decision = %d', $id_decision));
                            $success = $wpdb->insert('ai_decision_paragraph',
                                array(
                                    'id_decision' => $id_decision,
                                    'order' => $order,
                                    'content' => $paragraph,
                                    'rec_author' => $user,
                                    'rec_created' => $rec_created
                                )
                            );
                            $id_paragraph = $wpdb->insert_id;
                            foreach ($tags as $tag) {
                                $success = $wpdb->insert('ai_decision_paragraph_vocabulary',
                                    array(
                                        'id_decision_paragraph' => $id_paragraph,
                                        'id_concept' => $tag,
                                        'rec_author' => $user,
                                        'rec_created' => $rec_created
                                    )
                                );
                            }
                            @mysql_query("COMMIT", $wpdb->dbh);
                            $ret['message'] = 'Paragraph was successful tagged';

                            $decision = $THIS->get_decision($id_decision);
                            $url = sprintf('%s/treaties/%d/decisions/?showall=true#decision-%d', get_bloginfo('url'), $decision->id_treaty, $id_decision);
                            $THIS->add_activity_log('update', 'decision', "Tagged paragraph for decision '{$decision->number}'", null, $url);
                        } catch (Exception $e) {
                            $ret['message'] = 'Exception occurred during database operation';
                            @mysql_query("ROLLBACK", $wpdb->dbh);
                        }
                    }
                }
            }
            header('Content-Type:application/json');
            echo json_encode($ret);
            die();
        }


        /**
         * Access ai_decision joined with ai_treaty
         * @return Rows from the table
         */
        function get_decision_in_treaty_list($id_treaty) {
            $ret = array();
            global $wpdb;
            if (!empty($id_treaty)) {
                $rows = $wpdb->get_results("SELECT a.*, b.short_title FROM ai_decision a
                    INNER JOIN ai_treaty b ON a.id_treaty = b.id
                    WHERE a.id_treaty = $id_treaty
                    ORDER BY a.number");
                foreach ($rows as $row) {
                    $ob = new StdClass();
                    $ob->id = $row->id;
                    $ob->name = $row->short_title;
                    $ob->number = $row->number;
                    $ob->title = $row->long_title;
                    if ($ob->title === NULL || $ob->title == '') {
                        $ob->title = $row->short_title;
                    } else {
                        $ob->title = $row->short_title . ' - ' . $row->long_title;
                    }
                    $ob->title = $ob->number . ' - ' . $ob->title;
                    $ret[] = $ob;
                }
            }
            return $ret;
        }


        function get_decision_tags($id_decision) {
            global $wpdb;
            $ret = array();
            $rows = $wpdb->get_results(
                $wpdb->prepare("SELECT a.* FROM voc_concept a
                    INNER JOIN ai_decision_vocabulary b ON a.id = b.id_concept
                    WHERE b.id_decision = %d", $id_decision));
            foreach ($rows as $row) {
                $ret[$row->id] = $row;
            }
            return $ret;
        }

        function validate_edit_decision() {
            $this->actioned = TRUE;
            $val = new FormValidator();
            $val->addValidation("id_decision", "req", "Invalid decision");
            $val->addValidation("short_title", "req", "Invalid decision title");
            $val->addValidation("number", "req", "Invalid decision number");
            $val->addValidation("decision_type", "req", "Invalid decision type");
            $val->addValidation("status", "req", "Invalid decision status");
            $val->addValidation("published", "req", "Invalid decision publish date");
            $valid = $val->ValidateForm();
            if (!$valid) {
                $this->errors = $val->GetErrors();
            }
            if (!check_ajax_referer('decision_edit')) {
                $valid = false;
                $this->errors['security'] = 'Invalid security token';
            }

            $id_organization = get_request_int('id_organization');
            $id_treaty = get_request_int('id_treaty');
            $id_meeting = get_request_int('id_meeting');

            if (empty($id_meeting)) {
                $valid = false;
                $this->errors['id_meeting'] = 'Please select the meeting below';
            }
            if (empty($id_treaty) && empty($id_organization)) {
                $valid = false;
                $this->errors['id_organization'] = 'Please select either organization or treaty';
            }

            return $valid;
        }


        function edit_decision() {
            global $wpdb;
            global $current_user;
            $this->actioned = TRUE;
            if ($this->validate_edit_decision()) {
                $user = $current_user->user_login;
                $keywords = get_request_value('keywords', array(), false);
                $id_decision = get_request_int('id_decision');
                $rec_created = date('Y-m-d H:i:s', strtotime("now"));

                try {
                    @mysql_query("BEGIN", $wpdb->dbh);

                    // Update decision
                    $data = array();
                    $data['link'] = get_request_value('link');
                    $data['short_title'] = get_request_value('short_title');
                    $data['long_title'] = get_request_value('long_title');
                    $data['summary'] = get_request_value('summary');
                    $data['type'] = get_request_value('decision_type');
                    $data['status'] = get_request_value('status');
                    $data['number'] = get_request_value('number');
                    $data['id_treaty'] = get_request_int('id_treaty');
                    $data['id_organization'] = get_request_int('id_organization');
                    $data['published'] = get_request_value('published');
                    $updated = get_request_value('updated');
                    if (!empty($updated)) {
                        $data['updated'] = $updated;
                    }
                    $id_meeting = get_request_int('id_meeting');
                    if (!empty($id_meeting)) {
                        $data['id_meeting'] = $id_meeting;
                    }
                    $data['body'] = get_request_value('body');
                    $data['rec_created'] = $rec_created;
                    $data['rec_author'] = $user;

                    $wpdb->update('ai_decision', $data, array('id' => $id_decision));
                    $this->check_db_error();

                    // Update keywords
                    $wpdb->query($wpdb->prepare("DELETE FROM ai_decision_vocabulary WHERE id_decision = %d", $id_decision));
                    foreach ($keywords as $keyword) {
                        $success = $wpdb->insert('ai_decision_vocabulary',
                            array(
                                'id_decision' => $id_decision,
                                'id_concept' => intval($keyword),
                                'rec_author' => $user,
                                'rec_created' => $rec_created
                            )
                        );
                    }
                    @mysql_query("COMMIT", $wpdb->dbh);
                    $this->success = true;

                    // Log the action
                    $decision = $this->get_decision($id_decision);
                    $url = sprintf('%s/treaties/%d/decisions/?showall=true#decision-%d', get_bloginfo('url'), $decision->id_treaty, $id_decision);
                    $this->add_activity_log('update', 'decision', "Updated tags for decision '{$decision->number}'", null, $url);
                } catch (Exception $e) {
                    $this->success = FALSE;
                    @mysql_query("ROLLBACK", $wpdb->dbh);
                }
            }
        }


        function validate_add_decision() {
            check_ajax_referer('decision_add');
            $this->actioned = TRUE;
            $val = new FormValidator();
            $val->addValidation("id_treaty", "req", "Treaty is required");
            $val->addValidation("short_title", "req", "Missing short title");
            $val->addValidation("number", "req", "Missing decision number");
            $val->addValidation("decision_type", "req", "Missing type");
            $val->addValidation("status", "req", "Missing status");
            $val->addValidation("published", "req", "Missing date when decision was published");
            $valid = $val->ValidateForm();
            if (!$valid) {
                $this->errors = $val->GetErrors();
            }

            $id_organization = get_request_int('id_organization');
            $id_treaty = get_request_int('id_treaty');
            $id_meeting = get_request_int('id_meeting');

            if (empty($id_meeting)) {
                $valid = false;
                $this->errors['id_meeting'] = 'Please select the meeting below';
            }
            if (empty($id_treaty) && empty($id_organization)) {
                $valid = false;
                $this->errors['id_organization'] = 'Please select either organization or treaty';
            }
            return $valid;
        }


        function get_meetings_add_decision() {
            global $wpdb;
            $ret = array();
            $rows = $wpdb->get_results("SELECT a.id, a.start, a.title, COALESCE(b.short_title, 'Unknown') AS short_title FROM ai_event a
                LEFT JOIN ai_treaty b ON b.id = a.id_treaty
                WHERE a.type = 'cop'
                ORDER BY COALESCE(b.short_title, 'ZZ'), a.start DESC");
            foreach ($rows as $row) {
                if (empty($ret[$row->short_title])) {
                    $ret[$row->short_title] = array();
                }
                $ret[$row->short_title][] = $row;
            }
            return $ret;
        }


        function add_decision() {
            global $wpdb;
            global $current_user;
            $this->success = false;
            $this->actioned = true;
            $id_treaty = get_request_int('id_treaty');
            $treatyOb = new imea_treaties_page();
            $treaty = $treatyOb->get_treaty_by_id($id_treaty);
            $user = $current_user->user_login;
            $rec_created = date('Y-m-d H:i:s', strtotime("now"));

            $wpdb->query('BEGIN');
            try {
                $data = array();
                $data['link'] = get_request_value('link');
                $data['short_title'] = get_request_value('short_title');
                $data['long_title'] = get_request_value('long_title');
                $data['summary'] = get_request_value('summary');
                $data['type'] = get_request_value('decision_type');
                $data['status'] = get_request_value('status');
                $data['number'] = get_request_value('number');
                $data['id_treaty'] = get_request_int('id_treaty');
                $data['id_organization'] = get_request_int('id_organization');
                $data['published'] = get_request_value('published');
                $updated = get_request_value('updated');
                if (!empty($updated)) {
                    $data['updated'] = $updated;
                }
                $id_meeting = get_request_int('id_meeting');
                if (!empty($id_meeting)) {
                    $data['id_meeting'] = $id_meeting;
                }
                $data['meeting_url'] = get_request_value('meeting_url');
                $data['body'] = get_request_value('body');
                $data['rec_created'] = $rec_created;
                $data['rec_author'] = $user;
                $data['is_indexed'] = 0;

                $wpdb->insert('ai_decision', $data);
                $this->check_db_error();
                $id_decision = $wpdb->insert_id;

                // Upload the documents
                $upload_dir = '/uploads/decisions/' . $treaty->odata_name;
                if (!file_exists(ABSPATH . $upload_dir)) {
                    // Create directory if doesn't exist
                    mkdir(ABSPATH . $upload_dir, 0755, true);
                }
                if (isset($_FILES['document'])) {
                    // Check for errors
                    foreach ($_FILES['document']['name'] as $i => $name) {
                        if ($_FILES['document']['error'][$i] !== UPLOAD_ERR_OK && $_FILES['document']['error'][$i] !== UPLOAD_ERR_NO_FILE) {
                            throw new Exception("Error uploading file $name. Error code: {$_FILES['document']['error'][$i]}");
                        }
                    }
                    $languages = get_request_value('language', null, false);

                    foreach ($_FILES['document']['name'] as $i => $name) {
                        if ($_FILES['document']['error'][$i] == 0) {
                            if (is_uploaded_file($_FILES['document']['tmp_name'][$i])) {
                                $filename = "_{$id_decision}_" . uniqid() . '_' . $name;
                                $info = pathinfo($name);
                                $ext = $info['extension'];
                                $destination = ABSPATH . $upload_dir . '/' . $filename;

                                // Insert the document into database
                                $data = array();
                                $data['mime'] = $ext;
                                $data['url'] = get_bloginfo('url') . '/uploads/decisions/' . $treaty->odata_name . '/' . $filename;
                                $data['id_decision'] = $id_decision;
                                $data['path'] = $upload_dir . '/' . $filename;
                                $data['size'] = filesize($_FILES['document']['tmp_name'][$i]);
                                $data['is_indexed'] = 0;
                                $data['filename'] = $name;
                                $data['rec_created'] = $rec_created;
                                $data['rec_author'] = $user;
                                $data['language'] = isset($languages[$i]) ? $languages[$i] : 'en';

                                $wpdb->insert('ai_document', $data);
                                $this->check_db_error();

                                if (!move_uploaded_file($_FILES['document']['tmp_name'][$i], $destination)) {
                                    throw new Exception("Cannot move uploaded file $name to $destination!");
                                }
                            } else {
                                throw new Exception("File $name is not an uploaded file!");
                            }
                        }
                    }
                }
                // Log the action
                $decision = $this->get_decision($id_decision);
                $url = sprintf('%s/treaties/%d/decisions/?showall=true#decision-%d', get_bloginfo('url'), $id_treaty, $id_decision);
                $this->add_activity_log('insert', 'decision', "Inserted new decision '{$decision->number} {$decision->short_title}'", null, $url);
                $wpdb->query('COMMIT');
                $this->success = true;
            } catch (Exception $e) {
                $wpdb->query('ROLLBACK');
                throw $e;
            }
        }

        function validate_delete_decision() {
            check_ajax_referer('decision_delete');
            $this->actioned = TRUE;
            $id_decision = get_request_value('id_decision', null, false);
            $valid = count($id_decision) > 0;
            if (!$valid) {
                $this->errors = array('id_decision' => 'No decision selected');
            }
            return $valid;
        }

        function delete_decision() {
            global $wpdb;
            $id_decisions = get_request_value('id_decision', null, false);

            $wpdb->query('BEGIN');
            try {
                foreach ($id_decisions as $id_decision) {
                    $decision = $wpdb->get_row($wpdb->prepare("SELECT * FROM ai_decision WHERE id = %d", $id_decision));
                    // Remove associated paragraph tags
                    // http://www.xaprb.com/blog/2006/06/23/how-to-select-from-an-update-target-in-mysql/
                    $wpdb->query($wpdb->prepare("DELETE FROM ai_decision_paragraph_vocabulary
                            WHERE id_decision_paragraph IN
                            (
                                SELECT id_decision_paragraph FROM (
                                    SELECT id_decision_paragraph FROM ai_decision_paragraph_vocabulary a
                                    INNER JOIN ai_decision_paragraph b ON a.id_decision_paragraph = b.id
                                    WHERE id_decision = %d
                                ) AS tmptable
                            )
                ", $id_decision));

                    // Remove associated paragraphs
                    $wpdb->query($wpdb->prepare("DELETE FROM ai_decision_paragraph WHERE id_decision = %d", $id_decision));

                    // Remove associated tags
                    $wpdb->query($wpdb->prepare("DELETE FROM ai_decision_vocabulary WHERE id_decision = %d", $id_decision));

                    // Remove associated documents
                    $documents = $wpdb->get_results($wpdb->prepare("SELECT * FROM ai_document WHERE id_decision = %d", $id_decision));
                    foreach ($documents as $document) {
                        unlink(ABSPATH . '/' . $document->path);
                        $wpdb->query($wpdb->prepare("DELETE FROM ai_document WHERE id = %d", $document->id));
                    }

                    // Remove the decision
                    $wpdb->query($wpdb->prepare("DELETE FROM ai_decision WHERE id = %d", $id_decision));

                    // Log the action
                    $this->add_activity_log('delete', 'decision', "Deleted decision '{$decision->number} {$decision->short_title}'", null, null);
                }
                $wpdb->query('COMMIT');
                $this->success = true;
            } catch (Exception $e) {
                $wpdb->query('ROLLBACK');
                throw $e;
            }
            $this->actioned = true;
            $this->success = true;
            return true;
        }


        function get_allowed_status() {
            return array('draft', 'active', 'amended', 'retired', 'revised');
        }

        function get_allowed_type() {
            return array('decision', 'resolution', 'recommendation', 'legislation', 'case');
        }

        function ajax_order_decisions() {
            check_ajax_referer('secret_order_decisions', '_nonce');
            global $wpdb;
            $decisions = get_request_value('decisions');
            $id_treaty = get_request_int('id_treaty');
            $decisions = explode(',', $decisions);
            $ret = array();

            $display_order = array();
            foreach ($decisions as $idx => $id_decision) {
                $display_order[$id_decision] = $idx;
            }
            $ids = implode(',', array_keys($display_order));
            $sql = 'UPDATE ai_decision SET display_order = CASE id ';
            foreach ($display_order as $id => $ordinal) {
                $sql .= sprintf("WHEN %d THEN %d ", $id, $ordinal);
            }
            $sql .= "END WHERE id IN ($ids) AND id_treaty = $id_treaty";
            try {
                $wpdb->query('BEGIN');
                $wpdb->query($sql);
                $wpdb->query('COMMIT');
                $ret = $wpdb->get_results(
                    $wpdb->prepare("SELECT id, display_order AS `order` FROM ai_decision WHERE id_treaty = %d", $id_treaty)
                );
            } catch (Exception $e) {
                $wpdb->query('ROLLBACK');
                throw $e;
            }
            die('1');
        }
    }
}
?>
