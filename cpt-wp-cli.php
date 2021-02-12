<?php

/**
 * Plugin Name: Custom Post Types from WP-CLI
 * Plugin URI: https://github.com/stfn00/cpt-wp-cli
 * Description: Generates PHP code for registering a Custom Post Type in plugin with WP-CLI.
 * Version: 1.0.0
 * Author: Stefano Iachetta
 * Author URI: https://github.com/stfn00
 * Text Domain: cpt-wp-cli
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('WP_CLI')) return;

if (!class_exists('CPT_WP_CLI')) :

    class CPT_WP_CLI
    {
        // The plugin version number.
        var $version = '1.0.0';

        /**
         * __construct
         *
         * A dummy constructor to ensure CPT_WP_CLI is only setup once.
         *
         * @since	1.0.0
         *
         * @param	void
         * @return	void
         */
        function __construct()
        {
            // Do nothing.
        }

        /**
         * initialize
         *
         * Sets up the CPT_WP_CLI plugin.
         *
         * @since	1.0.0
         *
         * @param	void
         * @return	void
         */
        function initialize()
        {
            // Define constants.
            $this->define('CPT_WP_CLI', true);
            $this->define('CPT_WP_CLI_PATH', plugin_dir_path(__FILE__));
            $this->define('CPT_WP_CLI_BASENAME', plugin_basename(__FILE__));
            $this->define('CPT_WP_CLI_VERSION', $this->version);
            $this->define('CPT_WP_CLI_MAJOR_VERSION', 1);

            // Add actions.
            add_action('init', array($this, 'init'), 5);
        }

        /**
         * init
         *
         * Completes the setup process on "init" of earlier.
         *
         * @since	1.0.0
         *
         * @param	void
         * @return	void
         */
        function init()
        {
            // Bail early if called directly from functions.php or plugin file.
            if (!did_action('plugins_loaded')) {
                return;
            }

            \WP_CLI::add_command('cpt-wp-cli', 'CPT_WP_CLI_COMMAND');
        }

        /**
         * define
         *
         * Defines a constant if doesnt already exist.
         *
         * @since	1.0.0
         *
         * @param	string $name The constant name.
         * @param	mixed $value The constant value.
         * @return	void
         */
        function define($name, $value = true)
        {
            if (!defined($name)) {
                define($name, $value);
            }
        }
    }



    /**
     * cpt_wp_cli
     *
     * The main function responsible for returning the one true cpt_wp_cli Instance to functions everywhere.
     * Use this function like you would a global variable, except without needing to declare the global.
     *
     * Example: <?php $cpt_wp_cli = cpt_wp_cli(); ?>
     *
     * @since	1.0.0
     *
     * @param	void
     * @return	CPT_WP_CLI
     */
    function cpt_wp_cli()
    {
        global $cpt_wp_cli;

        // Instantiate only once.
        if (!isset($cpt_wp_cli)) {
            $cpt_wp_cli = new CPT_WP_CLI();
            $cpt_wp_cli->initialize();
        }

        return $cpt_wp_cli;
    }

    // Composer autoload.
    $cpt_wp_cli_autoloader = dirname(__FILE__) . '/vendor/autoload.php';
    if (file_exists($cpt_wp_cli_autoloader)) {
        require_once $cpt_wp_cli_autoloader;
    }

    // Include Class CPT_WP_CLI_COMMAND.
    require_once 'inc/class-cpt-wp-cli-command.php';

    // Instantiate.
    cpt_wp_cli();

endif; // class_exists check
