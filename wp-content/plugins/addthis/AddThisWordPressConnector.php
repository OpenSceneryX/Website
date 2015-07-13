<?php
/**
 * +--------------------------------------------------------------------------+
 * | Copyright (c) 2008-2015 AddThis, LLC                                     |
 * +--------------------------------------------------------------------------+
 * | This program is free software; you can redistribute it and/or modify     |
 * | it under the terms of the GNU General Public License as published by     |
 * | the Free Software Foundation; either version 2 of the License, or        |
 * | (at your option) any later version.                                      |
 * |                                                                          |
 * | This program is distributed in the hope that it will be useful,          |
 * | but WITHOUT ANY WARRANTY; without even the implied warranty of           |
 * | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the            |
 * | GNU General Public License for more details.                             |
 * |                                                                          |
 * | You should have received a copy of the GNU General Public License        |
 * | along with this program; if not, write to the Free Software              |
 * | Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA |
 * +--------------------------------------------------------------------------+
 */

require_once('AddThisCmsConnectorInterface.php');

if (!class_exists('AddThisWordpressConnector')) {
    Class AddThisWordpressConnector {
        // implements AddThisCmsConnectorInterface {

        static $settingsVariableName = 'addthis_settings';
        static $pluginVersion = '5.0.8';
        static $settingsPageId = 'addthis_social_widget';
        static $anonymousProfileIdPrefix = 'wp';
        static $pluginName = "AddThis Sharing Buttons";
        static $productPrefix = 'wpp';
        static $cmsName = "WordPress";
        protected $configs = null;

        protected $defaultConfigs = array(
            'addthis_plugin_controls'      => 'WordPress',
        );

        public $simpleConfigUpgradeMappings = array(
            array(
                'current' => array('addthis_above_showon_home', 'addthis_below_showon_home'),
                'deprecated' => array('addthis_showonhome'),
            ),
            array(
                'current' => array('addthis_above_showon_pages', 'addthis_below_showon_pages'),
                'deprecated' => array('addthis_showonpages'),
            ),
            array(
                'current' => array('addthis_above_showon_categories', 'addthis_below_showon_categories'),
                'deprecated' => array('addthis_showoncats'),
            ),
            array(
                'current' => array('addthis_above_showon_archives', 'addthis_below_showon_archives'),
                'deprecated' => array('addthis_showonarchives'),
            ),
            array(
                'current' => array('addthis_above_showon_posts', 'addthis_below_showon_posts'),
                'deprecated' => array('addthis_showonposts'),
            ),
            array(
                'current' => array('addthis_addressbar'),
                'deprecated' => array('addthis_copytracking2', 'addthis_copytracking1'),
            ),
            array(
                'current' => array('addthis_profile'),
                'deprecated' => array('profile', 'pubid'),
            ),
        );

        static function getPluginVersion() {
            return self::$pluginVersion;
        }

        static function getCmsName() {
            return self::$cmsName;
        }

        static function getPluginName() {
            return self::$pluginName;
        }

        static function getSettingsPageId() {
            return self::$settingsPageId;
        }

        static function getCmsVersion() {
            $version =  get_bloginfo('version');
            return $version;
        }

        static function getCmsMinorVersion() {
            $version =  (float)substr(self::getCmsVersion(),0,3);
            return $version;
        }

        static function getAnonymousProfileIdPrefix() {
            return self::$anonymousProfileIdPrefix;
        }

        static function getProductVersion() {
            $productVersion = self::$productPrefix . '-' . self::getPluginVersion();
            return $productVersion;
        }

        /**
         * the folder name for the AddThis plugin - OMG why is this hard coded?!?
         * @return string
         */
        public function getPluginFolder(){
            return 'addthis';
        }

        /**
         * gives you the base URL for our plugin
         * @return string
         */
        public function getPluginUrl(){
            $url = apply_filters(
                'addthis_files_uri',
                plugins_url()
            );
            $url .= '/' . $this->getPluginFolder();
            return $url;
        }

        /**
         * gives you the base URL for our plugin's JavaScript
         * @return string
         */
        public function getPluginJsFolderUrl() {
            $url = $this->getPluginUrl() . '/js/';
            return $url;
        }

        /**
         * gives you the base URL for our plugin's CSS
         * @return string
         */
        public function getPluginCssFolderUrl() {
            $url = $this->getPluginUrl() . '/css/';
            return $url;
        }

        /**
         * gives you the base URL for our plugin's images
         * @return string
         */
        public function getPluginImageFolderUrl() {
            $url = $this->getPluginUrl() . '/img/';
            return $url;
        }

        public function getSettingsPageUrl() {
            $url = admin_url("options-general.php?page=" . $this->getSettingsPageId());
            return $url;
        }

        public function getDefaultConfigs() {
            return $this->defaultConfigs;
        }

        public function getConfigs($cache = false) {
            if ($this->isPreviewMode()) {
                $this->configs = get_transient(self::$settingsVariableName);
            } elseif (!$cache || is_null($this->configs)) {
                $this->configs = get_option(self::$settingsVariableName);
            }

            if (!is_array($this->configs)) {
                $this->configs = null;
            }

            return $this->configs;
        }

        public function saveConfigs($configs = null) {
            if (!is_array($configs)) {
                $configs = $this->configs;
            }

            if (is_array($configs)) {
                update_option(self::$settingsVariableName, $configs);
                $this->configs = $this->getConfigs();
            }

            return $this->configs;
        }

        /**
         * checks if you're in preview mode
         * @return boolean true if in preview, false otherwise
         */
        public function isPreviewMode() {
            if (isset($_GET['preview']) && $_GET['preview'] == 1) {
                return true;
            }

            return false;
        }

        public function getSharingButtonLocations() {
            $types = array(
                'above',
                'below',
                'sidebar',
            );
            return $types;
        }

        /**
         * Returns an array of template options generlized without location info
         * @return array[] an array of associative arrays
         */

        public function getContentTypes() {
            $options = array(
                array(
                    'fieldName'    => 'home',
                    'displayName'  => 'Homepage',
                    'explanation'  => 'Includes both the blog post index page (home.php or index.php) and any static page set to be your front page under Settings->Reading->Front page displays.',
                ),
                array(
                    'fieldName'    => 'posts',
                    'displayName'  => 'Posts',
                    'explanation'  => 'Also known as articles or blog posts.',
                ),
                array(
                    'fieldName'    => 'pages',
                    'displayName'  => 'Pages',
                    'explanation'  => 'Often used to present static information about yourself or your site where the date published is less revelant than with posts.',
                ),
                array(
                    'fieldName'    => 'archives',
                    'displayName'  => 'Archives',
                    'explanation'  => 'A Category, Tag, Author or Date based view.',
                ),
                array(
                    'fieldName'    => 'categories',
                    'displayName'  => 'Categories',
                    'explanation'  => 'A view that displays costs filled under a specific category.',
                ),
                array(
                    'fieldName'    => 'excerpts',
                    'displayName'  => 'Excerpts',
                    'explanation'  => 'A condensed description of your post or page. These are often displayed in search results, RSS feeds, and sometimes on Archive or Category views. Important: Excerpts will only work some of the time with some themes, depending on how that theme retrieves your content.',
                ),
            );
            return $options;
        }

        public function isUpgrade() {
            $this->getConfigs(true);
            if (   !isset($this->configs['addthis_plugin_version'])
                || $this->configs['addthis_plugin_version'] != self::$pluginVersion
            ) {
                return true;
            }

            return false;
        }

        public function upgradeConfigs() {
            $this->getConfigs(true);
            if (is_null($this->configs)) {
                return $this->configs;
            }

            $this->configs['addthis_plugin_version'] = self::$pluginVersion;

            foreach ($this->simpleConfigUpgradeMappings as $configUpgradeMapping) {
                foreach ($configUpgradeMapping['current'] as $currentFieldName) {
                    foreach ($configUpgradeMapping['deprecated'] as $deprecatedFieldName) {
                        $this->getFromPreviousConfig($deprecatedFieldName, $currentFieldName);
                    }
                }
            }

            // if AddThis above button was enabled
            if (   !isset($this->configs['addthis_above_enabled'])
                && isset($this->configs['above'])
            ) {
                if ($this->configs['above'] == 'none' || $this->configs['above'] == 'disable') {
                    $this->configs['addthis_above_enabled'] = false;
                } else {
                    $this->configs['addthis_above_enabled'] = true;
                }
            }

            // if AddThis below button was enabled
            if (   !isset($this->configs['addthis_below_enabled'])
                && isset($this->configs['below'])
            ) {
                if ($this->configs['below'] == 'none' || $this->configs['below'] == 'disable') {
                    $this->configs['addthis_below_enabled'] = false;
                } else {
                    $this->configs['addthis_below_enabled'] = true;
                }
            }

            if (   isset($this->configs['addthis_for_wordpress'])
                && $this->configs['addthis_for_wordpress']
                && !isset($this->configs['addthis_plugin_controls'])
            ) {
                $this->configs['addthis_plugin_controls'] = "AddThis";
            }

            $this->saveConfigs();
            return $this->configs;
        }

        private function getFromPreviousConfig($deprecatedFieldName, $currentFieldName) {
            // if we don't have this value, get from a the depricated field
            if (   is_array($this->configs)
                && isset($this->configs[$deprecatedFieldName])
                && !isset($this->configs[$currentFieldName])
            ) {
                $deprecatedValue = $this->configs[$deprecatedFieldName];
                $this->configs[$currentFieldName] = $deprecatedValue;
            }
        }

        /**
         * Evaluates a handle and its source to determine if we should keep it.
         * We want to keep stuff from out plugin, from themes and from core
         * WordPress, but not stuff from other plugins as it can conflict with
         * our code.
         *
         * @param string  $handle     The name given to an enqueued script or
         * @param mixed   $src        style.  This is usually a string with the
         *                            the location of the enqueued script or
         *                            style, relative or absolute. Sometimes
         *                            this is not a string, and it adds CSS code
         *                            to a WordPress generated CSS file.
         * @param string[] $whitelist We will inevitably run into code from
         *                            other plugins that should be included on
         *                            our settings page. For those, their
         *                            handles can be added to this array of
         *                            strings. We've decided to whitelist
         *                            instead of blacklist, as we are likely to
         *                            encounter fewer plugins that add
         *                            functionality to our settings page than
         *                            plugins that behave badly and add unwanted
         *                            code to our page. This also keeps our code
         *                            working (though perhaps without the added
         *                            functionality from another plugin that may
         *                            be desired by the user) instead of
         *                            breaking the page outright.
         *                            Troubleshooting should also be easier, as
         *                            a user is more likely to be aware of which
         *                            of their plugins add functionality on
         *                            their settings pages, rather than which
         *                            ones doesn't play nicely with how they
         *                            enqueue their scripts and styles.
         * @return boolean true when a particular script or style should be
         *                 killed from our settings page, false when it should
         *                 not be killed
         */
        public function evalKillEnqueue($handle, $src, $whitelist = array()) {
            $pluginsFolder = '/wp-content/plugins/';
            $addThisPluginsFolder = $pluginsFolder . $this->getPluginFolder();
            $addThisUrl = $this->getPluginUrl();

            if (!is_string($src)) { return false; }

            if (   !is_string($src) // is the source location a string? keep css if not, cause, for some reason it breaks otherwise
                || in_array($handle, $whitelist) // keep stuff that's in the whitelist
                || substr($handle, 0, 7) === 'addthis' // handle has our prefix
                || substr($src, 0, strlen($addThisPluginsFolder)) === $addThisPluginsFolder // keep relative path stuff from this plugin
                || substr($src, 0, strlen($addThisUrl)) === $addThisUrl //full urls for this plugin
                || (   substr($src, 0, 4) === "/wp-" // keep css for non-plugins
                    && substr($src, 0, strlen($pluginsFolder)) !== $pluginsFolder)
            ) {
                return false;
            }

            return true;
        }

        /**
         * Dequeues unwanted scripts from the HTML page generated by WordPress.
         * This should only be used for our settings page. See the documentation
         * for the evalKillEnqueue function for more details, secifically for
         * more information on the $whitespace variable.
         */
        public function killUnwantedScripts() {
            global $wp_scripts;
            $whitelist = array();

            foreach ($wp_scripts->queue as $handle) {
                $obj = $wp_scripts->registered[$handle];
                $src = $obj->src;
                $kill = $this->evalKillEnqueue($handle, $src, $whitelist);
                if ($kill) {
                    wp_dequeue_script($handle);
                }
            }
        }

        /**
         * Dequeues unwanted styles from the HTML page generated by WordPress.
         * This should only be used for our settings page. See the documentation
         * for the evalKillEnqueue function for more details, secifically for
         * more information on the $whitespace variable.
         */
        public function killUnwantedStyles() {
            global $wp_styles;
            $whitelist = array();

            foreach ($wp_styles->queue as $handle) {
                $obj = $wp_styles->registered[$handle];
                $src = $obj->src;
                $kill = $this->evalKillEnqueue($handle, $src, $whitelist);
                if ($kill) {
                    wp_dequeue_style($handle);
                }
            }
        }

        public function addSettingsPageScripts() {
            $this->getConfigs(true);
            $this->killUnwantedScripts();

            $jsRootUrl = $this->getPluginJsFolderUrl();
            $imgRootUrl = $this->getPluginImageFolderUrl();

            if (   $this->getCmsMinorVersion() >= 3.2
                || $this->assumeLatest()
            ) {
                $optionsJsUrl = $jsRootUrl . 'options-page.32.js';
            } else {
                $optionsJsUrl = $jsRootUrl . 'options-page.js';
            }

            wp_enqueue_script(
                'addthis_options_page_script',
                $optionsJsUrl,
                array('jquery-ui-tabs', 'thickbox')
            );

            if ($this->configs['addthis_plugin_controls'] == 'AddThis') {
                wp_enqueue_script(
                    'addThisScript',
                    $jsRootUrl . 'addthis-for-wordpress.js'
                );

                return;
            }

            wp_enqueue_script('addthis_core', $jsRootUrl . 'core-1.1.1.js');
            wp_enqueue_script('addthis_lr', $jsRootUrl . 'lr.js');
            wp_enqueue_script('addthis_qtip_script', $jsRootUrl . 'jquery.qtip.min.js');
            wp_enqueue_script('addthis_ui_script', $jsRootUrl . 'jqueryui.sortable.js');
            wp_enqueue_script('addthis_selectbox', $jsRootUrl . 'jquery.selectBoxIt.min.js');
            wp_enqueue_script('addthis_jquery_messagebox', $jsRootUrl . 'jquery.messagebox.js');
            wp_enqueue_script('addthis_jquery_atjax', $jsRootUrl . 'jquery.atjax.js');
            wp_enqueue_script('addthis_lodash_script', $jsRootUrl . 'lodash-0.10.0.js');
            wp_enqueue_script('addthis_services_script', $jsRootUrl . 'gtc-sharing-personalize.js');
            wp_enqueue_script('addthis_service_script', $jsRootUrl . 'gtc.cover.js');

            wp_localize_script(
                'addthis_services_script',
                'addthis_params',
                array('img_base' => $imgRootUrl)
            );
            wp_localize_script(
                'addthis_options_page_script',
                'addthis_option_params',
                array(
                    'wp_ajax_url'=> admin_url('admin-ajax.php'),
                    'addthis_validate_action' => 'validate_addthis_api_credentials',
                    'img_base' => $imgRootUrl
                )
            );
        }

        public function addSettingsPageStyles() {
            $this->getConfigs(true);
            $this->killUnwantedStyles();
            $cssRootUrl = $this->getPluginCssFolderUrl();

            wp_enqueue_style('addthis_options_page_style', $cssRootUrl . 'options-page.css');
            wp_enqueue_style('addthis_general_style', $cssRootUrl . 'style.css');

            if ($this->configs['addthis_plugin_controls'] == 'AddThis') {
                return;
            }

            wp_enqueue_style('thickbox');
            wp_enqueue_style('addthis_services_style', $cssRootUrl . 'gtc.sharing-personalize.css');
            wp_enqueue_style('addthis_bootstrap_style', $cssRootUrl . 'bootstrap.css');
            wp_enqueue_style('addthis_widget', 'https://ct1.addthis.com/static/r07/widget114.css');
            wp_enqueue_style('addthis_widget_big', 'https://ct1.addthis.com/static/r07/widgetbig056.css');
        }

        public function addSettingsPage($htmlGeneratingFunction) {
            $hook_suffix = add_options_page(
                'AddThis Sharing Buttons',
                'AddThis Sharing Buttons',
                'manage_options',
                self::$settingsPageId,
                $htmlGeneratingFunction
            );

            $print_scripts_hook = 'admin_print_scripts-' . $hook_suffix;
            $print_styles_hook = 'admin_print_styles-' . $hook_suffix;

            add_action(
                $print_scripts_hook,
                array($this, 'addSettingsPageScripts')
            );
            add_action(
                $print_styles_hook,
                array($this, 'addSettingsPageStyles')
            );

        }

        public function assumeLatest() {
            if (   apply_filters('at_assume_latest', __return_false())
                || apply_filters('addthis_assume_latest', __return_false())
            ) {
                return true;
            }

            return false;
        }

        public function getHomepageUrl() {
            $url = get_option('home');
            return $url;
        }

        public function prepareSubmittedConfigs($input) {
            if (isset($input['addthis_profile'])) {
                $configs['addthis_profile'] = $input['addthis_profile'];
            }

            if (isset($input['addthis_environment'])) {
                $configs['addthis_environment'] = $input['addthis_environment'];
            }

            if (isset($input['addthis_plugin_controls'])) {
                if ($input['addthis_plugin_controls'] == 'WordPress') {
                    $configs['addthis_plugin_controls'] = 'WordPress';
                } else {
                    $configs['addthis_plugin_controls'] = 'AddThis';
                }
            }

            if (isset($input['addthis_twitter_template'])) {
                $configs['addthis_twitter_template'] = $input['addthis_twitter_template'];
            }

            if (isset($input['data_ga_property'])) {
                $configs['data_ga_property'] = $input['data_ga_property'];
            }

            if (isset($input['addthis_language'])) {
                $configs['addthis_language'] = $input['addthis_language'];
            }

            if (isset($input['addthis_rate_us'])) {
                $configs['addthis_rate_us'] = $input['addthis_rate_us'];
                $configs['addthis_rate_us_timestamp'] = time();
            }

            if (isset($input['addthis_config_json'])) {
                $configs['addthis_config_json'] = sanitize_text_field($input['addthis_config_json']);
            }

            if (isset($input['addthis_share_json'])) {
                $configs['addthis_share_json'] = sanitize_text_field($input['addthis_share_json']);
            }

            // All the checkbox fields
            $checkboxFields = array(
                'addthis_508',
                'addthis_addressbar',
                'addthis_append_data',
                'addthis_asynchronous_loading',
                'addthis_bitly',
                'addthis_per_post_enabled',
            );

            foreach ($checkboxFields as $field) {
                if (!empty($input[$field])) {
                    $configs[$field] = true;
                } else {
                    $configs[$field] = false;
                }
            }

            return $configs;
        }

        public function prepareCmsModeSubmittedConfigs($input, $configs) {
            return $configs;
        }
    }
}
