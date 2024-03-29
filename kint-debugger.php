<?php
/**
 * Plugin Name: Kint Debugger
 * Plugin URI:
 * Description: Simpe php dump ui/debugger
 * Version: 1.0.0
 * Author:
 * Author URI:
 * GitHub Plugin URI:
 * Requires: 5.0
 * License: Dual license GPL-2.0+ & MIT (Kint is licensed MIT)
 *
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

add_action('plugins_loaded', function () {
    require_once (__DIR__ . '/vendor/autoload.php');
});

/**
 * Generic data dump.
 *
 * @since 1.1
 */
if (!function_exists('dump_this')) {
    function dump_this($var, $inline = false)
    {
        /**
         * Some hooks send WP objects which then get passed as $inline
         * so check type too.
         */
        if (true === $inline) {
            $_ = [$var];
            echo call_user_func_array(['Kint', 'dump'], $_);
        } else {
            d($var);
        }
    }
}

/**
 * Helper functions.
 */

if (!function_exists('dump_wp_query')) {
    function dump_wp_query($inline = false)
    {
        global $wp_query;
        dump_this($wp_query, $inline);
    }
}

if (!function_exists('dump_wp')) {
    function dump_wp($inline = false)
    {
        global $wp;
        dump_this($wp, $inline);
    }
}

if (!function_exists('dump_post')) {
    function dump_post($inline = false)
    {
        global $post;
        dump_this($post, $inline);
    }
}

/* Override can be prevented using config constant. */
if (!defined('KINT_TO_DEBUG_BAR') || KINT_TO_DEBUG_BAR) {
    /* An mu-plugin can still override the function. */
    if (!function_exists('d')) {
        /**
         * Alias of Kint::dump()
         *
         * This sends Kint output to Debug Bar if active.
         *
         * Can be prevented by declaring the function first in an mu-plugin
         *   (but not a theme due to WordPress load sequence).
         *
         * @return string
         */
        function d()
        {
            /** @noinspection PhpUndefinedClassInspection */
            if (!class_exists('Kint')) {
                return;
            }
            $_ = func_get_args();
            if (class_exists('Debug_Bar')) {
                ob_start('kint_debug_ob');
                echo call_user_func_array(['Kint', 'dump'], $_);
                ob_end_flush();
            } else {
                return call_user_func_array(['Kint', 'dump'], $_);
            }

            return '';
        }
    }
}

/**
 * Output buffer callback.
 *
 * @param $buffer
 *
 * @return string
 */
function kint_debug_ob($buffer)
{
    global $kint_debug;
    $kint_debug[] = $buffer;
    if (class_exists('Debug_Bar')) {
        return '';
    }

    return $buffer;
}

/**
 * Add our Debug Bar panel.
 *
 * @param $panels
 *
 * @return array
 */
add_filter('debug_bar_panels', function($panels) {
    if (!class_exists('Kint_Debug_Bar_Panel')) {
        require_once __DIR__ . '/includes/class-kint-debug-bar-panel.php';
    }

    $panels[] = new Kint_Debug_Bar_Panel;

    return $panels;
});
