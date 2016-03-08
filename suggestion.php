<?php

class KMI_Suggestion
{
    public function __construct()
    {
        // shortcodes
        // suggestion UI
        add_shortcode('kmi_suggestion_form', array($this, 'Suggestion_Form'));
    }
    
    public function Suggestion_Form()
    {
        if(!is_user_logged_in())
        {
            wp_safe_redirect(site_url('/login-logout/'));
            exit;
        }
        
        $user = wp_get_current_user();
        
        if(isset($_POST['suggestion']['nonce'])
                && wp_verify_nonce($_POST['suggestion']['nonce'], 'user_suggestion_'.$user->ID))
        {
            $subject = strip_tags(trim($_POST['suggestion']['subject']));
            $content = strip_tags(trim($_POST['suggestion']['content']));
            
            if(!empty($subject) && !empty($content))
            {
                if($this->Send_Suggestion($user->user_login, $subject, $content))
                    echo '<p class="success">Suggestion successfully sent.</p>';
                else
                    echo '<p class="error">Suggestion failed to be sent. Please try again later.</p>';
            }
        }
        
        ?>
        <form method="post" name="suggestion_form" action="">
            <p>
                <label for="subject"><?php _e('Subject', 'suggestion'); ?></label>
                <input class="text-input" name="suggestion[subject]" type="text" id="subject" value="" />
            </p>
            <p>
                <label for="suggestion"><?php _e('Suggestion', 'suggestion') ?></label>
                <textarea name="suggestion[content]" id="suggestion" rows="3" cols="50"></textarea>
            </p>
            <p>
                <input type="submit" class="submit button" value="<?php _e('Send Suggestion', 'suggestion'); ?>" />
                <?php wp_nonce_field('user_suggestion_'.$user->ID, 'suggestion[nonce]') ?>
            </p>
        </form>
        <?php
    }
    
    private function Send_Suggestion($user_login, $subject='', $content='')
    {
        //create email message
        $message = __('User: '.$user_login.' have suggestion for the site.')."\r\n\r\n";
        $message .= __('Subject: '.$subject)."\r\n\r\n";
        $message .= __('Suggestion: '.$content)."\r\n\r\n";
        $message .= get_option('siteurl')."\r\n\r\n";
        //send email meassage
        return wp_mail(get_option('admin_email'), sprintf(__('[%s] User suggestion'), get_option('blogname')), $message);
    }
}

$kmi_suggestion = new KMI_Suggestion();