<?php

if(!defined('ABSPATH')) exit; // Exit if accessed directly

class KMI_User_Control
{
    public $general_settings;
    private $general_settings_key = 'kmi_user_control_general_settings';
    private $plugin_options_key = 'kmi_user_control_menu_option';
    private $plugin_settings_tabs = array();
    private $message = array();
    
    public function __construct()
    {
        $this->Setup_Shortcodes();
        $this->Setup_Action_Hooks();
    }
    
    public function Add_User_Control_Option_Page()
    {
        if(empty($GLOBALS['admin_page_hooks']['kmi_menu_options']))
            add_menu_page('KMI Options', 'KMI Options', 'manage_options', 'kmi_menu_options', array($this, 'KMI_Options_Page'));
        
        if(empty($GLOBALS['admin_page_hooks'][$this->plugin_options_key]))
        {
            add_submenu_page('kmi_menu_options', 'KMI User Control', 'User Control', 'manage_options', $this->plugin_options_key, array($this, 'User_Control_Option_Page'));
        }
    }
    
    /*
     * KMI option page UI
     */
    public function KMI_Options_Page()
    {
        ?>
        <div class="wrap">
            <h2>Welcome to KMI Technology plugins. You can select the items under this menu to edit the desired plugin's settings.</h2>
        </div>
        <?php
    }
    
    public function User_Control_Option_Page()
    {
        ?>
        <div class="wrap">
            <form method="POST" action="options.php">
                <?php
                    settings_fields($this->general_settings_key);
                    
                    do_settings_sections($this->general_settings_key);
                    
                    submit_button();
                ?>
            </form>
        </div>
        <?php
    }
    
    public function Register_User_Control_Settings()
    {
        // Register general settings tab
        $this->plugin_settings_tabs[$this->general_settings_key] = 'General';
        register_setting($this->general_settings_key, $this->general_settings_key, array($this, 'Sanitize_User_Control_General_Settings'));
        // Add general settings section
        add_settings_section('kmi_user_control_general_section', 'General Settings', array($this, 'General_Description_Section'), $this->general_settings_key);
        // Add fields on the general settings tab
        add_settings_field('kmi_user_control_register_url', 'Register URL', array($this, 'Display_User_Control_Register_URL_Field'), $this->general_settings_key, 'kmi_user_control_general_section');
        add_settings_field('kmi_user_control_login_url', 'Login URL', array($this, 'Display_User_Control_Login_URL_Field'), $this->general_settings_key, 'kmi_user_control_general_section');
        add_settings_field('kmi_user_control_profile_url', 'Profile URL', array($this, 'Display_User_Control_Profile_URL_Field'), $this->general_settings_key, 'kmi_user_control_general_section');
        add_settings_field('kmi_user_control_reset_password_url', 'Reset Password URL', array($this, 'Display_User_Control_Reset_Password_URL_Field'), $this->general_settings_key, 'kmi_user_control_general_section');
        add_settings_field('kmi_user_control_change_password_url', 'Change Password URL', array($this, 'Display_User_Control_Change_Password_URL_Field'), $this->general_settings_key, 'kmi_user_control_general_section');
    }
    
    public function Sanitize_User_Control_General_Settings($input)
    {
        $new_input = array();
        
        if(isset($input['kmi_user_control_register_url']))
            $new_input['kmi_user_control_register_url'] = esc_url(sanitize_text_field($input['kmi_user_control_register_url']));
        
        if(isset($input['kmi_user_control_login_url']))
            $new_input['kmi_user_control_login_url'] = esc_url(sanitize_text_field($input['kmi_user_control_login_url']));
        
        if(isset($input['kmi_user_control_profile_url']))
            $new_input['kmi_user_control_profile_url'] = esc_url(sanitize_text_field($input['kmi_user_control_profile_url']));
        
        if(isset($input['kmi_user_control_reset_password_url']))
            $new_input['kmi_user_control_reset_password_url'] = esc_url(sanitize_text_field($input['kmi_user_control_reset_password_url']));
        
        if(isset($input['kmi_user_control_change_password_url']))
            $new_input['kmi_user_control_change_password_url'] = esc_url(sanitize_text_field($input['kmi_user_control_change_password_url']));

        return $new_input;
    }
    
    public function General_Description_Section() { echo 'General settings section goes here.'; }
    
    public function Display_User_Control_Register_URL_Field()
    {
        ?>
        <input type="text" id="kmi_user_control_register_url" class="regular-text" name="<?php echo $this->general_settings_key; ?>[kmi_user_control_register_url]" value="<?php echo $this->general_settings['kmi_user_control_register_url']; ?>" />
        <?php
    }
    
