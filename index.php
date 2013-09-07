<?php
/**
 * The template for displaying all pages.
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site will use a
 * different template.
 *
 * @package WordPress
 * @subpackage Twenty_Ten
 * @since Twenty Ten 1.0
 */

get_header(); ?>
<div id="container" style="width: 100% !important;">
    <br/>

    <div id="content" role="main">
        <?php if (have_posts()) {
            while (have_posts()) : the_post(); ?>
                <div id="post-<?php the_ID(); ?>" <?php post_class(); ?> style="border: none !important;">
                    <?php if (is_front_page()) { ?>
                        <h2><?php the_title(); ?></h2>
                    <?php } else { ?>
                        <h1><?php the_title(); ?></h1>
                    <?php } ?>

                    <div class="entry-content">
                        <?php the_content(); ?>
                        <?php wp_link_pages(array('before' => '<div class="page-link">' . __('Pages:', 'twentyten'), 'after' => '</div>')); ?>
                        <?php edit_post_link(__('Edit', 'twentyten'), '<span class="edit-link">', '</span>'); ?>
                    </div>
                    <!-- .entry-content -->
                </div><!-- #post-## -->
            <?php endwhile;
        } ?>
        <div class="clear"></div>
    </div>
    <!-- #content -->
</div><!-- #container -->
<?php get_footer(); ?>
