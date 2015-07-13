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

if (!class_exists('AddThisConfigs')) {
    Class AddThisConfigs {

        protected $cmsInterface;
        protected $configs = null;
        protected $changedConfigs = false;

        protected $defaultConfigs = array(
            'above'                        => 'large_toolbox',
            'above_custom_more'            => '',
            'above_custom_preferred'       => '',
            'above_custom_services'        => '',
            'above_custom_size'            => '',
            'above_custom_string'          => '',
            'above_sharing'                => 'above-enable-smart-sharing',
            'addthis_508'                  => '',
            'addthis_above_enabled'        => false,
            'addthis_addressbar'           => false,
            'addthis_aftertitle'           => false,
            'addthis_append_data'          => true,
            'addthis_asynchronous_loading' => true,
            'addthis_beforecomments'       => false,
            'addthis_below_enabled'        => false,
            'addthis_bitly'                => false,
            'addthis_config_json'          => '',
            'addthis_environment'          => '',
            'addthis_language'             => '',
            'addthis_per_post_enabled'     => true,
            'addthis_plugin_controls'      => 'AddThis',
            'addthis_profile'              => '',
            'addthis_rate_us'              => '',
            'addthis_share_json'           => '',
            'addthis_sidebar_count'        => '5',
            'addthis_sidebar_enabled'      => false,
            'addthis_sidebar_position'     => 'left',
            'addthis_twitter_template'     => '',
            'atversion'                    => 300,
            'atversion_update_status'      => 0,
            'below'                        => 'large_toolbox',
            'below_custom_more'            => '',
            'below_custom_preferred'       => '',
            'below_custom_services'        => '',
            'below_custom_size'            => '',
            'below_custom_string'          => '',
            'below_sharing'                => 'below-enable-smart-sharing',
            'credential_validation_status' => 0,
            'data_ga_property'             => '',
            'location'                     => 'below',
            'style'                        => addthis_style_default ,
            'toolbox'                      => '',
        );

        public function __construct($cmsInterface) {
            $this->cmsInterface = $cmsInterface;
        }

        public function getDefaultConfigs() {
            $defaultConfigs = array();

            // add all share button location template settings to default to true
            $locationContentTypeLocationFields = $this->getFieldsForContentTypeSharingLocations();

            foreach ($locationContentTypeLocationFields as $field) {
                $optionName = $field['fieldName'];
                $defaultValue = $field['default'];
                settype($defaultValue, $field['dataType']);
                $defaultConfigs[$optionName] = $defaultValue;
            }

            $defaultConfigs = array_merge(
                $this->defaultConfigs,
                $defaultConfigs,
                $this->cmsInterface->getDefaultConfigs()
            );

            return $defaultConfigs;
        }

        public function getDefaultAddThisVersion() {
            $defaultConfigs = $this->cmsInterface->getDefaultConfigs();
            if (!empty($defaultConfigs['atversion'])) {
                $version = $defaultConfigs['atversion'];
                return $version;
            }

            $defaultConfigs = $this->getDefaultConfigs();
            if (!empty($defaultConfigs['atversion'])) {
                $version = $defaultConfigs['atversion'];
                return $version;
            }

            return false;
        }

        public function getAddThisVersion() {
            if (!is_array($this->configs)) {
                $this->getConfigs();
            }

            if (   isset($this->configs['atversion_update_status'])
                && $this->configs['atversion_update_status']
                && !empty($this->configs['atversion'])
            ) {
                $version = $this->configs['atversion'];
            } else {
                $version = $this->getDefaultAddThisVersion();
            }

            return $version;
        }

        public function getConfigs() {
            if ($this->cmsInterface->isUpgrade()) {
                $this->configs = $this->cmsInterface->upgradeConfigs();
            }

            $this->configs = $this->setDefaultConfigs();

            if (!empty($this->configs['addthis_twitter_template'])) {
                $this->configs['addthis_twitter_template'] = $this->getFirstTwitterUsername($this->configs['addthis_twitter_template']);
            }

            return $this->configs;
        }

        public function saveConfigs($configs = null) {
            if (!is_array($this->configs)) {
                $this->getConfigs();
            }

            if (is_array($configs) && is_array($this->configs)) {
                $this->configs = array_merge($this->configs, $configs);
            } elseif (is_array($configs) && !is_array($this->configs)) {
                $this->configs = $configs;
            }

            if (!is_null($this->configs)) {
                if (!empty($this->configs['addthis_twitter_template'])) {
                    $this->configs['addthis_twitter_template'] = $this->getFirstTwitterUsername($this->configs['addthis_twitter_template']);
                }

                $this->configs['atversion'] = $this->getAddThisVersion();

                $this->configs = $this->cmsInterface->saveConfigs($this->configs);
            }

            $this->changedConfigs = false;
            return $this->configs;
        }

        public function saveSubmittedConfigs($input) {
            $configs = $this->cmsInterface->prepareSubmittedConfigs($input);

            if(   isset($options['addthis_plugin_controls'])
               && $this->configs['addthis_plugin_controls'] != "AddThis"
            ) {
                $configs = $this->cmsInterface->prepareCmsModeSubmittedConfigs($input, $configs);
            }

            return $this->saveConfigs($configs);
        }

        private function setDefaultConfigs() {
            $this->configs = $this->cmsInterface->getConfigs(true);

            if (!is_array($this->configs)) {
                $this->configs = $this->getDefaultConfigs();
                $this->changedConfigs = true;
            } else {
                foreach($this->getDefaultConfigs() as $fieldName => $defaultValue) {
                    if (!isset($this->configs[$fieldName])) {
                        $this->configs[$fieldName] = $defaultValue;
                        $this->changedConfigs = true;
                    }
                }

                $this->configs['atversion'] = $this->getAddThisVersion();
            }

            $twoWeeksAgo = time() - (60 * 60 * 24 * 7 * 2);
            if (   isset($this->configs['addthis_rate_us_timestamp'])
                && $this->configs['addthis_rate_us_timestamp'] < $twoWeeksAgo
                && $this->configs['addthis_rate_us'] != 'rated'
            ) {
                $this->configs['addthis_rate_us'] = '';
            }

            if ($this->changedConfigs) {
                $this->configs = $this->saveConfigs();
            }

            return $this->configs;
        }

        public function getFieldsForContentTypeSharingLocations($requestedContentType = null, $requestedLocation = null) {
            $buttonLocations = $this->cmsInterface->getSharingButtonLocations();
            $contentTypes = $this->cmsInterface->getContentTypes();
            $fields = array();

            foreach ($buttonLocations as $location) {
                if ($requestedLocation !== null && $requestedLocation !== $location) {
                    continue;
                }

                foreach ($contentTypes as $template) {
                    if ($requestedContentType !== null && $requestedContentType !== $template['fieldName']) {
                        continue;
                    }

                    if ($location == 'sidebar' && $template['fieldName'] == 'excerpts') {
                        continue;
                    }

                    $fieldName = "addthis_" . $location . "_showon_" . $template['fieldName'];
                    $variableName = $location . "_" . $template['fieldName'];
                    $displayName = $template['displayName'];
                    $explanation = $template['explanation'];

                    $fieldInfo = array(
                        'fieldName'    => $fieldName,
                        'variableName' => $variableName,
                        'displayName'  => $displayName,
                        'explanation'  => $explanation,
                        'location'     => $location,
                        'template'     => $template,
                        'fieldType'    => 'checkbox',
                        'dataType'     => 'boolean',
                        'default'      => true,
                    );
                    $fields[] = $fieldInfo;
                }
            }

            return $fields;
        }

        public function getProfileId() {
            $this->getConfigs();
            if(   isset($this->configs['addthis_profile'])
               && !empty($this->configs['addthis_profile'])
            ) {
                return $this->configs['addthis_profile'];
            }

            return '';
        }

        public function getAnonymousProfileId() {
            $this->getConfigs();
            if(   !isset($this->configs['addthis_anonymous_profile'])
               || !$this->configs['addthis_anonymous_profile']
            ) {
                $prefix = $this->cmsInterface->getAnonymousProfileIdPrefix();
                $url = $this->cmsInterface->getHomepageUrl();
                $postfix = hash_hmac('md5', $url, 'addthis');
                $this->configs['addthis_anonymous_profile'] = $prefix . '-' . $postfix;
                $this->saveConfigs();
            }

            return $this->configs['addthis_anonymous_profile'];
        }


        public function getUsableProfileId() {
            if ($this->getProfileId()) {
                return $this->getProfileId();
            }

            return $this->getAnonymousProfileId();
        }

        public function createAddThisShareVariable() {
            if (!is_array($this->configs)) {
                $this->getConfigs();
            }

            $addThisShareVariable = array();

            if (!empty($this->configs['addthis_twitter_template'])) {
                $addThisShareVariable['passthrough']['twitter']['via'] = esc_js($this->configs['addthis_twitter_template']);
            }

            if (!empty($this->configs['addthis_bitly'])) {
                $addThisShareVariable['url_transforms']['shorten']['twitter'] = 'bitly';
                $addThisShareVariable['shorteners']['bitly'] = new stdClass();
            }

            // this should happen last!
            if (!empty($this->configs['addthis_share_json'])) {
                $json = $this->configs['addthis_share_json'];
                $fromJson = json_decode($json, true);
                if (is_array($fromJson)) {
                  $addThisShareVariable = array_merge($addThisShareVariable, $fromJson);
                }
            }

            return $addThisShareVariable;
        }

        public function createAddThisConfigVariable() {
            if (!is_array($this->configs)) {
                $this->getConfigs();
            }

            $addThisConfigVariable = array();

            if (!empty($this->configs['data_ga_property']) ){
                $addThisConfigVariable['data_ga_property'] = $this->configs['data_ga_property'];
                $addThisConfigVariable['data_ga_social'] = true;
            }

            if (   isset($this->configs['addthis_language'])
                && strlen($this->configs['addthis_language']) == 2
            ) {
                $addThisConfigVariable['ui_language'] = $this->configs['addthis_language'];
            }

            if (isset($this->configs['atversion'])) {
                $addThisConfigVariable['ui_atversion'] = $this->configs['atversion'];
            }

            $simpleCheckboxOptions = array(
                array(
                    'cmsConfigName'      => 'addthis_append_data',
                    'variableConfigName' => 'data_track_clickback',
                ),
                array(
                    'cmsConfigName'      => 'addthis_addressbar',
                    'variableConfigName' => 'data_track_addressbar',
                ),
                array(
                    'cmsConfigName'      => 'addthis_508',
                    'variableConfigName' => 'ui_508_compliant',
                ),
            );

            foreach ($simpleCheckboxOptions as $option) {
                if (!empty($this->configs[$option['cmsConfigName']])) {
                    $addThisConfigVariable[$option['variableConfigName']] = true;
                }
            }

            // this should happen last!
            if (!empty($this->configs['addthis_config_json'])) {
                $json = $this->configs['addthis_config_json'];
                $fromJson = json_decode($json, true);
                if (is_array($fromJson)) {
                  $addThisConfigVariable = array_merge($addThisConfigVariable, $fromJson);
                }
            }

            if(   isset($this->configs['addthis_plugin_controls'])
               && $this->configs['addthis_plugin_controls'] != "AddThis"
            ) {
                $addThisConfigVariable['ignore_server_config'] = true;
            }

            return $addThisConfigVariable;
        }

        public function getFirstTwitterUsername($input)
        {
            $twitter_username = '';
            preg_match_all('/@(\w+)\b/i', $input, $twitter_via_matches);
            if (count($twitter_via_matches[1]) == 0) {
                //To handle strings without @
                preg_match_all('/(\w+)\b/i', $input, $twitter_via_refined_matches);
                if (count($twitter_via_refined_matches[1]) > 0) {
                   $twitter_username = $twitter_via_refined_matches[1][0];
                }
            } else {
                $twitter_username = $twitter_via_matches[1][0];
            }

            return $twitter_username;
        }

        public function getAddThisPluginInfoJson() {
            if (!is_array($this->configs)) {
                $this->getConfigs();
            }

            $pluginInfo = array();
            $pluginInfo['cms_name'] = $this->cmsInterface->getCmsName();
            $pluginInfo['cms_version'] = $this->cmsInterface->getCmsVersion();
            $pluginInfo['plugin_name'] = $this->cmsInterface->getPluginName();
            $pluginInfo['plugin_version'] = $this->cmsInterface->getPluginVersion();
            $pluginInfo['plugin_mode'] = $this->configs['addthis_plugin_controls'];

            $json = json_encode($pluginInfo);
            return $json;
        }
    }
}