    public function Display_User_Control_Login_URL_Field()
    {
        ?>
        <input type="text" id="kmi_user_control_login_url" class="regular-text" name="<?php echo $this->general_settings_key; ?>[kmi_user_control_login_url]" value="<?php echo $this->general_settings['kmi_user_control_login_url']; ?>" />
        <?php
    }
    
    public function Display_User_Control_Profile_URL_Field()
    {
        ?>
        <input type="text" id="kmi_user_control_profile_url" class="regular-text" name="<?php echo $this->general_settings_key; ?>[kmi_user_control_profile_url]" value="<?php echo $this->general_settings['kmi_user_control_profile_url']; ?>" />
        <?php
    }
    
    public function Display_User_Control_Reset_Password_URL_Field()
    {
        ?>
        <input type="text" id="kmi_user_control_reset_password_url" class="regular-text" name="<?php echo $this->general_settings_key; ?>[kmi_user_control_reset_password_url]" value="<?php echo $this->general_settings['kmi_user_control_reset_password_url']; ?>" />
        <?php
    }
    
    public function Display_User_Control_Change_Password_URL_Field()
    {
        ?>
        <input type="text" id="kmi_user_control_change_password_url" class="regular-text" name="<?php echo $this->general_settings_key; ?>[kmi_user_control_change_password_url]" value="<?php echo $this->general_settings['kmi_user_control_change_password_url']; ?>" />
        <?php
    }
    
    public function Initialization()
    {
        // For the tab control
        $this->general_settings = (array)get_option($this->general_settings_key);
        
        // Merge with defaults
	$this->general_settings = array_merge(
            array(
                'general_option' => 'General value'
            ),
            $this->general_settings
        );
        
        /* If profile was saved, update profile. */
        if(isset($_POST['updateuser']) && $_POST['action'] == 'update-user')
        {
            global $current_user;
            
            /* update user password. */
            if(!empty($_POST['pass1']) && !empty($_POST['pass2']))
            {
                if($_POST['pass1'] == $_POST['pass2'])
                    wp_update_user(array('ID' => $current_user->ID, 'user_pass' => esc_attr($_POST['pass1'])));
                else
                    $this->message['profile_update']['error'][] = __('The passwords you entered do not match. Your password was not updated.', 'profile');
            }
            
            /* update user information. */
            if(!empty($_POST['url']))
                wp_update_user(array('ID' => $current_user->ID, 'user_url' => esc_url($_POST['url'])));
             
            if(!empty($_POST['email']))
            {
                if (!is_email($_POST['email']))
                    $this->message['profile_update']['error'][] = __('The Email you entered is not valid. Your email was not updated.', 'profile');
                elseif(email_exists($_POST['email']) != $current_user->ID)
                    $this->message['profile_update']['error'][] = __('This email is already used by another user. Please try a different one.', 'profile');
                else
                    wp_update_user(array('ID' => $current_user->ID, 'user_email' => sanitize_email($_POST['email'])));
            }
            
            if(!empty($_POST['first_name']))
                update_user_meta($current_user->ID, 'first_name', esc_attr($_POST['first_name']));
            
            if(!empty($_POST['last_name']))
                update_user_meta($current_user->ID, 'last_name', esc_attr($_POST['last_name']));
            
            if(!empty($_POST['display_name']))
            {
                wp_update_user(array('ID' => $current_user->ID, 'display_name' => esc_attr($_POST['display_name'])));
                update_user_meta($current_user->ID, 'display_name' , esc_attr($_POST['display_name']));
            }
            
            if(!empty($_POST['description']))
                update_user_meta($current_user->ID, 'description', esc_attr($_POST['description']));
        }
    }
    
    public function User_Login_Info()
    {
        global $current_user;
        
        get_currentuserinfo();
        
        if(is_user_logged_in()):
            ?>
            <h2 style="float: right; margin: 20px 0 0 0; clear: both;">
                Logged in as <a href="<?php echo $this->general_settings['kmi_user_control_profile_url']; ?>"><?php echo $current_user->display_name; ?></a> 
                [ <a href="<?php echo wp_logout_url(get_permalink()); ?>">Logout</a> ]
            </h2>
            <?php
        endif;
    }
    
