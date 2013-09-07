<?php
$id = get_request_variable('id', 1);
$page_data = new Thesaurus($id, array('id_ns' => 1, 'id_concept' => 1, 'feature' => 'str', 'mode' => 'str'));
$term = $page_data->term;
?>
    <h1><?php echo $term->term; ?></h1>
    <p>See content related to this term</p>