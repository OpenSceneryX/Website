<?php

/**
 * Plugin Name: OpenSceneryX
 * Plugin URI: http://www.opensceneryx.com
 * Description: Implements functionality for OpenSceneryX
 * Version: 1.0.0
 * Author: Austin Goudge
 * Author URI: http://www.opensceneryx.com
 * License: GPL2
 */

/*  Copyright 2015  Austin Goudge  (email : austin@opensceneryx.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if (!defined( 'WPINC')) {
    die;
}

require_once plugin_dir_path(__FILE__) . 'includes/class-opensceneryx.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-osxitem.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-osxcategory.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-osxobject.php';

function runOpenSceneryX($pluginDirPath)
{
    $osx = new OpenSceneryX();
    $osx->run($pluginDirPath);
}

runOpenSceneryX(plugin_dir_path(__FILE__));

