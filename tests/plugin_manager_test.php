<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Unit tests for the surveypro_plugin_manager class.
 *
 * @package   mod_surveypro
 * @copyright 2026 kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_surveypro;

use PHPUnit\Framework\Attributes\CoversClass;

defined('MOODLE_INTERNAL') || die();

global $CFG;

/**
 * Test the hide/show behaviour of surveypro_plugin_manager.
 *
 * @package   mod_surveypro
 * @copyright 2026 kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(\mod_surveypro\plugin_manager::class)]
final class plugin_manager_test extends \advanced_testcase {
    protected function setUp(): void {
        parent::setUp();
        set_error_handler(function ($errno, $errstr, $errfile, $errline) {
            if ($errno === E_DEPRECATED || $errno === E_USER_DEPRECATED) {
                fwrite(STDERR, "\nDEPRECATION: $errstr\n  in $errfile:$errline\n");
            }
            return false; // Lascia proseguire il normale error handler di PHP/PHPUnit.
        }, E_DEPRECATED | E_USER_DEPRECATED);
    }

    protected function tearDown(): void {
        restore_error_handler();
        parent::tearDown();
    }

    /**
     * Pick a subplugin actually installed for the given subtype, so the test
     * does not depend on hardcoded plugin names that might not exist.
     *
     * @param string $subtype
     * @return string the plugin short name
     */
    private function get_any_installed_plugin(string $subtype): string {
        $plugins = \core_component::get_plugin_list($subtype);
        $this->assertNotEmpty($plugins, "No installed plugins found for subtype '{$subtype}', cannot run this test.");

        return array_key_first($plugins);
    }

    /**
     * Executing 'hide' on an enabled plugin must set its 'disabled' config to 1.
     *
     * @return void
     */
    public function test_execute_hide_sets_disabled_config(): void {
        $this->resetAfterTest();
        $this->setAdminUser();

        $subtype = 'surveyprotemplate';
        $pluginname = $this->get_any_installed_plugin($subtype);
        $frankenstyle = $subtype . '_' . $pluginname;

        unset_config('disabled', $frankenstyle);
        $this->assertFalse(get_config($frankenstyle, 'disabled'));

        $manager = new \mod_surveypro\plugin_manager($subtype);
        ob_start();
        $manager->execute('hide', $pluginname);
        ob_end_clean();

        $this->assertEquals(1, get_config($frankenstyle, 'disabled'));
    }

    /**
     * Executing 'show' on a disabled plugin must clear its 'disabled' config.
     *
     * @return void
     */
    public function test_execute_show_clears_disabled_config(): void {
        $this->resetAfterTest();
        $this->setAdminUser();

        $subtype = 'surveyprotemplate';
        $pluginname = $this->get_any_installed_plugin($subtype);
        $frankenstyle = $subtype . '_' . $pluginname;

        // Start from a known "disabled" state.
        set_config('disabled', 1, $frankenstyle);
        $this->assertEquals(1, get_config($frankenstyle, 'disabled'));

        $manager = new \mod_surveypro\plugin_manager($subtype);
        ob_start();
        $manager->execute('show', $pluginname);
        ob_end_clean();

        $this->assertFalse(get_config($frankenstyle, 'disabled'));
    }
    /**
     * Guard against the regression that caused hide/show to silently no-op:
     * check that the icon-link action names passed by view_plugins_table()
     * are the plain action strings ('hide'/'show'), not icon paths, since
     * PARAM_PLUGIN sanitizes anything containing a slash to an empty string.
     *
     * @return void
     */
    public function test_execute_ignores_invalid_action_like_icon_path(): void {
        $this->resetAfterTest();
        $this->setAdminUser();

        $subtype = 'surveyprotemplate';
        $pluginname = $this->get_any_installed_plugin($subtype);
        $frankenstyle = $subtype . '_' . $pluginname;

        unset_config('disabled', $frankenstyle);

        $manager = new \mod_surveypro\plugin_manager($subtype);
        // Simulate what PARAM_PLUGIN would produce from the broken 't/hide' value: ''.
        ob_start();
        $manager->execute('', $pluginname);
        ob_end_clean();

        // Nothing should have changed, since '' is not a recognised action.
        $this->assertFalse(get_config($frankenstyle, 'disabled'));
    }
}
