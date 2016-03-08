<?php

// Redefine user notification function
if(!function_exists('wp_new_user_notification'))
{
    function wp_new_user_notification($user_id, $plaintext_pass = '')
    {
        $user = new WP_User($user_id);

        $user_login = stripslashes($user->user_login);
        $user_email = stripslashes($user->user_email);

        $message  = sprintf(__('New user registration on your blog %s:'), get_option('blogname')) . "\r\n\r\n";
        $message .= sprintf(__('Username: %s'), $user_login) . "\r\n\r\n";
        $message .= sprintf(__('E-mail: %s'), $user_email) . "\r\n";

        @wp_mail(get_option('admin_email'), sprintf(__('[%s] New User Registration'), get_option('blogname')), $message);

        if(empty($plaintext_pass))
            return;
        
        $message = sprintf(__("Welcome to %s! Here's how to log in:"), get_option('blogname'))."\r\n\r\n";
        $message .= site_url('/login/')."\r\n";
        $message .= sprintf(__('Username: %s'), $user_login)."\r\n";
        $message .= sprintf(__('Password: %s'), $plaintext_pass)."\r\n\r\n";
//        $message .= sprintf(__('If you have any problems, please contact me at %s.'), get_option('admin_email')) . "\r\n\r\n";
        
        wp_mail($user_email, sprintf(__('[%s] Your username and password'), get_option('blogname')), $message);
    }
}

// add mail from to mail
add_filter ('wp_mail_from', 'my_awesome_mail_from');
function my_awesome_mail_from(){ return 'TheEngineerTutor'; }

// add from name to mail
add_filter ('wp_mail_from_name', 'my_awesome_mail_from_name');
function my_awesome_email_from_name(){ return 'TheEngineerTutor'; }