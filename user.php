<?php

class KMI_User
{
    public function __construct()
    {
        // shortcodes
        // login UI
        add_shortcode('user_login_info', array($this, 'User_Login_Info'));
        add_shortcode('kmi_login_form', array($this, 'Login_Form'));
        // registration UI
        add_shortcode('kmi_registration_form', array($this, 'Registration_Form'));
        // reset password UI
        add_shortcode('kmi_reset_password_form', array($this, 'Reset_Password_Form'));
        // change password UI
        add_shortcode('kmi_change_password_form', array($this, 'Change_Password_Form'));
        // profile UI
        add_shortcode('kmi_profile_form', array($this, 'Profile_Form'));
        // Account Activation UI
        add_shortcode('kmi_account_activation_form', array($this, 'Account_Activation_Form'));
        
        // filter hooks
        // change login / logout page title
        add_filter('the_title', array($this, 'Toggle_LoginLogout_Title'));
        
        // action hooks
        // add forgot password and register link on login form
        add_action('login_form_middle', array($this, 'Add_LostPassword_Register_Link'));
        // add redirection handler for failed login
        add_action('wp_login_failed', array($this, 'Login_Failed_Redirect'));
        // add redirection handler for empty username and password
        add_action('authenticate', array($this, 'Login_Empty_Redirect'));
        // add redirection handler for failed registration
        add_action('register_post', array($this, 'Register_Failed_Redirect'), 99, 3);
        // disable admin bar for non admin users
        add_action('after_setup_theme', array($this, 'Remove_Admin_Bar'));
        // block wp-admin for non admin users
        add_action('admin_init', array($this, 'Restrict_Admin_Pages'));
    }
    
    public function Toggle_LoginLogout_Title($title)
    {
        if($title == 'Login / Logout')
        {
            if(is_user_logged_in())
                $title = 'Logout';
            else
                $title = 'Login';
        }
        return $title;
    }
    
    public function User_Login_Info()
    {
        global $current_user;
        
        get_currentuserinfo();
        
        if(is_user_logged_in()):
            ?>
            <h3 style="float: right; margin: 20px 15px 0 0;">
                Logged in as <a href="<?php echo site_url('/profile/'); ?>"><?php echo $current_user->display_name; ?></a> 
                [ <a href="<?php echo wp_logout_url(get_permalink()); ?>">Logout</a> ]
            </h3>
            <?php
        endif;
    }
    
    public function Login_Form()
    {
        $args = array('redirect'=> site_url().'/login/');
        
        if(isset($_GET['login']) && $_GET['login'] == 'failed')
        {
            ?>
            <p class="error">Login failed: You have entered an incorrect Username or password, please try again.</p>
            <?php
        }
        
        if(!empty($_GET['ref']) && !empty($_GET['ac']))
        {
            $user_id = get_user_id_from_string(trim($_GET['ref']));
            
            $activation_code = get_user_meta($user_id, 'activation_code', true);
            
            if($activation_code === trim($_GET['ac']))
            {
                update_user_meta($user_id, 'active_status', 'yes');
            }
        }
        
        if(!is_user_logged_in())
            wp_login_form($args);
        else
        {
            $user = wp_get_current_user();
            
            if($user->kmi_expiry_date < date('Y-m-d'))
            {
                $user->kmi_member_type = 'Free';
                update_user_meta($user->ID, 'kmi_member_type', $user->kmi_member_type);
            }
            
            $member_type = strtolower($user->kmi_member_type);
            
            if(!empty($user->first_name) && !empty($user->last_name))
                $display_name = $user->first_name.' '.$user->last_name;
            elseif(!empty($user->display_name))
                $display_name = $user->display_name;
            else
                $display_name = $user->user_login;
            
            ?>
            <h2>
                Welcome <a href="<?php site_url(); ?>/profile/"><?php echo $display_name; ?></a> 
                [ <a href="<?php echo wp_logout_url(get_permalink()); ?>">Logout</a> ].
            </h2>
            <?php if($member_type == 'premium'): ?>
                <p>You are granted full privileges as a premium user on this site.</p>
            <?php elseif($member_type == 'gold'): ?>
                <p>
                    You are granted a limited privileges as a gold user on this site.<br/>
                    You can upgrade your account to Premium <a href="<?php echo site_url('/subscription/'); ?>">here</a>.
                </p>
            <?php else: ?>
                <p>
                    You are granted a minimum privileges as a free user on this site.<br/>
                    You can upgrade your account <a href="<?php echo site_url('/subscription/'); ?>">here</a>.
                </p>
            <?php endif; ?>
            <?php
        }
    }
    
