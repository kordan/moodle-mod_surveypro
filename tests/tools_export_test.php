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
 * Unit tests for tools_export
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_surveypro;

defined('MOODLE_INTERNAL') || die();

/**
 * Unit tests for tools_export methods.
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\mod_surveypro\tools_export::class)]
final class tools_export_test extends \advanced_testcase {
    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Instantiate tools_export with minimal dependencies.
     *
     * @param array $surveyproparams Optional surveypro parameters.
     * @return array [$export, $surveypro, $cm, $context]
     */
    private function make_export(array $surveyproparams = []): array {
        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $surveypro = $this->getDataGenerator()->create_module(
            'surveypro',
            array_merge(['course' => $course->id], $surveyproparams)
        );
        $cm = get_coursemodule_from_instance('surveypro', $surveypro->id);
        $context = \context_module::instance($cm->id);
        $export = new tools_export($cm, $context, $surveypro);

        return [$export, $surveypro, $cm, $context];
    }

    /**
     * Build a minimal formdata object for export.
     *
     * @param array $params
     * @return \stdClass
     */
    private function make_formdata(array $params = []): \stdClass {
        $formdata = new \stdClass();
        $formdata->status = $params['status'] ?? SURVEYPRO_STATUSALL;
        $formdata->outputstyle = $params['outputstyle'] ?? SURVEYPRO_RAW;
        $formdata->downloadtype = $params['downloadtype'] ?? SURVEYPRO_DOWNLOADCSV;
        foreach ($params as $key => $value) {
            $formdata->$key = $value;
        }
        return $formdata;
    }

    // -------------------------------------------------------------------------
    // Tests for get_export_filename()
    // -------------------------------------------------------------------------

    /**
     * get_export_filename() must return a non-empty string.
     */
    public function test_get_export_filename_returns_string(): void {
        $this->resetAfterTest();

        [$export, $surveypro] = $this->make_export();
        $export->formdata = $this->make_formdata();

        $filename = $export->get_export_filename();

        $this->assertIsString($filename);
        $this->assertNotEmpty($filename);
    }

    /**
     * get_export_filename() with extension must include the extension.
     */
    public function test_get_export_filename_with_extension(): void {
        $this->resetAfterTest();

        [$export] = $this->make_export();
        $export->formdata = $this->make_formdata();

        $filename = $export->get_export_filename('csv');

        $this->assertStringEndsWith('.csv', $filename);
    }

    /**
     * get_export_filename() with status closed must include the status string.
     */
    public function test_get_export_filename_status_closed(): void {
        $this->resetAfterTest();

        [$export] = $this->make_export();
        $export->formdata = $this->make_formdata(['status' => SURVEYPRO_STATUSCLOSED]);

        $filename = $export->get_export_filename();

        $statusclosed = str_replace(' ', '', get_string('statusclosed', 'surveypro'));
        $this->assertStringContainsString($statusclosed, $filename);
    }

    /**
     * get_export_filename() with status in progress must include the status string.
     */
    public function test_get_export_filename_status_inprogress(): void {
        $this->resetAfterTest();

        [$export] = $this->make_export();
        $export->formdata = $this->make_formdata(['status' => SURVEYPRO_STATUSINPROGRESS]);

        $filename = $export->get_export_filename();

        $statusinprogress = str_replace(' ', '', get_string('statusinprogress', 'surveypro'));
        $this->assertStringContainsString($statusinprogress, $filename);
    }

    /**
     * get_export_filename() with verbose output must include 'verbose'.
     */
    public function test_get_export_filename_verbose(): void {
        $this->resetAfterTest();

        [$export] = $this->make_export();
        $export->formdata = $this->make_formdata(['outputstyle' => SURVEYPRO_VERBOSE]);

        $filename = $export->get_export_filename();

        $this->assertStringContainsString('verbose', $filename);
    }

    // -------------------------------------------------------------------------
    // Tests for are_attachments_onboard()
    // -------------------------------------------------------------------------

    /**
     * are_attachments_onboard() must return false when no fileupload items exist.
     */
    public function test_are_attachments_onboard_false(): void {
        $this->resetAfterTest();

        [$export, $surveypro] = $this->make_export();
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_surveypro');
        $generator->create_item_character($surveypro, ['required' => 0]);

        $this->assertFalse($export->are_attachments_onboard());
    }

    /**
     * are_attachments_onboard() must return true when fileupload items exist.
     */
    public function test_are_attachments_onboard_true(): void {
        $this->resetAfterTest();

        [$export, $surveypro] = $this->make_export();
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_surveypro');
        $generator->create_item_fileupload($surveypro, ['required' => 0]);

        $this->assertTrue($export->are_attachments_onboard());
    }

    // -------------------------------------------------------------------------
    // Tests for attachments_define_packagename()
    // -------------------------------------------------------------------------

    /**
     * attachments_define_packagename() with type user must return a string ending with _by_user.
     */
    public function test_attachments_define_packagename_user(): void {
        $this->resetAfterTest();

        [$export] = $this->make_export();

        $name = $export->attachments_define_packagename('user');

        $this->assertIsString($name);
        $this->assertStringEndsWith('_by_user', $name);
    }

    /**
     * attachments_define_packagename() with type item must return a string ending with _by_item.
     */
    public function test_attachments_define_packagename_item(): void {
        $this->resetAfterTest();

        [$export] = $this->make_export();

        $name = $export->attachments_define_packagename('item');

        $this->assertIsString($name);
        $this->assertStringEndsWith('_by_item', $name);
    }

    /**
     * attachments_define_packagename() must return a string no longer than 80 characters.
     */
    public function test_attachments_define_packagename_max_length(): void {
        $this->resetAfterTest();

        // Create a surveypro with a very long name.
        [$export] = $this->make_export(['name' => str_repeat('a', 200)]);

        $name = $export->attachments_define_packagename('user');

        $this->assertLessThanOrEqual(80, strlen($name));
    }

    // -------------------------------------------------------------------------
    // Tests for export_add_ownerid()
    // -------------------------------------------------------------------------

    /**
     * export_add_ownerid() must return userid when not anonymous.
     */
    public function test_export_add_ownerid_not_anonymous(): void {
        $this->resetAfterTest();

        [$export] = $this->make_export(['anonymous' => 0]);
        $export->formdata = $this->make_formdata();

        $richsubmission = new \stdClass();
        $richsubmission->userid = 42;

        $result = $export->export_add_ownerid($richsubmission);

        $this->assertEquals(42, $result[SURVEYPRO_OWNERIDLABEL]);
    }

    /**
     * export_add_ownerid() must return empty array when anonymous.
     */
    public function test_export_add_ownerid_anonymous(): void {
        $this->resetAfterTest();

        [$export] = $this->make_export(['anonymous' => 1]);
        $export->formdata = $this->make_formdata();

        $richsubmission = new \stdClass();
        $richsubmission->userid = 42;

        $result = $export->export_add_ownerid($richsubmission);

        $this->assertEmpty($result);
    }

    // -------------------------------------------------------------------------
    // Tests for export_add_names()
    // -------------------------------------------------------------------------

    /**
     * export_add_names() must return names when not anonymous and includenames is set.
     */
    public function test_export_add_names_included(): void {
        $this->resetAfterTest();

        [$export] = $this->make_export(['anonymous' => 0]);
        $export->formdata = $this->make_formdata(['includenames' => 1]);

        $richsubmission = new \stdClass();
        $richsubmission->firstname = 'John';
        $richsubmission->lastname = 'Doe';

        $result = $export->export_add_names($richsubmission);

        $this->assertEquals('John', $result['firstname']);
        $this->assertEquals('Doe', $result['lastname']);
    }

    /**
     * export_add_names() must return empty array when anonymous.
     */
    public function test_export_add_names_anonymous(): void {
        $this->resetAfterTest();

        [$export] = $this->make_export(['anonymous' => 1]);
        $export->formdata = $this->make_formdata(['includenames' => 1]);

        $richsubmission = new \stdClass();
        $richsubmission->firstname = 'John';
        $richsubmission->lastname = 'Doe';

        $result = $export->export_add_names($richsubmission);

        $this->assertEmpty($result);
    }

    /**
     * export_add_names() must return empty array when includenames is not set.
     */
    public function test_export_add_names_not_included(): void {
        $this->resetAfterTest();

        [$export] = $this->make_export(['anonymous' => 0]);
        $export->formdata = $this->make_formdata(); // No includenames.

        $richsubmission = new \stdClass();
        $richsubmission->firstname = 'John';
        $richsubmission->lastname = 'Doe';

        $result = $export->export_add_names($richsubmission);

        $this->assertEmpty($result);
    }

    // -------------------------------------------------------------------------
    // Tests for export_add_dates()
    // -------------------------------------------------------------------------

    /**
     * export_add_dates() must return empty array when includedates is not set.
     */
    public function test_export_add_dates_not_included(): void {
        $this->resetAfterTest();

        [$export] = $this->make_export();
        $export->formdata = $this->make_formdata(); // No includedates.

        $richsubmission = new \stdClass();
        $richsubmission->timecreated = time();
        $richsubmission->timemodified = time();

        $result = $export->export_add_dates($richsubmission);

        $this->assertEmpty($result);
    }

    /**
     * export_add_dates() in RAW mode must return timestamps.
     */
    public function test_export_add_dates_raw(): void {
        $this->resetAfterTest();

        [$export] = $this->make_export();
        $export->formdata = $this->make_formdata([
            'includedates' => 1,
            'outputstyle' => SURVEYPRO_RAW,
        ]);

        $now = time();
        $richsubmission = new \stdClass();
        $richsubmission->timecreated = $now;
        $richsubmission->timemodified = $now;

        $result = $export->export_add_dates($richsubmission);

        $this->assertEquals($now, $result['timecreated']);
        $this->assertEquals($now, $result['timemodified']);
    }

    /**
     * export_add_dates() in RAW mode with no timemodified must return null.
     */
    public function test_export_add_dates_raw_no_timemodified(): void {
        $this->resetAfterTest();

        [$export] = $this->make_export();
        $export->formdata = $this->make_formdata([
            'includedates' => 1,
            'outputstyle' => SURVEYPRO_RAW,
        ]);

        $richsubmission = new \stdClass();
        $richsubmission->timecreated = time();
        $richsubmission->timemodified = 0;

        $result = $export->export_add_dates($richsubmission);

        $this->assertNull($result['timemodified']);
    }

    /**
     * export_add_dates() in VERBOSE mode must return formatted strings.
     */
    public function test_export_add_dates_verbose(): void {
        $this->resetAfterTest();

        [$export] = $this->make_export();
        $export->formdata = $this->make_formdata([
            'includedates' => 1,
            'outputstyle' => SURVEYPRO_VERBOSE,
        ]);

        $richsubmission = new \stdClass();
        $richsubmission->timecreated = time();
        $richsubmission->timemodified = time();

        $result = $export->export_add_dates($richsubmission);

        $this->assertIsString($result['timecreated']);
        $this->assertIsString($result['timemodified']);
    }

    // -------------------------------------------------------------------------
    // Tests for export_begin_newrecord()
    // -------------------------------------------------------------------------

    /**
     * export_begin_newrecord() must return a record with placeholders.
     */
    public function test_export_begin_newrecord_has_placeholders(): void {
        $this->resetAfterTest();

        [$export] = $this->make_export(['anonymous' => 0]);
        $export->formdata = $this->make_formdata();

        $richsubmission = new \stdClass();
        $richsubmission->userid = 42;
        $richsubmission->firstname = 'John';
        $richsubmission->lastname = 'Doe';
        $richsubmission->timecreated = time();
        $richsubmission->timemodified = time();

        $placeholders = [101 => SURVEYPRO_EXPNULLVALUE, 102 => SURVEYPRO_EXPNULLVALUE];

        $record = $export->export_begin_newrecord($richsubmission, $placeholders);

        $this->assertArrayHasKey(101, $record);
        $this->assertArrayHasKey(102, $record);
        $this->assertEquals(SURVEYPRO_EXPNULLVALUE, $record[101]);
    }

    /**
     * export_begin_newrecord() must include ownerid when not anonymous.
     */
    public function test_export_begin_newrecord_includes_ownerid(): void {
        $this->resetAfterTest();

        [$export] = $this->make_export(['anonymous' => 0]);
        $export->formdata = $this->make_formdata();

        $richsubmission = new \stdClass();
        $richsubmission->userid = 42;
        $richsubmission->firstname = 'John';
        $richsubmission->lastname = 'Doe';
        $richsubmission->timecreated = time();
        $richsubmission->timemodified = time();

        $placeholders = [];
        $record = $export->export_begin_newrecord($richsubmission, $placeholders);

        $this->assertArrayHasKey(SURVEYPRO_OWNERIDLABEL, $record);
        $this->assertEquals(42, $record[SURVEYPRO_OWNERIDLABEL]);
    }
}
