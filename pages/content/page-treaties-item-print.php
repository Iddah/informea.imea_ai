<?php
$treaty = informea_treaties::get_treaty_from_request();
$page_data = new informea_treaties($treaty->id);
$articles = informea_treaties::get_articles($treaty->id);
$all_paragraphs = informea_treaties::get_all_paragraphs($treaty->id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <title><?php echo $treaty->long_title; ?></title>
    <style type="text/css">
        h1 { text-align: center; }
        img { float: left; vertical-align: middle; margin: 0 20px; }
        ul { list-style-type: none; margin: 0; padding: 0; }
        ul li { padding: 0; margin: 20px 0 0 0; }
        table { margin: 0; padding: 0; border: 0; border-collapse: collapse; }
        table td { margin: 0; padding: 5px; border: 0;}
        table td.td-tags { width: 25%; }
        .zebra { background-color: #f4f4f4; }
        td.text { width: 950px; }
        li.indent-1 { margin-left: 30px; }
        li.indent-2 { margin-left: 60px; }
        li.indent-3 { margin-left: 90px; }
        li.indent-4 { margin-left: 120px; }
        li.indent-5 { margin-left: 150px; }
        div.clear { clear: both; }
    </style>
</head>
<body>
    <h1>
        <img src="<?php echo get_bloginfo('template_directory'); ?>/images/logo-black.png" />
        <?php echo $treaty->long_title; ?>
    </h1>
    <div class="clear"></div>
    <table id="treaty-print" class="treaty-<?php echo $treaty->odata_name; ?>">
        <?php
        $c = 0;
        foreach ($articles as $article) {
            $at = $page_data->get_article_tags($article->id);
            $paragraphs = $all_paragraphs[$article->id];
            $atags_ob = $page_data->get_article_tags($article->id);
            $atags = array();
            ?>
            <tr class="new-article">
                <td><h2><?php echo $article->official_order; ?> <?php echo $article->title; ?></h2></td>
                <td class="td-tags">
                    <?php
                    if (count($atags_ob)) {
                        foreach ($atags_ob as $atag) {
                            $atags[] = $atag->term;
                        }
                        echo '<strong>Tags</strong>: ';
                        echo implode(', ', $atags);
                    }
                    ?>
                    &nbsp;
                </td>
            </tr>
            <?php
            if (count($paragraphs)) {
                foreach ($paragraphs as $paragraph) {
                    $zebra = ($c++ % 2 == 0) ? 'zebra' : '';
                    $ptags_ob = $page_data->get_paragraph_tags($paragraph->id);
                    $ptags = array();
                    ?>
                    <tr class="<?php echo $zebra; ?>">
                        <td class="content"><?php echo $paragraph->content; ?></td>
                        <td class="td-tags">
                            <?php
                            if (count($ptags_ob)) {
                                echo '<strong>Tags</strong>: ';
                                foreach ($ptags_ob as $ptag) {
                                    $ptags[] = $ptag->term;
                                }
                                echo implode(', ', $ptags);
                            }
                            ?>
                            &nbsp;
                        </td>
                    </tr>
                <?php
                }
            } else {
                // No paragraphs, list the entire article
                ?>
                <tr>
                    <td class="content"><?php echo $article->content; ?></td>
                    <td class="td-tags">
                        <?php
                        if (count($atags_ob)) {
                            foreach ($atags_ob as $atag) {
                                $atags[] = $atag->term;
                            }
                            echo '<strong>Tags</strong>: ';
                            echo implode(', ', $atags);
                        }
                        ?>
                        &nbsp;
                    </td>
                </tr>
            <?php
            }
        }
        ?>
    </table>
</body>
</html>