    public function Add_LostPassword_Register_Link()
    {
	return '<a href="/reset-password/" style="margin-left: 6px;">Forgot your password?</a> | <a href="/register/">Register</a>';
    }
    
    public function Login_Failed_Redirect($user)
    {
        // check what page the login attempt is coming from
        $referrer = $_SERVER['HTTP_REFERER'];
        
        // check that were not on the default login page
        if(!empty($referrer) && !strstr($referrer,'wp-login') && !strstr($referrer,'wp-admin') && $user != null)
        {
            // make sure we don't already have a failed login attempt
            if(!strstr($referrer, '?login=failed'))
            {
                // Redirect to the login page and append a querystring of login failed
	    	wp_redirect($referrer.'?login=failed');
	    }
            else
                wp_redirect($referrer);

	    exit;
	}
    }
    
    public function Login_Empty_Redirect($user)
    {
        // check what page the login attempt is coming from
        $referrer = $_SERVER['HTTP_REFERER'];
        
        $error = false;
        
        if($_POST['log'] == '' || $_POST['pwd'] == '')
            $error = true;
        
        // check that were not on the default login page
  	if(!empty($referrer) && !strstr($referrer,'wp-login') && !strstr($referrer,'wp-admin') && $error)
        {
            // make sure we don't already have a failed login attempt
            if(!strstr($referrer, '?login=failed'))
            {
                // Redirect to the login page and append a querystring of login failed
        	wp_redirect($referrer . '?login=failed');
            }
            else
                wp_redirect($referrer);
            
            exit;
      	}
    }

    public function Registration_Form()
    {
        if($_GET['register'] == 'failed')
        {
            ?>
            <p class="error">Registration failed.<br/>
                <?php
                    foreach($_GET as $key => $value)
                    {
                        if($key === 'register')
                            continue;
                        echo ucwords(str_replace('_', ' ', $key)).'<br/>';
                    }
                ?>
            </p>
            <?php
        }
        elseif($_GET['register'] == 'true')
            echo '<p class="success">Check your email for the password!</p>';
        
        ?>
        <h4>Create an account for this site.</h4>
        <form id="loginform" action="<?php echo site_url('wp-login.php?action=register', 'login_post'); ?>" method="POST">
            <p class="login-username">
                <label for="user_login">Username</label>
                <input type="text" name="user_login" id="user_login" class="input" value="" size="20" />
            </p>
            <p class="login-email">
                <label for="user_email">Email</label>
                <input type="email" name="user_email" id="user_email" class="input" value="" size="20" />
                <?php do_action('register_form'); ?>
            </p>
            <p class="login-submit">
		<input type="submit" name="wp-submit" id="wp-submit" class="button-primary" value="Register" />
            </p>
            <input type="hidden" name="redirect_to" value="<?php echo get_site_url().'/register/'; ?>?register=true" />
            <input type="hidden" name="user-cookie" value="1" />
        </form>
        <?php
    }
    
    public function Register_Failed_Redirect($sanitized_user_login, $user_email, $errors)
    {
        // this line is copied from register_new_user function of wp-login.php
        $errors = apply_filters('registration_errors', $errors, $sanitized_user_login, $user_email);
        //this if check is copied from register_new_user function of wp-login.php
        if($errors->get_error_code())
        {
            //setup your custom URL for redirection
            $referrer = get_site_url().'/register/'; // current registration page
            //add error codes to custom redirection URL one by one
            $referrer = add_query_arg('register', 'failed', $referrer); 
            foreach($errors->errors as $e => $m)
            {
                $referrer = add_query_arg($e, '1', $referrer);    
            }
            //add finally, redirect to your custom page with all errors in attributes
            wp_redirect($referrer);
            exit;
        }
    }
    
    public function Account_Activation_Form()
    {
        
    }