    public function Login_Form()
    {
        $args = array('redirect' => $this->general_settings['kmi_user_control_login_url']);
        
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
                Welcome <a href="<?php echo $this->general_settings['kmi_user_control_profile_url']; ?>"><?php echo $display_name; ?></a> 
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
            echo '<p class="success">Thank you for registering in our site. Please check your email for the password.</p>';
        
        ?>
        <h4>Create an account for this site.</h4>
        <form id="loginform" action="<?php echo site_url('wp-login.php?action=register', 'login_post'); ?>" method="POST">
            <p class="login-username">
                <label for="user_login">Username (required)</label>
                <input type="text" name="user_login" id="user_login" class="input" value="" size="20" />
            </p>
            <p class="login-email">
                <label for="user_email">Email (required)</label>
                <input type="email" name="user_email" id="user_email" class="input" value="" size="20" />
                <?php do_action('register_form'); ?>
            </p>
            <p class="login-submit">
		<input type="submit" name="wp-submit" id="wp-submit" class="button-primary left" value="Register" />
            </p>
            <input type="hidden" name="redirect_to" value="<?php echo esc_url($this->general_settings['kmi_user_control_register_url']); ?>?register=true" />
            <input type="hidden" name="user-cookie" value="1" />
        </form>
        <?php
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
                $errors->add('expired_key', __('Key has expired. Go <a href="'.esc_url($this->general_settings['kmi_user_control_reset_password_url']).'">here</a> to request new key.'));
            else
                $errors->add('invalid_key', __('Invalid key. Go <a href="'.esc_url($this->general_settings['kmi_user_control_reset_password_url']).'">here</a> to request new key.'));
	}
        
        if(isset($_POST['wp-submit']) && $_POST['pass1'] != $_POST['pass2'])
            $errors->add('password_reset_mismatch', __('The passwords did not match.'));
        
        do_action('validate_password_reset', $errors, $user);
        
        if(!$errors->get_error_code() && !empty($_POST['pass1']))
        {
            
            reset_password($user, $_POST['pass1']);
            echo __('<p class="success">Your password has been reset.').' <a href="'.esc_url($this->general_settings['kmi_user_control_login_url']).'">'.__('Log in').'</a></p>';
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
        
        if(count($this->message['profile_update']['error']) > 0)
        {
            ?>
            <p class="error">
                <?php
                foreach($this->message['profile_update']['error'] as $error)
                {
                    echo $error.'<br/>';
                }
                ?>
            </p>
            <?php
            // Reset the profile update message
            $this->message['profile_update'] = array();
        }
        else if(isset($_POST['updateuser']) && $_POST['action'] == 'update-user')
        {
            ?>
            <p class="success">User profile successfully updated.</p>
            <?php
        }
        
        if(is_user_logged_in()):
            ?>
            <form method="post" id="adduser" action="">
                <p class="form-username">
                    <label for="first-name"><?php _e('First Name', 'profile'); ?></label>
                    <input class="text-input half-width" name="first_name" type="text" id="first-name" value="<?php echo $current_user->first_name; //the_author_meta( 'first_name', $current_user->ID ); ?>" />
                </p><!-- .form-username -->
                <p class="form-username">
                    <label for="last-name"><?php _e('Last Name', 'profile'); ?></label>
                    <input class="text-input half-width" name="last_name" type="text" id="last-name" value="<?php echo $current_user->last_name; //the_author_meta( 'last_name', $current_user->ID ); ?>" />
                </p><!-- .form-username -->
                <p class="form-display_name">
                    <label for="display_name"><?php _e('Display name publicly as') ?></label>
                    <select name="display_name" id="display_name" class="half-width"><br/>
                        <?php
                            $public_display = array();
                            $public_display['display_nickname']  = sanitize_text_field($current_user->nickname);
                            $public_display['display_username']  = sanitize_text_field($current_user->user_login);

                            if(!empty($current_user->first_name))
                                $public_display['display_firstname'] = sanitize_text_field($current_user->first_name);

                            if(!empty($current_user->last_name))
                                $public_display['display_lastname'] = sanitize_text_field($current_user->last_name);

                            if(!empty($current_user->first_name) && !empty($current_user->last_name))
                            {
                                $public_display['display_firstlast'] = sanitize_text_field($current_user->first_name).' '.sanitize_text_field($current_user->last_name);
                                $public_display['display_lastfirst'] = sanitize_text_field($current_user->last_name).' '.sanitize_text_field($current_user->first_name);
                            }

                            if(!in_array($current_user->display_name, $public_display)) // add display name if it isn't existed
                                $public_display['display_displayname'] = sanitize_text_field($current_user->display_name);

//                            $public_display = array_map('trim', $public_display);
                            $public_display = array_unique($public_display);

                            $display_name = !empty($_POST['display_name']) ? sanitize_text_field($_POST['display_name']) : $current_user->display_name;

                            foreach($public_display as $id => $item):
                                ?><option <?php selected($display_name, $item); ?>><?php echo $item; ?></option><?php
                            endforeach;
                        ?>
                    </select>
                </p><!-- .form-display_name -->
                <p class="form-email">
                    <label for="email"><?php _e('E-mail *', 'profile'); ?></label>
                    <input class="text-input half-width" name="email" type="text" id="email" value="<?php echo $current_user->user_email; //the_author_meta( 'user_email', $current_user->ID ); ?>" />
                </p><!-- .form-email -->
                <p class="form-url">
                    <label for="url"><?php _e('Website', 'profile'); ?></label>
                    <input class="text-input half-width" name="url" type="text" id="url" value="<?php echo $current_user->user_url; //the_author_meta( 'user_url', $current_user->ID ); ?>" />
                </p><!-- .form-url -->
                <p class="form-password">
                    <label for="pass1"><?php _e('Password *', 'profile'); ?> </label>
                    <input class="text-input half-width" name="pass1" type="password" id="pass1" />
                </p><!-- .form-password -->
                <p class="form-password">
                    <label for="pass2"><?php _e('Repeat Password *', 'profile'); ?></label>
                    <input class="text-input half-width" name="pass2" type="password" id="pass2" />
                </p><!-- .form-password -->
                <p class="form-textarea">
                    <label for="description"><?php _e('Biographical Information', 'profile') ?></label>
                    <textarea name="description" id="description" class="half-width vertical-resize" rows="3" cols="50"><?php echo $current_user->description; //the_author_meta( 'description', $current_user->ID ); ?></textarea>
                </p><!-- .form-textarea -->
                <?php
                    //action hook for plugin and extra fields
                    do_action('edit_user_profile', $current_user);
                ?>
                <p class="form-submit">
                    <input name="updateuser" type="submit" id="updateuser" class="submit button" value="<?php _e('Update', 'profile'); ?>" />
                    <?php wp_nonce_field('update-user_'. $current_user->ID) ?>
                    <input name="action" type="hidden" id="action" value="update-user" />
                </p><!-- .form-submit -->
            </form>
            <?php
        else:
            echo '<h3>Sorry, you must first <a href="'.esc_url($this->general_settings['kmi_user_control_login_url']).'">log in</a> to view this page. You can <a href="'.esc_url($this->general_settings['kmi_user_control_register_url']).'">register free here</a>.</h3>';
        endif;
    }
    
