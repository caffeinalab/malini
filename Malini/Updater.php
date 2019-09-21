<?php

namespace Malini;

class Updater 
{

    protected $config;
    protected $github;

    public function __construct()
    {
        $this->config = [
            'slug' => 'malini/malini.php',
            'proper_folder_name' => 'malini'
        ];
        $this->github = [
            'user' => 'caffeinalab',
            'repository' => 'malini'
        ];
    }

    public static function updateService()
    {
        (new static())->bootUpdateService();
    }

    public function bootUpdateService()
    {
        // define the alternative API for updating checking
        add_filter('pre_set_site_transient_update_plugins', [ $this, 'checkVersion' ]);
        // Define the alternative response for information checking
        add_filter('plugins_api', [ $this, 'setPluginInfo' ], 10, 3);
        // reactivate plugin
        add_filter('upgrader_post_install', [ $this, 'postInstall' ], 10, 3);
    }

    private function getPluginData()
    {
        if (isset($this->plugin_data)) {
            return $this->plugin_data;
        }
    
        include_once ABSPATH.'/wp-admin/includes/plugin.php';
    
        $this->plugin_data = get_plugin_data(WP_PLUGIN_DIR.'/'.$this->config['slug']);
        $github_url = parse_url($this->plugin_data['PluginURI']);
        $github_info = explode('/', $github_url['path']);
    
        $this->github = [
          'user' => $github_info[1],
          'repository' => $github_info[2]
        ];
    }

    private function getRepoReleaseInfo()
    {
        // Only do this once
        if (!empty($this->github_api_result)) {
            return;
        }
    
        $transient = get_transient("{$this->github["user"]}_{$this->github["repository"]}_transient_update");
        if ($transient !== false) {
            return $transient;
        }
        // Query the GitHub API
        $url = "https://api.github.com/repos/{$this->github["user"]}/{$this->github["repository"]}/releases";
    
        // Get the results
        $this->github_api_result = wp_remote_retrieve_body(wp_remote_get($url));
        if (!empty($this->github_api_result)) {
            $this->github_api_result = @json_decode($this->github_api_result);
        }
        // Use only the latest release
        if (is_array($this->github_api_result)) {
            $this->github_api_result = $this->github_api_result[0];
        }
        set_transient("{$this->github["user"]}_{$this->github["repository"]}_transient_update", $this->github_api_result, 3.600);
    }

    public function checkVersion($transient)
    {
        if (!empty($transient)&& empty($transient->checked)) {
            return $transient;
        }
        // Get plugin & GitHub release information
        $this->getPluginData();
        $this->getRepoReleaseInfo();
    
        if (!isset($this->github_api_result->tag_name)) {
            return $transient;
        }
        $doUpdate = version_compare($this->github_api_result->tag_name, $transient->checked[$this->config['slug']]);
    
        if ($doUpdate == 1) {
            $package = $this->github_api_result->zipball_url;
            // Include the access token for private GitHub repos
            if (!empty($this->config["access_token"])) {
                $package = add_query_arg([ "access_token" => $this->config["access_token"] ], $package);
            }
    
            $obj = new \StdClass();
            $obj->slug = $this->config["slug"];
            $obj->new_version = $this->github_api_result->tag_name;
            $obj->url = $this->plugin_data["PluginURI"];
            $obj->package = $package;
            $obj->plugin = $this->config["slug"];
            // TODO: Add Malini icons
            $obj->icons = [];
            /*
            $obj->icons = [
                '1x' => '/wp-content/plugins/malini/res/128.png',
                '2x' => '/wp-content/plugins/malini/res/128.png'
            ];
            */
            $transient->response[$this->config["slug"]] = $obj;
        }
        return $transient;
    }

    public function setPluginInfo($false, $action, $response)
    {
        // Get plugin & GitHub release information
        $this->getPluginData();
        $this->getRepoReleaseInfo();
    
        // If nothing is found, do nothing
        if (empty($response->slug) || $response->slug != $this->config["slug"]) {
            return false;
        }
        // Add our plugin information
        $response->last_updated = $this->github_api_result->published_at;
        $response->slug = $this->config["slug"];
        $response->name = $this->plugin_data["Name"];
        $response->plugin_name  = $this->plugin_data["Name"];
        $response->version = $this->github_api_result->tag_name;
        $response->author = $this->plugin_data["AuthorName"];
        $response->homepage = $this->plugin_data["PluginURI"];
    
        $response->sections = [ 'description' =>$this->github_api_result->body ];
        // This is our release download zip file
        $downloadLink = $this->github_api_result->zipball_url;
        $response->download_link = $downloadLink;
        return $response;
    }

    public function postInstall($true, $hook_extra, $result)
    {
        global $wp_filesystem;
        // Move & Activate
        $proper_destination = WP_PLUGIN_DIR.'/'.$this->config['proper_folder_name'];
        $wp_filesystem->move($result['destination'], $proper_destination);
        $result['destination'] = $proper_destination;
        $activate = activate_plugin(WP_PLUGIN_DIR.'/'.$this->config['slug']);
        // Output the update message
        $fail  = __('The plugin has been updated, but could not be reactivated. Please reactivate it manually.', 'github_plugin_updater');
        $success = __('Plugin reactivated successfully.', 'github_plugin_updater');
        echo is_wp_error($activate) ? $fail : $success;
        return $result;
    }

}