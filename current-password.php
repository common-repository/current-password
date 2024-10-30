<?php
/**
 *
 * @link              https://wpcurrentpassword.com/
 * @since             1.0.0
 * @package           Current_Password
 *
 * @wordpress-plugin
 * Plugin Name:       Current Password?
 * Plugin URI:        https://wpcurrentpassword.com/
 * Description:       Require user's or admin's current password as part of the password changing process on the dashboard.
 * Version:           2.1.1
 * Author:            WP Current Password
 * Author URI:        https://wpcurrentpassword.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       current-password
 * Domain Path:       /languages
 * 
 */

if (!defined('WPINC')) {
    die;
}

define('CURRENT_PASSWORD_VERSION', '2.1.1');
 
class wpCp {

    public function __construct() {

        $this->name = 'Current Password?';
        $this->text_domain = 'current-password';
        $this->version = CURRENT_PASSWORD_VERSION;

        add_action('admin_init',                        array($this, 'wpcpLoadTranslations'),                                  1);
        add_action('admin_init',                        array($this, 'wpcpParsePage'),                                         1);
        add_action('admin_footer',                      array($this, 'wpcpJS'),                                               10);
        add_action('in_admin_footer',                   array($this, 'wpcpFlushPageParse'),                                 9999);
        add_action('user_profile_update_errors',        array($this, 'wpcpPasswordChangeErrors'),                           1, 3);
        add_filter('wp_pre_insert_user_data',           array($this, 'wpcpRecordUserData'),                                 1, 3);

    }

    /**
	 * Check if page should be secured with the Current or Admin password field.
	 *
	 * @since    2.0.0
	 */  

    public function wpcpSecureThisPage() {

        global $pagenow;

        $pagesToSecure = array(
            'profile.php',
            'user-edit.php',
            'user-new.php'
        );

        if(in_array($pagenow, $pagesToSecure)) {

            return true;

        }

        return false;

    }

    /**
	 * Return the HTML that should be injected.
	 *
	 * @since    2.0.0
	 */  

    public function wpcpHtmlToInject($currentPage) {

        if("profile.php" === $currentPage) {

            $inputTitle = "Current Password";
            $inputLabel = "current";
            $inputName = "wpcp_current_pass";

        } else {

            $inputTitle = "Admin Password";
            $inputLabel = "admin";
            $inputName = "wpcp_admin_pass";

        }

        $inputHtml = false;

        switch($currentPage) {

            case 'user-edit.php':
            case 'profile.php':

                $inputHtml = '<tr class="user-'.$inputName.'-wrap wpcp_input-wrap">' . PHP_EOL;
                $inputHtml .= '<th scope="row"><label for="'.$inputName.'">'.__($inputTitle, $this->text_domain).' <span class="description">'.__('(required)').'</span></label></th>' . PHP_EOL;
                $inputHtml .= '<td>' . PHP_EOL;
                $inputHtml .= '<input name="'.$inputName.'" id="'.$inputName.'" class="regular-text" autocomplete="off" type="password" value="" />' . PHP_EOL;
                $inputHtml .= '<p class="description" id="'.$inputName.'-description">'.__('To change password, please type your '.$inputLabel.' password.', $this->text_domain).'</p>' . PHP_EOL;
                $inputHtml .= '</td>' . PHP_EOL;
                $inputHtml .= '</tr>' . PHP_EOL;
                $inputHtml .= '<tr id="password" class="user-pass1-wrap">';

            break;

            case 'user-new.php':

                $inputHtml = '<table class="form-table" role="presentation">' . PHP_EOL;
                $inputHtml .= '<tr class="form-field form-required">' . PHP_EOL;
                $inputHtml .= '<th scope="row"><label for="'.$inputName.'">'.__($inputTitle, $this->text_domain).' <span class="description">'.__('(required)').'</span></label></th>' . PHP_EOL;
                $inputHtml .= '<td>' . PHP_EOL;
                $inputHtml .= '<input name="'.$inputName.'" id="'.$inputName.'" autocomplete="off" type="password" value="" />' . PHP_EOL;
                $inputHtml .= '<p style="position:absolute;" class="description" id="'.$inputName.'-description">'.__('Type your admin password to create a new user.', $this->text_domain).'</p>' . PHP_EOL;
                $inputHtml .= '</td>' . PHP_EOL;
                $inputHtml .= '</tr>' . PHP_EOL;
                $inputHtml .= '</table>' . PHP_EOL;
                $inputHtml .= '<p class="submit">';

            break;
            
        }

        return $inputHtml;

    }

    /**
	 * Check if plugin is installed as a Must Use plugin.
	 *
	 * @since    1.1.0
	 */  

    public function wpcpIsMustUsePlugin() {

        return (in_array(__FILE__,wp_get_mu_plugins())) ? true : false;

    }

    /**
	 * Get plugin directory.
	 *
	 * @since    1.1.0
	 */  

    public function wpcpPluginDirectory() {

        return ($this->wpcpIsMustUsePlugin()) ? dirname(__FILE__).'/'.$this->text_domain : dirname(__FILE__);

    }

    /**
	 * Loads the translations.
	 *
	 * @since    1.1.0
	 */  

    public function wpcpLoadTranslations() {

        $languageDirectoryAbs = $this->wpcpPluginDirectory().'/languages/';
        $languageDirectoryRel = basename($this->wpcpPluginDirectory()).'/languages/';

        if(file_exists($languageDirectoryAbs) && is_dir($languageDirectoryAbs)) {
            
            load_plugin_textdomain($this->text_domain, false, $languageDirectoryRel);

        }

    }

    /**
	 * Parses the Profile page.
	 *
	 * @since    1.0.0
	 */  

