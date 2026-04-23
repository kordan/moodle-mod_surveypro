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
 * Unit tests for tools_import
 *
 * @package   mod_surveypro
 * @copyright 2013 onwards kordan <stringapiccola@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_surveypro;

defined('MOODLE_INTERNAL') || die();

/**
 * Unit tests for tools_import methods.
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\mod_surveypro\tools_import::class)]
final class tools_import_test extends \advanced_testcase {

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Instantiate tools_import with minimal dependencies.
     *
     * @param array $surveyproparams Optional surveypro parameters.
     * @return array [$import, $surveypro, $cm, $context]
     */
    private function make_import(array $surveyproparams = []): array {
        $this->setAdminUser();
        $course = $this->getDataGenerator()->create_course();
        $surveypro = $this->getDataGenerator()->create_module(
            'surveypro',
            array_merge(['course' => $course->id], $surveyproparams)
        );
        $cm = get_coursemodule_from_instance('surveypro', $surveypro->id);
        $context = \context_module::instance($cm->id);
        $import = new tools_import($cm, $context, $surveypro);

        return [$import, $surveypro, $cm, $context];
    }

    /**
     * Build a minimal itemhelper object.
     *
     * @param array $params
     * @return \stdClass
     */
    private function make_itemhelper(array $params = []): \stdClass {
        $itemhelper = new \stdClass();
        $itemhelper->plugin = $params['plugin'] ?? 'character';
        $itemhelper->content = $params['content'] ?? 'Test question';
        $itemhelper->required = $params['required'] ?? 0;
        $itemhelper->usespositionalanswer = $params['usespositionalanswer'] ?? false;
        $itemhelper->usesoptionother = $params['usesoptionother'] ?? false;
        $itemhelper->parentid = $params['parentid'] ?? 0;
        $itemhelper->parentvalue = $params['parentvalue'] ?? null;
        $itemhelper->usescontentformat = $params['usescontentformat'] ?? false;

        return $itemhelper;
    }

    // -------------------------------------------------------------------------
    // Tests for are_headers_unique()
    // -------------------------------------------------------------------------

    /**
     * are_headers_unique() must return false when all headers are unique.
     */
    public function test_are_headers_unique_all_unique(): void {
        $this->resetAfterTest();

        [$import] = $this->make_import();

        $headers = ['userid', 'timecreated', 'question1', 'question2'];
        $result = $import->are_headers_unique($headers);

        $this->assertFalse($result);
    }

    /**
     * are_headers_unique() must return an error object when duplicates exist.
     */
    public function test_are_headers_unique_with_duplicates(): void {
        $this->resetAfterTest();

        [$import] = $this->make_import();

        $headers = ['userid', 'question1', 'question1', 'question2'];
        $result = $import->are_headers_unique($headers);

        $this->assertIsObject($result);
        $this->assertEquals('import_duplicateheader', $result->key);
    }

    // -------------------------------------------------------------------------
    // Tests for are_headers_matching()
    // -------------------------------------------------------------------------

    /**
     * are_headers_matching() must return false when no non-matching headers exist.
     */
    public function test_are_headers_matching_all_match(): void {
        $this->resetAfterTest();

        [$import] = $this->make_import();

        $result = $import->are_headers_matching([]);

        $this->assertFalse($result);
    }

    /**
     * are_headers_matching() must return an error object when non-matching headers exist.
     */
    public function test_are_headers_matching_with_nonmatching(): void {
        $this->resetAfterTest();

        [$import] = $this->make_import();

        $result = $import->are_headers_matching(['unknowncolumn', 'anothercolumn']);

        $this->assertIsObject($result);
        $this->assertEquals('import_extraheaderfound', $result->key);
        $this->assertStringContainsString('unknowncolumn', $result->a);
    }

    // -------------------------------------------------------------------------
    // Tests for is_valid_userid()
    // -------------------------------------------------------------------------

    /**
     * is_valid_userid() must return false for a valid numeric userid.
     */
    public function test_is_valid_userid_valid(): void {
        $this->resetAfterTest();

        [$import] = $this->make_import();

        $this->assertFalse($import->is_valid_userid('42'));
    }

    /**
     * is_valid_userid() must return an error for an empty userid.
     */
    public function test_is_valid_userid_empty(): void {
        $this->resetAfterTest();

        [$import] = $this->make_import();

        $result = $import->is_valid_userid('');

        $this->assertIsObject($result);
        $this->assertEquals('import_missinguserid', $result->key);
    }

    /**
     * is_valid_userid() must return an error for a non-numeric userid.
     */
    public function test_is_valid_userid_non_numeric(): void {
        $this->resetAfterTest();

        [$import] = $this->make_import();

        $result = $import->is_valid_userid('notanumber');

        $this->assertIsObject($result);
        $this->assertEquals('import_invaliduserid', $result->key);
        $this->assertEquals('notanumber', $result->a);
    }

    // -------------------------------------------------------------------------
    // Tests for is_valid_creationtime()
    // -------------------------------------------------------------------------

    /**
     * is_valid_creationtime() must return false for a valid timestamp.
     */
    public function test_is_valid_creationtime_valid(): void {
        $this->resetAfterTest();

        [$import] = $this->make_import();

        $this->assertFalse($import->is_valid_creationtime('1700000000'));
    }

    /**
     * is_valid_creationtime() must return an error for an empty value.
     */
    public function test_is_valid_creationtime_empty(): void {
        $this->resetAfterTest();

        [$import] = $this->make_import();

        $result = $import->is_valid_creationtime('');

        $this->assertIsObject($result);
        $this->assertEquals('import_missingtimecreated', $result->key);
    }

    /**
     * is_valid_creationtime() must return an error for a non-numeric value.
     */
    public function test_is_valid_creationtime_non_numeric(): void {
        $this->resetAfterTest();

        [$import] = $this->make_import();

        $result = $import->is_valid_creationtime('notadate');

        $this->assertIsObject($result);
        $this->assertEquals('import_invalidtimecreated', $result->key);
    }

    // -------------------------------------------------------------------------
    // Tests for is_valid_modificationtime()
    // -------------------------------------------------------------------------

    /**
     * is_valid_modificationtime() must return false for a valid timestamp.
     */
    public function test_is_valid_modificationtime_valid(): void {
        $this->resetAfterTest();

        [$import] = $this->make_import();

        $this->assertFalse($import->is_valid_modificationtime('1700000000'));
    }

    /**
     * is_valid_modificationtime() must return false for an empty value.
     */
    public function test_is_valid_modificationtime_empty(): void {
        $this->resetAfterTest();

        [$import] = $this->make_import();

        $this->assertFalse($import->is_valid_modificationtime(''));
    }

    /**
     * is_valid_modificationtime() must return an error for a non-numeric value.
     */
    public function test_is_valid_modificationtime_non_numeric(): void {
        $this->resetAfterTest();

        [$import] = $this->make_import();

        $result = $import->is_valid_modificationtime('notadate');

        $this->assertIsObject($result);
        $this->assertEquals('import_invalidtimemodified', $result->key);
    }

    // -------------------------------------------------------------------------
    // Tests for get_default_status()
    // -------------------------------------------------------------------------

    /**
     * get_default_status() must return CLOSED when all required items are provided.
     */
    public function test_get_default_status_all_required_provided(): void {
        $this->resetAfterTest();

        [$import] = $this->make_import();
        $import->columntoitemid = [0 => '101', 1 => '102', 2 => '103'];

        $requireditems = [101, 102, 103];
        $result = $import->get_default_status($requireditems);

        $this->assertEquals(SURVEYPRO_STATUSCLOSED, $result);
    }

    /**
     * get_default_status() must return IN PROGRESS when some required items are missing.
     */
    public function test_get_default_status_missing_required(): void {
        $this->resetAfterTest();

        [$import] = $this->make_import();
        $import->columntoitemid = [0 => '101']; // Only item 101 provided.

        $requireditems = [101, 102]; // Item 102 is required but missing.
        $result = $import->get_default_status($requireditems);

        $this->assertEquals(SURVEYPRO_STATUSINPROGRESS, $result);
    }

    /**
     * get_default_status() must return CLOSED when there are no required items.
     */
    public function test_get_default_status_no_required_items(): void {
        $this->resetAfterTest();

        [$import] = $this->make_import();
        $import->columntoitemid = [0 => '101'];

        $requireditems = [];
        $result = $import->get_default_status($requireditems);

        $this->assertEquals(SURVEYPRO_STATUSCLOSED, $result);
    }

    // -------------------------------------------------------------------------
    // Tests for is_string_notempty()
    // -------------------------------------------------------------------------

    /**
     * is_string_notempty() must return false for a non-empty value.
     */
    public function test_is_string_notempty_valid(): void {
        $this->resetAfterTest();

        [$import] = $this->make_import();
        $itemhelper = $this->make_itemhelper();

        $csvrow = [0 => 'some answer'];
        $result = $import->is_string_notempty($csvrow, 0, $itemhelper);

        $this->assertFalse($result);
    }

    /**
     * is_string_notempty() must return an error for an empty value.
     */
    public function test_is_string_notempty_empty(): void {
        $this->resetAfterTest();

        [$import] = $this->make_import();
        $itemhelper = $this->make_itemhelper();

        $csvrow = [0 => ''];
        $result = $import->is_string_notempty($csvrow, 0, $itemhelper);

        $this->assertIsObject($result);
        $this->assertEquals('import_emptyrequiredvalue', $result->key);
    }

    /**
     * is_string_notempty() must return an error for SURVEYPRO_NOANSWERVALUE.
     */
    public function test_is_string_notempty_noanswer(): void {
        $this->resetAfterTest();

        [$import] = $this->make_import();
        $itemhelper = $this->make_itemhelper();

        $csvrow = [0 => SURVEYPRO_NOANSWERVALUE];
        $result = $import->is_string_notempty($csvrow, 0, $itemhelper);

        $this->assertIsObject($result);
        $this->assertEquals('import_noanswertorequired', $result->key);
    }

    // -------------------------------------------------------------------------
    // Tests for are_positions_valid()
    // -------------------------------------------------------------------------

    /**
     * are_positions_valid() must return false for a valid position.
     */
    public function test_are_positions_valid_valid(): void {
        $this->resetAfterTest();

        [$import] = $this->make_import();
        $itemhelper = $this->make_itemhelper(['usesoptionother' => false]);

        $result = $import->are_positions_valid('2', 0, 5, $itemhelper);

        $this->assertFalse($result);
    }

    /**
     * are_positions_valid() must return an error for a position out of bounds.
     */
    public function test_are_positions_valid_out_of_bounds(): void {
        $this->resetAfterTest();

        [$import] = $this->make_import();
        $itemhelper = $this->make_itemhelper(['usesoptionother' => false]);

        $result = $import->are_positions_valid('10', 0, 5, $itemhelper);

        $this->assertIsObject($result);
        $this->assertEquals('import_positionoutofbound', $result->key);
    }

    /**
     * are_positions_valid() must return an error for a non-numeric position without optionother.
     */
    public function test_are_positions_valid_non_numeric_no_optionother(): void {
        $this->resetAfterTest();

        [$import] = $this->make_import();
        $itemhelper = $this->make_itemhelper(['usesoptionother' => false]);

        $result = $import->are_positions_valid('notanumber', 0, 5, $itemhelper);

        $this->assertIsObject($result);
        $this->assertEquals('import_positionnotinteger', $result->key);
    }

    /**
     * are_positions_valid() must return false for a non-numeric value at the last position with optionother.
     */
    public function test_are_positions_valid_optionother_last_position(): void {
        $this->resetAfterTest();

        [$import] = $this->make_import();
        $itemhelper = $this->make_itemhelper(['usesoptionother' => true]);

        // Single non-numeric value at last position is allowed with optionother.
        $result = $import->are_positions_valid('sometext', 0, 5, $itemhelper);

        $this->assertFalse($result);
    }

    // -------------------------------------------------------------------------
    // Tests for is_maxentries_respected()
    // -------------------------------------------------------------------------

    /**
     * is_maxentries_respected() must return false when maxentries is 0 (unlimited).
     */
    public function test_is_maxentries_respected_unlimited(): void {
        $this->resetAfterTest();

        [$import] = $this->make_import(['maxentries' => 0]);

        $submissionsperuser = [42 => 5, 43 => 3];
        $result = $import->is_maxentries_respected($submissionsperuser);

        $this->assertFalse($result);
    }

    /**
     * is_maxentries_respected() must return false when under the limit.
     */
    public function test_is_maxentries_respected_under_limit(): void {
        $this->resetAfterTest();

        global $USER, $DB;

        [$import, $surveypro] = $this->make_import(['maxentries' => 5]);

        $user = $this->getDataGenerator()->create_user();
        // User has 2 existing submissions.
        $DB->insert_record('surveypro_submission', (object)[
            'surveyproid' => $surveypro->id,
            'userid' => $user->id,
            'status' => SURVEYPRO_STATUSCLOSED,
            'timecreated' => time(),
            'timemodified' => 0,
        ]);
        $DB->insert_record('surveypro_submission', (object)[
            'surveyproid' => $surveypro->id,
            'userid' => $user->id,
            'status' => SURVEYPRO_STATUSCLOSED,
            'timecreated' => time(),
            'timemodified' => 0,
        ]);

        // Importing 2 more — total 4, under limit of 5.
        $submissionsperuser = [$user->id => 2];
        $result = $import->is_maxentries_respected($submissionsperuser);

        $this->assertFalse($result);
    }

    /**
     * is_maxentries_respected() must return an error when exceeding the limit.
     */
    public function test_is_maxentries_respected_exceeds_limit(): void {
        $this->resetAfterTest();

        global $DB;

        [$import, $surveypro] = $this->make_import(['maxentries' => 3]);

        $user = $this->getDataGenerator()->create_user();
        // User has 2 existing submissions.
        $DB->insert_record('surveypro_submission', (object)[
            'surveyproid' => $surveypro->id,
            'userid' => $user->id,
            'status' => SURVEYPRO_STATUSCLOSED,
            'timecreated' => time(),
            'timemodified' => 0,
        ]);
        $DB->insert_record('surveypro_submission', (object)[
            'surveyproid' => $surveypro->id,
            'userid' => $user->id,
            'status' => SURVEYPRO_STATUSCLOSED,
            'timecreated' => time(),
            'timemodified' => 0,
        ]);

        // Importing 2 more — total 4, exceeds limit of 3.
        $submissionsperuser = [$user->id => 2];
        $result = $import->is_maxentries_respected($submissionsperuser);

        $this->assertIsObject($result);
        $this->assertEquals('import_breakingmaxentries', $result->key);
        $this->assertEquals($user->id, $result->a->userid);
        $this->assertEquals(3, $result->a->maxentries);
        $this->assertEquals(4, $result->a->totalentries);
    }

    // -------------------------------------------------------------------------
    // Tests for get_columntoitemid()
    // -------------------------------------------------------------------------

    /**
     * get_columntoitemid() must map matching headers to item ids.
     */
    public function test_get_columntoitemid_matching_headers(): void {
        $this->resetAfterTest();

        [$import] = $this->make_import();

        $surveyheaders = [
            SURVEYPRO_OWNERIDLABEL => SURVEYPRO_OWNERIDLABEL,
            SURVEYPRO_TIMECREATEDLABEL => SURVEYPRO_TIMECREATEDLABEL,
            101 => 'question1',
            102 => 'question2',
        ];
        $foundheaders = [SURVEYPRO_OWNERIDLABEL, 'question1', 'question2'];

        $nonmatching = $import->get_columntoitemid($foundheaders, $surveyheaders);

        $this->assertEmpty($nonmatching);
        $this->assertArrayHasKey(SURVEYPRO_OWNERIDLABEL, $import->environmentheaders);
    }

    /**
     * get_columntoitemid() must return non-matching headers.
     */
    public function test_get_columntoitemid_nonmatching_headers(): void {
        $this->resetAfterTest();

        [$import] = $this->make_import();

        $surveyheaders = [
            101 => 'question1',
        ];
        $foundheaders = ['question1', 'unknowncolumn'];

        $nonmatching = $import->get_columntoitemid($foundheaders, $surveyheaders);

        $this->assertContains('unknowncolumn', $nonmatching);
    }
}
