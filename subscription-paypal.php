<?php

class KMI_Subscription_Paypal
{
    public function __construct()
    {
        // shortcodes
        // subscription form
        add_shortcode('kmi_subscription_form', array($this, 'Subscription_Form'));
        // paypal payment response handler
        add_shortcode('kmi_IPN_response_hdlr', array($this, 'IPN_Response_Handler'));
        
        // action hooks
        // add paypal payment option page in the settings admin menu
        add_action('admin_menu', array($this, 'Subscription_Option_Menu'));
    }
    
    /*=========================== ACTION METHODS =============================*/
    
    public function Subscription_Option_Menu()
    {
        // set default values
        if(!get_option('kmi_paypal_email'))
            update_option('kmi_paypal_email', get_option('admin_email'));
        
        if(!get_option('kmi_currency'))
            update_option('kmi_currency', 'USD');
        
        if(!get_option('kmi_currency_symbol'))
            update_option('kmi_currency_symbol', '$');
        
        if(!get_option('kmi_shipping_cost'))
            update_option('kmi_shipping_cost', '0');
        
        if(!get_option('kmi_return_url'))
            update_option('kmi_return_url', site_url('/thank-you/'));
        
        if(!get_option('kmi_cancel_url'))
            update_option('kmi_cancel_url', site_url('/subscription/'));
        
        add_options_page('KMI Subscription Options', 'KMI Subscription Options', 'manage_options', 'kmi-subscription-option', array($this, 'Subscription_Option_Page'));
    }
    
    public function Subscription_Option_Page()
    {
        if(isset($_POST['update_option']))
        {
            update_option('kmi_paypal_email', $_POST['paypal_email']);
//            if(!empty($_POST['paypal_live_server']))
            update_option('kmi_paypal_live_server', $_POST['paypal_live_server']);
            update_option('kmi_currency', $_POST['currency']);
            update_option('kmi_currency_symbol', $_POST['currency_symbol']);
            update_option('kmi_shipping_cost', $_POST['shipping_cost']);
            update_option('kmi_return_url', $_POST['return_url']);
            update_option('kmi_cancel_url', $_POST['cancel_url']);

            echo '<div id="message" class="updated">Paypal payment subscription options was updated.</div>';
        }
        
        ?>
        <div class="wrap">
            <h2>KMI Subscription Option</h2>
            
            <form action="" method="POST">
                <div class="postbox">
                    <h3><label for="title"><?php echo (__('KMI Subscription Options', 'WSPSC')); ?></label></h3>
                    <table class="form-table">
                        <tr valign="top">
                            <th scope="row"><?php echo __('Paypal Email Address :', 'WSPSC'); ?></th>
                            <td><input type="text" name="paypal_email" value="<?php echo get_option('kmi_paypal_email'); ?>" size="40" /></td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><?php echo __('Paypal Live Server :', 'WSPSC'); ?></th>
                            <td>
                                <input type="checkbox" name="paypal_live_server" value="checked" <?php echo get_option('kmi_paypal_live_server'); ?> />
                                <?php echo __('If not selected it will use the paypal sandbox test server.', 'WSPSC'); ?>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><?php echo __('Currency :', 'WSPSC'); ?></th>
                            <td>
                                <input type="text" name="currency" value="<?php echo get_option('kmi_currency'); ?>" size="5" />
                                <?php echo __('(e.g. USD, EUR, GBP, AUD)', 'WSPSC'); ?>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><?php echo __('Currency Symbol :', 'WSPSC'); ?></th>
                            <td>
                                <input type="text" name="currency_symbol" value="<?php echo get_option('kmi_currency_symbol'); ?>" size="1" />
                                <?php echo __('(e.g. $, &#163;, &#8364;)', 'WSPSC'); ?>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><?php echo __('Base Shipping Cost :', 'WSPSC'); ?></th>
                            <td>
                                <input type="text" name="shipping_cost" value="<?php echo get_option('kmi_shipping_cost'); ?>" size="5" />
                                <?php echo __('This is the base shipping cost that will be added to the total of individual products shipping cost. Put 0 if you do not want to charge shipping cost or use base shipping cost.', 'WSPSC'); ?>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><?php echo __('Return URL :', 'WSPSC'); ?></th>
                            <td>
                                <input type="text" name="return_url" value="<?php echo get_option('kmi_return_url'); ?>" size="80" /><br />
                                <?php echo __('This is the URL the customer will be redirected to after a successful payment.', 'WSPSC'); ?>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><?php echo __('Cancel URL :', 'WSPSC'); ?></th>
                            <td>
                                <input type="text" name="cancel_url" value="<?php echo get_option('kmi_cancel_url'); ?>" size="80" /><br />
                                <?php echo __('This is the URL the customer will be redirected to after a cancelling payment.', 'WSPSC'); ?>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="submit">
                    <input type="submit" name="update_option" class="button-primary" value="<?php echo __('Update Options &raquo;', 'WSPSC'); ?>" />
                </div>
            </form>
        </div>
        <?php
    }
    
    /*========================== END ACTION METHODS ==========================*/
    
    /*========================== SHORTCODE METHODS ===========================*/
    
