<?php
global $wpdb;
$entity = get_request_variable('entity');
if ($entity == 'decision_document') {
    $id = get_request_int('id');
    $row = $wpdb->get_row("SELECT url, path FROM ai_document WHERE id = $id");
    if(empty($row)) {
        download_die_404();
    } else {
        $remote_url = $row->url;
        $handle = curl_init($remote_url);
        curl_setopt($handle, CURLOPT_NOBODY, 1);
        curl_setopt($handle, CURLOPT_HEADER, 1);
        curl_setopt($handle, CURLOPT_TIMEOUT, 5);
        curl_exec($handle);
        $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
        if ($httpCode != 200) {
            $local_url = get_bloginfo('url') . '/' . $row->path;
            header("Location: $local_url");
        } else {
            header("Location: $remote_url");
        }
    }
    exit();

} else if ('terms_csv' == $entity) {
    $filename = 'informea_vocabulary_' . date('d_M_Y_H_i') . '.csv';
    header('Content-type: text/csv');
    header("Content-disposition: attachment;filename=$filename");
    Thesaurus::generate_download_csv();
    exit();

} else if ('countries_csv' == $entity) {
    $filename = 'informea_countries_' . date('d_M_Y_H_i') . '.csv';
    header('Content-type: text/csv');
    header("Content-disposition: attachment;filename=$filename");
    informea_countries::generate_parties_download_csv();
    exit();

} else if ('vcard' == $entity) {
    $id = get_request_int('id');
    informea_treaties::generate_vcard($id);

} else if ('rss' == $entity) {
    $id = get_request_variable('id');
    if($id == 'meetings') {
        InformeaRSSWriter::meetings_rss();
    } else if($id == 'news') {
        InformeaRSSWriter::news_rss();
    } else {
        download_die_404();
        exit();
    }

} else {
    download_die_404();
    exit();
}


function download_die_404() {
    global $wp_query;
    $wp_query->set_404();
    status_header(404);
    get_template_part(404);
}