    public function Reset_Password_Form()
    {
        global $wpdb;
        $errors = array();
        
        if(isset($_POST['resetpass']))
        {
            $username = trim($_POST['user_login']);
            $user_exists = false;

            // first check by username
            if (username_exists($username))
            {
                $user_exists = true;
                $user = get_user_by('login', $username);
            }
            // then by e-mail address
            else if(email_exists($username))
            {
                $user_exists = true;
                $user = get_user_by_email($username);
            }
            else
                $errors[] = __('Username or Email was not found, try again!');
            
            if($user_exists)
            {
                do_action('lostpassword_post');
                
                $user_login = $user->user_login;
                $user_email = $user->user_email;

                do_action('retrieve_password', $user_login);
            
                $allow = apply_filters('allow_password_reset', true, $user->ID);

                if(!$allow)
                    $errors[] = __('Password reset is not allowed for this user.');

                // Generate something random for a password reset key.
                $key = wp_generate_password(20, false);

                // fires when a password reset key is generated
                do_action('retrieve_password_key', $user_login, $key);

                // Now insert the key, hashed, into the DB.
                if(empty($wp_hasher))
                {
                    require_once ABSPATH.'wp-includes/class-phpass.php';
                    $wp_hasher = new PasswordHash(8, true);
                }
                $hashed = $wp_hasher->HashPassword($key);
                $wpdb->update($wpdb->users, array('user_activation_key' => $hashed), array('user_login' => $user_login));

                if(!$this->Reset_Password_Email($key, $user_login, $user_email))
                    $errors[] = __('The e-mail could not be sent.')."<br />\n".__('Possible reason: your host may have disabled the mail() function...');
            }
            
            if(count($errors) > 0)
            {
                echo '<p class="error">';
                foreach($errors as $error)
                {
                    echo $error.'<br/>';
                }
                echo '</p>';
            }
            else
                echo '<p class="success">We have just sent you an email with Password reset instructions.</p>';
        }
        
        ?>
        <!--<form id="loginform" action="<?php echo site_url('wp-login.php?action=lostpassword', 'login_post'); ?>" method="POST">-->
        <h4>Please enter your username or email address. You will receive a link to create a new password via email.</h4>
        <form id="loginform" action="" method="POST">
            <p class="login-username">
                <label for="user_login" class="hide"><?php _e('Username or Email'); ?> : </label>
                <input type="text" name="user_login" id="user_login" class="input" value="" size="20" />
            </p>
            <p class="login-submit">
               <?php do_action('login_form', 'resetpass'); ?>
		<input type="submit" name="resetpass" id="wp-submit" class="button-primary" value="<?php _e('Reset my password'); ?>" />
            </p>
            <!--<input type="hidden" name="redirect_to" value="<?php echo $_SERVER['REQUEST_URI']; ?>?reset=true" />-->
            <!--<input type="hidden" name="user-cookie" value="1" />-->
        </form>
        <?php
    }
    
    public function Change_Password_Form()
    {
        $errors = new WP_Error();
        
        $messages = array();
        
        $user = check_password_reset_key($_GET['key'], $_GET['login']);
        
        if(is_wp_error($user))
        {
            if($user->get_error_code() === 'expired_key')
                $errors->add('expired_key', __('Key has expired. Go <a href="'.site_url('/reset-password/').'">here</a> to request new key.'));
            else
                $errors->add('invalid_key', __('Invalid key. Go <a href="'.site_url('/reset-password/').'">here</a> to request new key.'));
	}
        
        if(isset($_POST['wp-submit']) && $_POST['pass1'] != $_POST['pass2'])
            $errors->add('password_reset_mismatch', __('The passwords did not match.'));
        
        do_action('validate_password_reset', $errors, $user);
        
        if(!$errors->get_error_code() && !empty($_POST['pass1']))
        {
            
            reset_password($user, $_POST['pass1']);
            echo __('<p class="success">Your password has been reset.').' <a href="'.site_url('/login/').'">'.__('Log in').'</a></p>';
        }
        elseif($errors->get_error_code())
        {
            echo '<p class="error">';
            foreach($errors->get_error_messages() as $error)
            {
                echo $error.'<br/>';
            }
            echo '</p>';
        }
        
        ?>
        <form id="loginform" action="" method="POST">
            <p class="login-username">
                <label for="pass1" class="hide"><?php _e('New password'); ?> : </label>
                <input type="password" name="pass1" id="pass1" class="input" value="" size="20" autocomplete="off" />
            </p>
            <p class="login-password">
                <label for="pass2" class="hide"><?php _e('Confirm new password'); ?> : </label>
                <input type="password" name="pass2" id="pass2" class="input" value="" size="20" autocomplete="off" />
            </p>
            <p class="login-submit">
                <input type="submit" name="wp-submit" id="wp-submit" class="button-primary" value="<?php _e('Reset Password'); ?>" />
            </p>
        </form>
        <?php
    }
    