    public function Subscription_Form()
    {
        if(!is_user_logged_in())
        {
            wp_safe_redirect(site_url('/login-logout/'));
            exit;
        }
        
        $subscriptions = array(
            'gold_monthly'      => array(
                'type'      => 'Gold Subscription',
                'sub_type'  => 'Monthly',
                'price'     => '7.99'
            ),
            'gold_yearly'       => array(
                'type'      => 'Gold Subscription',
                'sub_type'  => 'Yearly',
                'price'     => '79.99',
                'discount'  => '<span style="color: #ff6600;">(Save over $15.00!)</span>'
            ),
            'premium_monthly'   => array(
                'type'      => 'Premium Subscription',
                'sub_type'  => 'Monthly',
                'price'     => '9.99'
            ),
            'premium_yearly'    => array(
                'type'      => 'Premium Subscription',
                'sub_type'  => 'Yearly',
                'price'     => '99.99',
                'discount'  => '<span style="color: #ff6600;">(Save over $15.00!)</span>'
            )
        );
        
        $server = get_option('kmi_paypal_live_server') != 'checked' ? 'sandbox.' : '';
        
        foreach($subscriptions as $subcription => $fields):
            ?>
            <h2><?php echo $fields['type']; ?></h2>
            <p>
                <strong><?php echo $fields['sub_type']; ?></strong>
                <?php echo '$'.$fields['price']; ?>
                <?php echo !empty($fields['discount']) ? $fields['discount'] : ''; ?>
            </p>
            <form id="paypal_form" class="paypal" action="https://www.<?php echo $server ?>paypal.com/cgi-bin/webscr" method="POST">
                <input name="cmd" type="hidden" value="_xclick" />
                <input type="hidden" name="upload" value="1" />
                <input type="hidden" name="business" value="<?php echo get_option('kmi_paypal_email'); ?>" />
                <input type="hidden" name="item_name" value="<?php echo $fields['type'].' ('.$fields['sub_type'].')'; ?>" />
                <input type="hidden" name="amount" value="<?php echo $fields['price']; ?>" />
                <input type="hidden" name="currency_code" value="<?php echo get_option('kmi_currency'); ?>" />
                <input type="hidden" name="lc" value="US" />
                <input type="hidden" name="rm" value="2" />
                <input type="hidden" name="return" value="<?php echo get_option('kmi_return_url'); ?>" />
                <input type="hidden" name="cancel_return" value="<?php echo get_option('kmi_cancel_url'); ?>" />
                <input type="hidden" name="notify_url" value="<?php echo site_url('/test/'); ?>" />
                <input name="no_note" type="hidden" value="1" />
                <!--<input type="hidden" name="currency_code" id="currency_code" value="'.get_option('kmi_currency').'" />-->
                <input type="hidden" name="no_shipping" id="no_shipping" value="1" />
                <input type="hidden" name="custom" id="no_shipping" value="<?php echo get_current_user_id(); ?>" />
                <input name="<?php echo $subcription; ?>" type="submit" value=""
                    style="width: 113px; height: 26px; cursor: pointer;
                    border: none; background: transparent url('https://www.paypal.com/en_US/i/btn/btn_subscribe_LG.gif');" />
            </form>
            <br/><hr/>
            <?php
        endforeach;
        ?>
        <?php
    }
    
    public function IPN_Response_Handler()
    {
        if(!is_user_logged_in())
        {
            wp_safe_redirect(site_url('/login-logout/'));
            exit;
        }
        
        $current_user = wp_get_current_user();
        
//        foreach($_POST as $key => $value)
//        {
//            echo "{$key}={$value}<br/>";
//        }
        
        if(!empty($_POST['payment_status']) && strtolower($_POST['payment_status']) == 'completed')
        {
            if(!empty($_POST['custom']) && $current_user->ID == $_POST['custom'])
            {
                $member_type = 'Free';
                
                if(strpos($_POST['item_name'], 'Premium') !== false)
                    $member_type = 'Premium';
                elseif(strpos($_POST['item_name'], 'Gold') !== false)
                    $member_type = 'Gold';
                
                $today = date('Y-m-d');
                $date_expire = '0000-00-00';
                
                if(!empty($current_user->kmi_expiry_date) && $current_user->kmi_expiry_date > $today)
                    $today = $current_user->kmi_expiry_date;
                
                if(strpos($_POST['item_name'], 'Yearly') !== false)
                    $date_expire = date('Y-m-d', strtotime(date('Y-m-d', strtotime($today)) . "+1 year"));
                elseif(strpos($_POST['item_name'], 'Monthly') !== false)
                    $date_expire = date('Y-m-d', strtotime(date('Y-m-d', strtotime($today)) . "+1 month"));
                
                update_user_meta($current_user->ID, 'kmi_member_type', $member_type);
                update_user_meta($current_user->ID, 'kmi_expiry_date', $date_expire);
                
                $this->Send_Subscription_Notification($current_user->user_login, $member_type);
                
                $display_name = !empty($current_user->first_name) && !empty($current_user->last_name)
                        ? $current_user->first_name.' '.$current_user->last_name : $current_user->display_name;
                
                ?>
                <h2>Hi <a href="<?php echo site_url('/profile/'); ?>"><?php echo $display_name; ?></a>,</h2>
                <p>
                    Thank you for subscribing <?php echo $member_type; ?> account.<br/>
                    You are now granted some privileges that is not available to the free users.
                </p>
                <?php
            }
            else
                echo '<p class="error">Unrecognized account! Unable to upgrade the account.</p>';
        }
        else
            echo '<p class="error">Failed Transaction!</p>';
    }
    
    /*========================= END SHORTCODE METHODS ========================*/
    
    private function Send_Subscription_Notification($user_login, $member_type)
    {
        //create email message
        $message = __("User: {$user_login} upgrade membership to {$member_type} account.")."\r\n\r\n";
        $message .= get_option('siteurl')."\r\n\r\n";
        //send email meassage
        wp_mail(get_option('admin_email'), sprintf(__('[%s] Membership Upgrade'), get_option('blogname')), $message);
    }
}

$kmi_subscription = new KMI_Subscription_Paypal();