<?php

use Doctrine\Inflector\InflectorFactory;

if (defined('WP_CLI') && WP_CLI) {

    /**
     * Generates code for creating Custom Post Types.
     *
     * @since	1.0.0
     */
    class CPT_WP_CLI_COMMAND
    {
        /**
         * Generates PHP code for registering a Custom Post Type in plugin.
         *
         * The following files are always generated:
         *
         * * `plugin-slug.php` is the main PHP plugin file.
         * * `readme.txt` is the readme file for the plugin.
         * * `.gitignore` tells which files (or patterns) git should ignore.
         * * `.distignore` tells which files and folders should be ignored in distribution.
         *
         * ## OPTIONS
         *
         * <slug>
         * : The internal name of Custom Post Type.
         * 
         * [--label=<label>]
         * : The text used to translate the update messages.
         *
         * [--dashicon=<dashicon>]
         * : The dashicon to use in the menu.
         *
         * [--dir=<dirname>]
         * : Put the new plugin in some arbitrary directory path. Plugin directory will be path plus supplied slug.
         *
         * [--plugin_name=<title>]
         * : What to put in the 'Plugin Name:' header.
         *
         * [--plugin_description=<description>]
         * : What to put in the 'Description:' header.
         *
         * [--plugin_author=<author>]
         * : What to put in the 'Author:' header.
         *
         * [--plugin_author_uri=<url>]
         * : What to put in the 'Author URI:' header.
         *
         * [--plugin_uri=<url>]
         * : What to put in the 'Plugin URI:' header.
         *
         * [--activate]
         * : Activate the newly generated plugin.
         *
         * [--activate-network]
         * : Network activate the newly generated plugin.
         *
         * [--force]
         * : Overwrite files that already exist.
         *
         * ## EXAMPLES
         *
         *     $ wp cpt-wp-cli plugin sample-cpt --label="Sample CPT"
         *     Success: Created plugin files.
         */
        public function plugin($args, $assoc_args)
        {
            if (strlen($args[0]) > 20) {
                WP_CLI::error('Post type slugs cannot exceed 20 characters in length.');
            }

            $cpt_slug = $args[0];
            $plugin_name = ucwords(str_replace('-', ' ', $cpt_slug));
            $plugin_name = "{$plugin_name} CPT WP CLI";
            $plugin_package = str_replace(' ', '_', $plugin_name);
            $dashicon = (!empty($args['dashicon'])) ? $args['dashicon'] : 'admin-post';

            if (in_array($cpt_slug, array('.', '..'), true)) {
                WP_CLI::error("Invalid plugin slug specified. The slug cannot be '.' or '..'.");
            }

            $defaults = array(
                'label'               => preg_replace('/_|-/', ' ', strtolower($cpt_slug)),
                'dashicon'            => $this->extract_dashicon($dashicon),
                'plugin_slug'         => $cpt_slug,
                'plugin_name'         => $plugin_name,
                'plugin_package'      => $plugin_package,
                'plugin_description'  => 'PLUGIN DESCRIPTION HERE',
                'plugin_author'       => 'YOUR NAME HERE',
                'plugin_author_uri'   => 'YOUR SITE HERE',
                'plugin_uri'          => 'PLUGIN SITE HERE',
                'plugin_tested_up_to' => get_bloginfo('version'),
            );
            $data = wp_parse_args($assoc_args, $defaults);

            $data['slug'] = $cpt_slug;

            $data['textdomain'] = $cpt_slug;

            $data['label_ucfirst'] = ucfirst($data['label']);
            $data['label_plural'] = $this->pluralize($data['label']);
            $data['label_plural_ucfirst'] = ucfirst($data['label_plural']);

            $data['machine_name'] = $this->generate_machine_name($cpt_slug);

            if (!empty($assoc_args['dir'])) {
                if (!is_dir($assoc_args['dir'])) {
                    WP_CLI::error("Cannot create plugin in directory that doesn't exist.");
                }
                $plugin_dir = "{$assoc_args['dir']}/{$cpt_slug}";
            } else {
                $plugin_dir = WP_PLUGIN_DIR . "/{$cpt_slug}";
                $this->maybe_create_plugins_dir();

                $error_msg = $this->check_target_directory('plugin', $plugin_dir);
                if (!empty($error_msg)) {
                    WP_CLI::error("Invalid plugin slug specified. {$error_msg}");
                }
            }

            $plugin_path = "{$plugin_dir}/{$cpt_slug}.php";
            $plugin_readme_path = "{$plugin_dir}/readme.txt";

            $files_to_create = array(
                $plugin_path                  => self::mustache_render('plugin.mustache', $data),
                $plugin_readme_path           => self::mustache_render('plugin-readme.mustache', $data),
                "{$plugin_dir}/.gitignore"    => self::mustache_render('plugin-gitignore.mustache', $data),
                "{$plugin_dir}/.distignore"   => self::mustache_render('plugin-distignore.mustache', $data),
            );
            $force = \WP_CLI\Utils\get_flag_value($assoc_args, 'force');
            $files_written = $this->create_files($files_to_create, $force);

            $skip_message = 'All plugin files were skipped.';
            $success_message = 'Created plugin files.';
            $this->log_whether_files_written($files_written, $skip_message, $success_message);

            if (\WP_CLI\Utils\get_flag_value($assoc_args, 'activate')) {
                WP_CLI::run_command(array('plugin', 'activate', $cpt_slug));
            } elseif (\WP_CLI\Utils\get_flag_value($assoc_args, 'activate-network')) {
                WP_CLI::run_command(array('plugin', 'activate', $cpt_slug), array('network' => true));
            }
        }



        /**
         * Creates the plugins directory if it doesn't already exist.
         */
        protected function maybe_create_plugins_dir()
        {
            if (!is_dir(WP_PLUGIN_DIR)) {
                wp_mkdir_p(WP_PLUGIN_DIR);
            }
        }



        /**
         * Checks that the `$target_dir` is a child directory of the WP themes or plugins directory, depending on `$type`.
         *
         * @param string $type       "theme" or "plugin"
         * @param string $target_dir The theme/plugin directory to check.
         *
         * @return null|string Returns null on success, error message on error.
         */
        private function check_target_directory($type, $target_dir)
        {
            $parent_dir = dirname(self::canonicalize_path(str_replace('\\', '/', $target_dir)));

            if ('theme' === $type && str_replace('\\', '/', WP_CONTENT_DIR . '/themes') !== $parent_dir) {
                return sprintf('The target directory \'%1$s\' is not in \'%2$s\'.', $target_dir, WP_CONTENT_DIR . '/themes');
            }

            if ('plugin' === $type && str_replace('\\', '/', WP_PLUGIN_DIR) !== $parent_dir) {
                return sprintf('The target directory \'%1$s\' is not in \'%2$s\'.', $target_dir, WP_PLUGIN_DIR);
            }

            // Success.
            return null;
        }



        protected function create_files($files_and_contents, $force)
        {
            $wp_filesystem = $this->init_wp_filesystem();
            $wrote_files   = array();

            foreach ($files_and_contents as $filename => $contents) {
                $should_write_file = $this->prompt_if_files_will_be_overwritten($filename, $force);
                if (!$should_write_file) {
                    continue;
                }

                $wp_filesystem->mkdir(dirname($filename));

                if (!$wp_filesystem->put_contents($filename, $contents)) {
                    WP_CLI::error("Error creating file: {$filename}");
                } elseif ($should_write_file) {
                    $wrote_files[] = $filename;
                }
            }
            return $wrote_files;
        }



        protected function prompt_if_files_will_be_overwritten($filename, $force)
        {
            $should_write_file = true;
            if (!file_exists($filename)) {
                return true;
            }

            WP_CLI::warning('File already exists.');
            WP_CLI::log($filename);
            if (!$force) {
                do {
                    $answer = cli\prompt(
                        'Skip this file, or replace it?',
                        $default = false,
                        $marker  = '[s/r]: '
                    );
                } while (!in_array($answer, array('s', 'r'), true));
                $should_write_file = 'r' === $answer;
            }

            $outcome = $should_write_file ? 'Replacing' : 'Skipping';
            WP_CLI::log($outcome . PHP_EOL);

            return $should_write_file;
        }



        protected function log_whether_files_written($files_written, $skip_message, $success_message)
        {
            if (empty($files_written)) {
                WP_CLI::log($skip_message);
            } else {
                WP_CLI::success($success_message);
            }
        }



        /**
         * Extracts dashicon name when provided or return null otherwise.
         *
         * @param array $assoc_args
         * @return string|null
         */
        private function extract_dashicon($dashicon)
        {
            if (!$dashicon)
                return null;

            return preg_replace('/dashicon(-|s-)/', '', $dashicon);
        }



        /**
         * Generates the machine name for function declarations.
         *
         * @param string $slug Slug name to convert.
         * @return string
         */
        private function generate_machine_name($slug)
        {
            return str_replace('-', '_', $slug);
        }



        /**
         * Pluralizes a noun.
         *
         * @see    Inflector::pluralize()
         * @param  string $word Word to be pluralized.
         * @return string
         */
        private function pluralize($word)
        {
            $inflector = InflectorFactory::create()->build();

            return $inflector->pluralize($word);
        }



        /**
         * Initializes WP_Filesystem.
         */
        protected function init_wp_filesystem()
        {
            global $wp_filesystem;

            WP_Filesystem();

            return $wp_filesystem;
        }



        /**
         * Localizes the template path.
         */
        private static function mustache_render($template, $data = array())
        {
            $mustache = new \Mustache_Engine(array(
                'entity_flags'  => ENT_QUOTES,
                'loader'        => new \Mustache_Loader_FilesystemLoader(dirname(dirname(__FILE__)) . "/templates"),
            ));

            $tpl = $mustache->loadTemplate($template);

            return $tpl->render($data);
        }



        /*
         * Returns the canonicalized path, with dot and double dot segments resolved.
         *
         * Copied from Symfony\Component\DomCrawler\AbstractUriElement::canonicalizePath().
         * Implements RFC 3986, section 5.2.4.
         *
         * @param string $path The path to make canonical.
         *
         * @return string The canonicalized path.
         */
        private static function canonicalize_path($path)
        {
            if ('' === $path || '/' === $path) {
                return $path;
            }

            if ('.' === substr($path, -1)) {
                $path .= '/';
            }

            $output = array();

            foreach (explode('/', $path) as $segment) {
                if ('..' === $segment) {
                    array_pop($output);
                } elseif ('.' !== $segment) {
                    $output[] = $segment;
                }
            }

            return implode('/', $output);
        }
    }
}
