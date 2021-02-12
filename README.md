# cpt-wp-cli

Generates PHP code for registering a Custom Post Type in plugin with WP-CLI.



## Installing

N.B. To use the commands added by the plugin you must have installed [WP-CLI](https://wp-cli.org)

Clone the repo or download the files in WP plugins folder:

~~~
git clone https://github.com/stfn00/cpt-wp-cli
~~~

Then activate it and you are ready to use the commands from WP-CLI.



## Usage

This package implements the following commands:

### wp cpt-wp-cli plugin

Generates PHP code for registering a Custom Post Type in plugin.

~~~
wp cpt-wp-cli plugin <slug> [--label=<label>] [--dashicon=<dashicon>] [--dir=<dirname>] [--plugin_name=<title>] [--plugin_description=<description>] [--plugin_author=<author>] [--plugin_author_uri=<url>] [--plugin_uri=<url>] [--activate] [--activate-network] [--force]
~~~

The following files are always generated:

* `plugin-slug.php` is the main PHP plugin file.
* `readme.txt` is the readme file for the plugin.
* `.gitignore` tells which files (or patterns) git should ignore.
* `.distignore` tells which files and folders should be ignored in distribution.

**OPTIONS**

	<slug>
		The internal name of Custom Post Type.

    [--label=<label>]
		The text used to translate the update messages.

	[--dashicon=<dashicon>]
		The dashicon to use in the menu.

	[--dir=<dirname>]
		Put the new plugin in some arbitrary directory path. Plugin directory will be path plus supplied slug.

	[--plugin_name=<title>]
		What to put in the 'Plugin Name:' header.

	[--plugin_description=<description>]
		What to put in the 'Description:' header.

	[--plugin_author=<author>]
		What to put in the 'Author:' header.

	[--plugin_author_uri=<url>]
		What to put in the 'Author URI:' header.

	[--plugin_uri=<url>]
		What to put in the 'Plugin URI:' header.

	[--activate]
		Activate the newly generated plugin.

	[--activate-network]
		Network activate the newly generated plugin.

	[--force]
		Overwrite files that already exist.

**EXAMPLES**

    $ wp cpt-wp-cli plugin sample-cpt --label="Sample CPT"
    Success: Created plugin files.
