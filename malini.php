<?php
/**
 * Malini.
 *
 * @author      Caffeina
 * @copyright   2019 Caffeina
 * @license     MIT
 *
 * @wordpress-plugin
 * Plugin Name: Malini
 * Plugin URI:  https://github.com/caffeinalab/malini
 * Description: Malini is an extensible content decoration and normalization library for WordPress entities.
 * Version:     1.0.0
 * Author:      Caffeina
 * Author URI:  https://caffeina.com
 * Text Domain: malini
 * License:     MIT
 */
defined('ABSPATH') or die('No script kiddies please!');

require_once __DIR__.'/vendor/autoload.php';

add_action('init', function () {
    malini()->boot();
    \Malini\Updater::updateService();
});