    public function Add_LostPassword_Register_Link()
    {
	return '<a href="'.esc_url($this->general_settings['kmi_user_control_reset_password_url']).'" style="margin-left: 6px;">Forgot your password?</a> | <a href="'.esc_url($this->general_settings['kmi_user_control_register_url']).'">Register</a>';
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
    
    public function Register_Failed_Redirect($sanitized_user_login, $user_email, $errors)
    {
        // this line is copied from register_new_user function of wp-login.php
        $errors = apply_filters('registration_errors', $errors, $sanitized_user_login, $user_email);
        //this if check is copied from register_new_user function of wp-login.php
        if($errors->get_error_code())
        {
            //setup your custom URL for redirection
            $referrer = esc_url($this->general_settings['kmi_user_control_register_url']); // current registration page
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
    
    public function Remove_Admin_Bar()
    {
        if(!current_user_can('administrator') && !is_admin())
            show_admin_bar(false);
    }
    
    public function Restrict_Admin_Pages()
    {
        if(is_admin() && !current_user_can('administrator')
            && !(defined('DOING_AJAX') && DOING_AJAX))
        {
            wp_redirect(site_url());
            exit;
        }
    }
    
    /*
     * Adding css and scripts in to the front end pages
     */
    public function Add_Styles_And_Scripts()
    {
        if(!wp_style_is('kmi_global_style', 'registered'))
        {
            wp_register_style('kmi_global_style', plugins_url('css/global.css', __FILE__));
        }
        
        if(!wp_style_is('kmi_global_style', 'enqueued'))
        {
            wp_enqueue_style('kmi_global_style');
        }
    }
    
    /*
     * Add shortcode hooks
     */
    private function Setup_Shortcodes()
    {
        // login UI
        add_shortcode('kmi_user_login_info', array($this, 'User_Login_Info'));
        add_shortcode('kmi_login_form', array($this, 'Login_Form'));
        // registration UI
        add_shortcode('kmi_registration_form', array($this, 'Registration_Form'));
        // reset password UI
        add_shortcode('kmi_reset_password_form', array($this, 'Reset_Password_Form'));
        // change password UI
        add_shortcode('kmi_change_password_form', array($this, 'Change_Password_Form'));
        // profile UI
        add_shortcode('kmi_profile_form', array($this, 'Profile_Form'));
    }
    
    /*
     * Add action hooks
     */
    private function Setup_Action_Hooks()
    {
        // Add option page in the admin panel
        add_action('admin_menu', array($this, 'Add_User_Control_Option_Page'));
        // Register the settings to use on the user control pages
        add_action('admin_init', array($this, 'Register_User_Control_Settings'));
        // Register the settings to use on the user control pages
        add_action('init', array($this, 'Initialization'));
        // Add forgot password and register link on login form
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
        // Add css and scripts
        add_action('wp_enqueue_scripts', array($this, 'Add_Styles_And_Scripts'));
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

$kmi_user_control = new KMI_User_Control();