    public function wpcpParsePage($buffer) {
    
        if(!defined('DOING_AJAX') || !DOING_AJAX && $this->wpcpSecureThisPage()) {

            ob_start(array($this, 'wpcpInjectInput'));

        }
        
    }

    /**
	 * Inject the Current/ Admin Password field above the New Password field when parsing the Profile page.
	 *
	 * @since    1.0.0
	 */  

    public function wpcpInjectInput($buffer) {

        if($this->wpcpSecureThisPage()) {

            global $pagenow;

            if($inputHtml = $this->wpcpHtmlToInject($pagenow)) {
                    
                $replaceThis = ("user-new.php" === $pagenow) ? '<p class="submit">' : '<tr id="password" class="user-pass1-wrap">';
                $buffer = str_replace($replaceThis, $inputHtml, $buffer);

            }

        }

        return $buffer;

    }

    /**
	 * Flushes the output buffer after parse.
	 *
	 * @since    1.0.0
	 */  

    public function wpcpFlushPageParse() {

        if($this->wpcpSecureThisPage()) {
        
            ob_flush();

        }

    }

    /**
	 * Reverts back the password if the provided value in the Current/ Admin Password field is not valid.
	 *
	 * @since    1.0.0
	 */  

    function wpcpRecordUserData($userData, $isUpdate, $userId) {

        if($this->wpcpSecureThisPage()) {

            global $pagenow;

            $inputName = ("profile.php" === $pagenow) ? "wpcp_current_pass" : "wpcp_admin_pass";
            $actionName = ("profile.php" === $pagenow) ? "wpcp/profile_password_change" : "wpcp/user_password_change";

            $thisUserObject = get_user_by('id', $userId);
            $currentUserObject = get_user_by('id', get_current_user_id());

            if(isset($_POST[$inputName]) && !empty($_POST[$inputName]) && wp_check_password($_POST[$inputName], $currentUserObject->data->user_pass, get_current_user_id())) {

                do_action($actionName, $userId, $_POST[$inputName], $_POST['pass1']);

            } else {

                $userData['user_pass'] = (!empty($thisUserObject)) ? $thisUserObject->data->user_pass : '';

            }

        }
        
        return $userData;

    }

    /**
	 * Handles the errors of empty or not valid current and admin password.
	 *
	 * @since    1.0.0
	 */  

    function wpcpPasswordChangeErrors(&$errorsObject, $isUpdate, $userObject) {

        if($this->wpcpSecureThisPage()) {

            if(!isset($_POST['pass1']) || empty($_POST['pass1'])) {

                return;

            }

            global $pagenow;

            $inputLabel = ("profile.php" === $pagenow) ? "current" : "admin";
            $inputName = ("profile.php" === $pagenow) ? "wpcp_current_pass" : "wpcp_admin_pass";

            if(!isset($_POST[$inputName]) || empty($_POST[$inputName])) {

                $errorsObject->add($inputName.'_empty', sprintf('<strong>%s</strong>: %s', __('ERROR', $this->text_domain), __('Please enter your '.$inputLabel.' password.', $this->text_domain)));

            }

            $currentUserObject = get_user_by('id', get_current_user_id());
            
            if(isset($_POST[$inputName]) && !empty($_POST[$inputName]) && !wp_check_password($_POST[$inputName], $currentUserObject->data->user_pass, get_current_user_id())) {

                $errorsObject->add($inputName.'_invalid', sprintf('<strong>%s</strong>: %s', __('ERROR', $this->text_domain), __('The provided '.$inputLabel.' password is invalid.', $this->text_domain)));

            }

        }

    }

    /**
	 * Adds plugin javascript funcitonality.
	 *
	 * @since    1.0.0
	 */  

    public function wpcpJS() {

        $wpcpProfilePageJs = <<<HTML

        <script type="text/javascript">

        (function($) {

            'use strict';
            
            $(function() {

                if($('.wpcp_input-wrap').length) {

                    $('.wpcp_input-wrap').hide();

                    $('.wp-generate-pw').on('click', function() {
            
                        $('.wpcp_input-wrap').show();
            
                    });
            
                    $('.wp-cancel-pw').on('click', function() {
            
                        $('.wpcp_input-wrap').hide();
                        $('.wpcp_input-wrap input[type="text"]').val('');
            
                    });

                }

            });

        })(jQuery);

        </script>
            
HTML;

        echo $this->wpcpMinifyInlineJS($wpcpProfilePageJs);

    }

    /**
	 * Utility to minify inline JS.
	 *
	 * @since    1.0.0
	 */  

    public function wpcpMinifyInlineJS($inlineJavascript) {

        /**
         * JS minifier regex pattern & replacement via https://gist.github.com/Rodrigo54/93169db48194d470188f
         */

        $pattern = array(
            /* '#\s*("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')\s*|\s*\/\*(?!\!|@cc_on)(?>[\s\S]*?\*\/)\s*|\s*(?<![\:\=])\/\/.*(?=[\n\r]|$)|^\s*|\s*$#', */
            '#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\'|\/\*(?>.*?\*\/)|\/(?!\/)[^\n\r]*?\/(?=[\s.,;]|[gimuy]|$))|\s*([!%&*\(\)\-=+\[\]\{\}|;:,.<>?\/])\s*#s',
            '#;+\}#',
            '#([\{,])([\'])(\d+|[a-z_][a-z0-9_]*)\2(?=\:)#i',
            '#([a-z0-9_\)\]])\[([\'"])([a-z_][a-z0-9_]*)\2\]#i'
        );
        
        $replacement = array(
            /* '$1', */
            '$1$2',
            '}',
            '$1$3',
            '$1.$3'
        );

        return preg_replace($pattern, $replacement, $inlineJavascript);

    }

}

new wpCp();
?>