    public function Profile_Form()
    {
        global $current_user;
        
        get_currentuserinfo();
        
        $errors = array();
        
        /* If profile was saved, update profile. */
        if(isset($_POST['updateuser']) && $_POST['action'] == 'update-user')
        {
            /* update user password. */
            if(!empty($_POST['pass1']) && !empty($_POST['pass2']))
            {
                if($_POST['pass1'] == $_POST['pass2'])
                    wp_update_user(array('ID' => $current_user->ID, 'user_pass' => esc_attr($_POST['pass1'])));
                else
                    $errors[] = __('The passwords you entered do not match. Your password was not updated.', 'profile');
            }
            
            /* update user information. */
            if(!empty($_POST['url']))
                wp_update_user(array('ID' => $current_user->ID, 'user_url' => esc_attr($_POST['url'])));
             
            if(!empty($_POST['email']))
            {
                if (!is_email(esc_attr($_POST['email'])))
                    $errors[] = __('The Email you entered is not valid. please try again.', 'profile');
                elseif(email_exists(esc_attr($_POST['email'])) != $current_user->id)
                    $errors[] = __('This email is already used by another user. try a different one.', 'profile');
                else
                    wp_update_user(array('ID' => $current_user->ID, 'user_email' => esc_attr($_POST['email'])));
            }
            
            if(!empty($_POST['first-name']))
                update_user_meta($current_user->ID, 'first_name', esc_attr($_POST['first-name']));
            
            if(!empty($_POST['last-name']))
                update_user_meta($current_user->ID, 'last_name', esc_attr($_POST['last-name']));
            
            if(!empty($_POST['display_name']))
            {
                wp_update_user(array('ID' => $current_user->ID, 'display_name' => esc_attr($_POST['display_name'])));
                update_user_meta($current_user->ID, 'display_name' , esc_attr($_POST['display_name']));
            }
            
            if(!empty($_POST['description']))
                update_user_meta($current_user->ID, 'description', esc_attr($_POST['description']));
            
            if(count($errors) > 0)
            {
                ?>
                <p class="error">Updating profile failed.<br/>
                    <?php
                        foreach($errors as $error)
                        {
                            echo $error.'<br/>';
                        }
                    ?>
                </p>
                <?php
            }
            else
            {
                ?>
                <p class="success">Profile update successful.</p>
                <?php
            }
        }
        
        if(is_user_logged_in()):
            ?>
            <form method="post" id="adduser" action="">
                <p class="form-username">
                    <label for="first-name"><?php _e('First Name', 'profile'); ?></label>
                    <input class="text-input" name="first-name" type="text" id="first-name" value="<?php the_author_meta( 'first_name', $current_user->ID ); ?>" />
                </p><!-- .form-username -->
                <p class="form-username">
                    <label for="last-name"><?php _e('Last Name', 'profile'); ?></label>
                    <input class="text-input" name="last-name" type="text" id="last-name" value="<?php the_author_meta( 'last_name', $current_user->ID ); ?>" />
                </p><!-- .form-username -->
                <p class="form-display_name">
                    <label for="display_name"><?php _e('Display name publicly as') ?></label>
                    <select name="display_name" id="display_name"><br/>
                        <?php
                            $public_display = array();
                            $public_display['display_nickname']  = $current_user->nickname;
                            $public_display['display_username']  = $current_user->user_login;

                            if(!empty($current_user->first_name))
                                $public_display['display_firstname'] = $current_user->first_name;

                            if(!empty($current_user->last_name))
                                $public_display['display_lastname'] = $current_user->last_name;

                            if(!empty($current_user->first_name) && !empty($current_user->last_name))
                            {
                                $public_display['display_firstlast'] = $current_user->first_name . ' ' . $current_user->last_name;
                                $public_display['display_lastfirst'] = $current_user->last_name . ' ' . $current_user->first_name;
                            }

                            if(!in_array($current_user->display_name, $public_display)) // add display name if it isn't existed
                                $public_display = array('display_displayname' => $current_user->display_name) + $public_display;

                            $public_display = array_map('trim', $public_display);
                            $public_display = array_unique($public_display);

                            $display_name = !empty($_POST['display_name']) ? $_POST['display_name'] : $current_user->display_name;

                            foreach($public_display as $id => $item):
                                ?><option <?php selected($display_name, $item); ?>><?php echo $item; ?></option><?php
                            endforeach;
                        ?>
                    </select>
                </p><!-- .form-display_name -->
                <p class="form-email">
                    <label for="email"><?php _e('E-mail *', 'profile'); ?></label>
                    <input class="text-input" name="email" type="text" id="email" value="<?php the_author_meta( 'user_email', $current_user->ID ); ?>" />
                </p><!-- .form-email -->
                <p class="form-url">
                    <label for="url"><?php _e('Website', 'profile'); ?></label>
                    <input class="text-input" name="url" type="text" id="url" value="<?php the_author_meta( 'user_url', $current_user->ID ); ?>" />
                </p><!-- .form-url -->
                <p class="form-password">
                    <label for="pass1"><?php _e('Password *', 'profile'); ?> </label>
                    <input class="text-input" name="pass1" type="password" id="pass1" />
                </p><!-- .form-password -->
                <p class="form-password">
                    <label for="pass2"><?php _e('Repeat Password *', 'profile'); ?></label>
                    <input class="text-input" name="pass2" type="password" id="pass2" />
                </p><!-- .form-password -->
                <p class="form-textarea">
                    <label for="description"><?php _e('Biographical Information', 'profile') ?></label>
                    <textarea name="description" id="description" rows="3" cols="50"><?php the_author_meta( 'description', $current_user->ID ); ?></textarea>
                </p><!-- .form-textarea -->
                <?php
                    //action hook for plugin and extra fields
                    do_action('edit_user_profile', $current_user);
                ?>
                <p class="form-submit">
                    <?php echo $referer; ?>
                    <input name="updateuser" type="submit" id="updateuser" class="submit button" value="<?php _e('Update', 'profile'); ?>" />
                    <?php wp_nonce_field('update-user_'. $current_user->ID) ?>
                    <input name="action" type="hidden" id="action" value="update-user" />
                </p><!-- .form-submit -->
            </form>
            <?php
        else:
            echo '<h3>Sorry, you must first <a href="'.site_url().'/login/">log in</a> to view this page. You can <a href="'.site_url().'/register/">register free here</a>.</h3>';
        endif;
    }
    
    public function Restrict_Admin_Pages()
    {
        if(is_admin() && !current_user_can('administrator')
            && !(defined('DOING_AJAX') && DOING_AJAX))
        {
            wp_redirect(site_url().'/login/');
            exit;
        }
    }
    
    public function Remove_Admin_Bar()
    {
        if(!current_user_can('administrator') && !is_admin())
            show_admin_bar(false);
    }

    private function Reset_Password_Email($key, $user_login, $user_email)
    {
        //create email message
        $message = __('Someone has asked to reset the password for the following site and username.')."\r\n\r\n";
        $message .= get_option('siteurl')."\r\n\r\n";
        $message .= sprintf(__('Username: %s'), $user_login)."\r\n\r\n";
        $message .= __('To reset your password visit the following address, otherwise just ignore this email and nothing will happen.')."\r\n\r\n";
        $message .= network_site_url("/change-password/?action=rp&key={$key}&login=".rawurlencode($user_login), 'login')."\r\n";
        //send email meassage
        return wp_mail($user_email, sprintf(__('[%s] Password Reset'), get_option('blogname')), $message);
    }
}
 
$kmi_user = new KMI_User();