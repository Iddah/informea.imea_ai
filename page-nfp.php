<?php
$nfp_id = get_request_variable('id_nfp', 1, NULL);
$nfp = imea_countries_page::get_nfp($nfp_id);
$treaties = informea_countries::get_focal_point_treaties($nfp);
$options = get_option('informea_options');
$public_key = $options['recaptcha_public'];
$send = FALSE;

$contact = get_request_value('contact');
$send = !empty($contact);

$cname = get_request_value('cname');
$email = get_request_value('email');
$message = get_request_value('message');
$copy = get_request_boolean('copy');
$copy_ck = $copy ? ' checked="checked"' : '';

$recaptcha_error = null;
$errors = NULL;

if($send) {
    $errors = informea_treaties::send_message_to_nfp_validate();
    if(empty($errors)) {
        $errors = informea_treaties::send_message_to_nfp();
    }
    $success = empty($errors);
}

get_header();

if(!empty($nfp)) :
if (have_posts()) : while (have_posts()) : the_post();
?>
    <div id="page-title">
        <h1>Contact <?php echo informea_countries::get_focal_point_name($nfp); ?></h1>
    </div>
    <div class="col2-left col2"></div>
    <div class="col2-center col2 text-justify">

        <?php the_content(); ?>
        <table class="table">
            <tr>
            <?php if(!empty($nfp->position)): ?>
                <td>Position</td>
                <td><?php echo $nfp->position; ?></td>
            <?php endif; ?>
            </tr>
            <tr>
            <?php if(!empty($nfp->institution)): ?>
                <td>Institution</td>
                <td><?php echo $nfp->institution; ?></td>
            <?php endif; ?>
            </tr>
            <tr>
            <?php if(!empty($nfp->telephone)): ?>
                <td>Telephone</td>
                <td><?php echo $nfp->telephone; ?></td>
            <?php endif; ?>
            </tr>
            <tr>
            <?php if(!empty($nfp->fax)): ?>
                <td>Fax</td>
                <td><?php echo $nfp->fax; ?></td>
            <?php endif; ?>
            </tr>
            <tr>
                <td>Representing</td>
                <td><?php echo $nfp->country_name; ?></td>
            </tr>
            <tr>
                <td>Focal point for</td>
                <td>
                    <ul>
                        <?php foreach($treaties as $treaty): echo sprintf('<li>%s</li>', $treaty->short_title); endforeach; ?>
                    </ul>
                </td>
            </tr>
        </table>

        <form action="" id="nfp-contact" method="post">
            <h2>Contact form</h2>

            <?php if($send && !empty($errors)): ?>
                <div class="alert alert-error">
                    Error validating your submission
                    <ul>
                        <?php foreach($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            <?php if($send && empty($errors)): ?>
                <div class="alert alert-success">
                    The message has been sent to the focal point!
                    <?php if($copy): ?>
                        You should receive a copy of the e-mail in your inbox. If not, please check spam first.
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="field">
                <label for="cname">Your name *</label>
                <input type="text" id="cname" name="cname" size="30" placeholder="Type your full name here"
                    value="<?php echo $cname; ?>" />
            </div>
            <div class="field">
                <label for="email">Your e-mail *</label>
                <input type="text" id="email" name="email" size="40"
                       placeholder="Your e-mail where person can respond"
                       value="<?php echo $email; ?>" />
            </div>
            <div class="field">
                <label for="message">Message to focal point *</label>
                <textarea id="message" name="message" class="top" cols="40" rows="6"><?php echo $message; ?></textarea>
            </div>
            <div class="field">
                <label>Copy</label>
                <input id="copy" type="checkbox" name="copy" value="1" <?php echo $copy_ck; ?> />
                <label id="l_copy" for="copy">I would like to receive a copy of the message</label>
            </div>
            <div class="field">
                <label>Security *</label>
                <div>
                <?php echo recaptcha_get_html($public_key, $recaptcha_error); ?>
                </div>
            </div>
            <input id="contact" type="submit" class="btn orange" name="contact" value="Send message" />
            <p class="info">* - required fields</p>
        </form>

    </div>
<?php
endwhile; endif;
get_footer();
else:
?>
    <div id="page-title">
        <h1><?php the_title(); ?></h1>
    </div>
    <div class="col2-left col2">

    </div>
    <div class="col2-center col2 text-justify">
    <div class="alert alert-error">
        The focal point does not exist, or page was incorrectly accessed.
        <br />
        Visit a <a href="/countries">country profile</a> or <a href="/treaties">treaty</a> page
        and look there for national focal points.
    </div>
    <p>
        <img src="http://www.informea.org/wp-content/uploads/2013/04/robot.jpg" />
    </p>
<?php
endif;