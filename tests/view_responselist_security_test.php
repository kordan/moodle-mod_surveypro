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

declare(strict_types=1);

namespace mod_surveypro;

defined('MOODLE_INTERNAL') || die();

// Load the gadget fixture explicitly.
// Fixtures live outside the PSR-4 autoload tree intentionally: they must
// never be loaded in production, only when the test suite runs.
require_once(__DIR__ . '/fixtures/security_test_gadget.php');

use mod_surveypro\tests\fixtures\security_test_gadget;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use moodle_exception;

/**
 * Security regression tests for the PHP Object Injection fix in view_responselist.
 *
 * Covers all four protected static helpers introduced by the fix, accessed via an
 * anonymous subclass (no reflection needed, no visibility hacks).
 *
 * Test categories
 * ---------------
 * 1. decode_search_restrictions_raw  – JSON path (valid inputs)
 * 2. decode_search_restrictions_raw  – JSON path (invalid inputs)
 * 3. decode_search_restrictions_raw  – Legacy serialize path (safe: allowed_classes=false)
 * 4. decode_search_restrictions_raw  – Gadget chain NOT triggered  ← core security assertion
 * 5. validate_search_restrictions_structure – Allowed scalar types
 * 6. validate_search_restrictions_structure – Forbidden types / depth bomb
 * 7. normalise_search_restriction_keys – Valid positive-integer keys
 * 8. normalise_search_restriction_keys – Invalid keys rejected
 * 9. decode_search_restrictions        – Full pipeline (happy path)
 * 10. decode_search_restrictions       – Full pipeline (attack payloads)
 * 11. Roundtrip: view_responsesearch output → view_responselist input
 *
 * @package   mod_surveypro
 * @category  test
 * @copyright 2025 Your Name <your@email.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
#[CoversClass(view_responselist::class)]
#[Group('mod_surveypro')]
#[Group('security')]
final class view_responselist_security_test extends \advanced_testcase {
    // =========================================================================
    // Test fixture: anonymous subclass that exposes all protected methods.
    // Using an anonymous subclass (rather than reflection) keeps the type
    // system intact and fails loudly if any method signature changes.
    // =========================================================================

    /**
     * @var view_responselist
     *
     * Anonymous subclass instance used as SUT proxy.
     */
    private view_responselist $sut;

    /**
     * @var string gadgetpayload.
     */
    private string $gadgetpayload;

    /**
     * Setup
     *
     * @return void
     */
    protected function setUp(): void {
        parent::setUp();
        $this->resetAfterTest();

        // The payload must be constructed BEFORE resetting the flag,
        // because `serialize()` triggers `__destruct()` on the temporary object.
        $this->gadgetpayload = serialize(new security_test_gadget('test'));

        // The flag is already set to true because of the __destruct() call above: we'll reset it
        // AFTER constructing the payload, so the tests start with a clean slate.
        security_test_gadget::$triggered = false;

        $this->sut = new class (null, null, null) extends view_responselist {
            /**
             * Class constructor.
             *
             * @param object $cm
             * @param object $context
             * @param object $surveypro
             */
            public function __construct($cm, $context, $surveypro) {
                // Intentionally left blank: the tested methods are static
                // and do not access properties initialized by the parent constructor.
            }
            /**
             * Public decoder of text.
             *
             * @param string $raw
             * @return array
             */
            public function pub_decode(string $raw): array {
                return $this->decode_search_restrictions($raw);
            }
            /**
             * Public decoder of raw.
             *
             * @param string $raw
             * @return array
             */
            public static function pub_decode_raw(string $raw): array {
                return self::decode_search_restrictions_raw($raw);
            }
            /**
             * Public validator
             *
             * @param array $data
             * @param int $depth
             * @return void
             */
            public static function pub_validate(array $data, int $depth = 0): void {
                self::validate_search_restrictions_structure($data, $depth);
            }
            /**
             * Public is allowed
             *
             * @param mixed $value
             * @param int $depth
             * @return bool
             */
            public static function pub_is_allowed(mixed $value, int $depth = 0): bool {
                return self::is_allowed_search_restriction_value($value, $depth);
            }
            /**
             * Public normalise.
             *
             * @param array $data
             * @return array
             */
            public static function pub_normalise(array $data): array {
                return self::normalise_search_restriction_keys($data);
            }
        };
    }

    // =========================================================================
    // 1. decode_search_restrictions_raw – JSON path (valid)
    // =========================================================================

    /**
     * Test raw json decode valid
     *
     * @param string $json
     * @param array $expected
     * @return void
     */
    #[Test]
    #[DataProvider('provide_valid_json_inputs')]
    #[TestDox('Valid JSON input is decoded to the expected array')]
    public function test_raw_json_decode_valid(string $json, array $expected): void {
        $result = $this->sut::pub_decode_raw($json);
        $this->assertSame($expected, $result);
    }

    /**
     * Provide a valid json object as input.
     *
     * @return array<string, array{string, array}>
     */
    public static function provide_valid_json_inputs(): array {
        return [
            'empty object'           => ['{}', []],
            'single string value'    => ['{"42":"foo"}', ['42' => 'foo']],
            'integer value'          => ['{"1":99}', ['1' => 99]],
            'float value'            => ['{"1":3.14}', ['1' => 3.14]],
            'bool true value'        => ['{"1":true}', ['1' => true]],
            'bool false value'       => ['{"1":false}', ['1' => false]],
            'null value'             => ['{"1":null}', ['1' => null]],
            'nested array value'     => ['{"3":["a","b"]}', ['3' => ['a', 'b']]],
            'multiple keys'          => ['{"1":"x","2":"y"}', ['1' => 'x', '2' => 'y']],
            'unicode string'         => ['{"1":"café"}', ['1' => 'café']],
            'top-level json array'   => ['["a","b"]', ['a', 'b']],
        ];
    }

    // =========================================================================
    // 2. decode_search_restrictions_raw – JSON path (invalid)
    // =========================================================================

    /**
     * Test raw json decode invalid throws
     *
     * @param string $json
     * @return void
     */
    #[Test]
    #[DataProvider('provide_invalid_json_inputs')]
    #[TestDox('Malformed or non-array JSON throws moodle_exception')]
    public function test_raw_json_decode_invalid_throws(string $raw): void {
        $this->expectException(moodle_exception::class);
        $this->sut::pub_decode_raw($raw);
    }

    /**
     * Provide an invalid json object as input.
     *
     * @return array<string, array{string}>
     */
    public static function provide_invalid_json_inputs(): array {
        return [
            'malformed json (no quotes)' => ['{not:valid}'],
            'json string scalar'         => ['"just a string"'],
            'json integer scalar'        => ['42'],
            'json boolean scalar'        => ['true'],
            'truncated json'             => ['{"key":'],
        ];
    }

    // =========================================================================
    // 3. decode_search_restrictions_raw – Legacy serialize path
    // =========================================================================

    /**
     * Test raw legacy decode valid
     *
     * @param array $input
     * @return void
     */
    #[Test]
    #[DataProvider('provide_valid_legacy_inputs')]
    #[TestDox('Legacy PHP-serialized array is decoded safely')]
    public function test_raw_legacy_decode_valid(array $input): void {
        $result = $this->sut::pub_decode_raw(serialize($input));
        $this->assertSame($input, $result);
    }

    /**
     * Provide a valid legacy input.
     *
     * @return array<string, array{array}>
     */
    public static function provide_valid_legacy_inputs(): array {
        return [
            'flat string values' => [[42 => 'foo', 99 => 'bar']],
            'nested arrays'      => [[1 => ['a', 'b'], 2 => ['c']]],
            'single item'        => [[7 => 'search_term']],
        ];
    }

    /**
     * Test raw legacy non array throws
     *
     * @return void
     */
    #[Test]
    #[TestDox('Legacy non-array serialized value throws moodle_exception')]
    public function test_raw_legacy_non_array_throws(): void {
        $this->expectException(moodle_exception::class);
        // serialize() of a plain string produces 's:…' — not an array.
        $this->sut::pub_decode_raw(serialize('evil string'));
    }

    // =========================================================================
    // 4. Gadget chain – CORE SECURITY ASSERTIONS
    //
    // With the old bare unserialize(), any class present in the runtime that
    // defines __wakeup() or __destruct() becomes an attack surface.
    // security_test_gadget is intentionally loaded (via require_once above)
    // so it IS present in the runtime — exactly mirroring the production risk.
    //
    // All three tests below must see security_test_gadget::$triggered === false.
    // =========================================================================

    /**
     * Test gadget wakeup never triggered via raw
     *
     * @return void
     */
    #[Test]
    #[TestDox('__wakeup() of a gadget class is NEVER called on a serialized object payload')]
    public function test_gadget_wakeup_never_triggered_via_raw(): void {
        // Usa il payload pre-costruito: nessun new security_test_gadget() qui.
        try {
            $this->sut::pub_decode_raw($this->gadgetpayload);
        } catch (moodle_exception $e) {
            // Expected: a serialized object is not an array, so decode_search_restrictions_raw
            // throws invalidrequest. This is the correct behaviour — we only care that
            // security_test_gadget::$triggered remains false.
            $this->assertStringContainsString('invalidrequest', $e->errorcode);
        }

        $this->assertFalse(
            security_test_gadget::$triggered,
            'CRITICAL: __wakeup() was invoked. The allowed_classes=>false guard is not in effect.'
        );
    }

    /**
     * Test gadget destruct never triggered when nested in array
     *
     * @return void
     */
    #[Test]
    #[TestDox('__destruct() of a gadget nested inside a serialized array is NEVER called')]
    public function test_gadget_destruct_never_triggered_when_nested_in_array(): void {
        $payload = serialize([42 => new security_test_gadget('nested')]);
        // __destruct() dell'oggetto temporaneo è già scattato qui sopra.
        security_test_gadget::$triggered = false; // reset locale dopo la serialize.

        try {
            $result = $this->sut::pub_decode_raw($payload);
            foreach ($result as $v) {
                $this->assertNotInstanceOf(security_test_gadget::class, $v);
            }
        } catch (moodle_exception $e) {
            // Expected: the nested object becomes __PHP_Incomplete_Class (not a real instance),
            // so validate_search_restrictions_structure rejects it with invalidrequest.
            $this->assertStringContainsString('invalidrequest', $e->errorcode);
        }

        gc_collect_cycles();

        $this->assertFalse(
            security_test_gadget::$triggered,
            'CRITICAL: __destruct() was invoked on a nested gadget object.'
        );
    }

    /**
     * Test full pipeline gadget never triggered
     *
     * @return void
     */
    #[Test]
    #[TestDox('Full pipeline rejects a serialized object payload without triggering the gadget')]
    public function test_full_pipeline_gadget_never_triggered(): void {
        // Usa il payload pre-costruito nel setUp().
        try {
            $this->sut->pub_decode($this->gadgetpayload);
            $this->fail('Expected moodle_exception was not thrown.');
        } catch (moodle_exception $e) {
            // Expected: the serialized object payload must be rejected with invalidrequest.
            // We reach here in the correct path — the gadget must NOT have been triggered.
            $this->assertStringContainsString('invalidrequest', $e->errorcode);
        }

        $this->assertFalse(
            security_test_gadget::$triggered,
            'CRITICAL: gadget was triggered during full pipeline processing.'
        );
    }

    // =========================================================================
    // 5. validate_search_restrictions_structure – Allowed types
    // =========================================================================

    /**
     * Test validate allows safe types
     *
     * @param mixed $value
     * @return void
     */
    #[Test]
    #[DataProvider('provide_allowed_scalar_values')]
    #[TestDox('Allowed value type passes structural validation without exception')]
    public function test_validate_allows_safe_types(mixed $value): void {
        $this->sut::pub_validate([1 => $value]);
        // Reaching this line means no exception was thrown.
        $this->assertTrue(true);
    }

    /**
     * Provide allowed scalar values
     *
     * @return array<string, array{mixed}>
     */
    public static function provide_allowed_scalar_values(): array {
        return [
            'null'           => [null],
            'bool true'      => [true],
            'bool false'     => [false],
            'int zero'       => [0],
            'int positive'   => [42],
            'int negative'   => [-1],
            'float'          => [3.14],
            'empty string'   => [''],
            'ascii string'   => ['hello world'],
            'unicode string' => ['こんにちは'],
            'flat array'     => [['a', 'b', 'c']],
            'nested array'   => [[['x' => [1, 2, 3]]]],
        ];
    }

    // =========================================================================
    // 6. validate_search_restrictions_structure – Forbidden types / depth bomb
    // =========================================================================

    /**
     * Test validate rejects incomplete class object
     *
     * @return void
     */
    #[Test]
    #[TestDox('Object value (e.g. __PHP_Incomplete_Class) inside array throws moodle_exception')]
    public function test_validate_rejects_incomplete_class_object(): void {
        $this->expectException(moodle_exception::class);
        // unserialize with allowed_classes=>false returns __PHP_Incomplete_Class,
        // which is still an object and must be rejected.
        $incomplete = unserialize(
            serialize(new security_test_gadget()),
            ['allowed_classes' => false]
        );
        $this->sut::pub_validate([1 => $incomplete]);
    }

    /**
     * Test validate rejects resource value
     *
     * @return void
     */
    #[Test]
    #[TestDox('Resource value inside array throws moodle_exception')]
    public function test_validate_rejects_resource_value(): void {
        $this->expectException(moodle_exception::class);
        $handle = tmpfile();
        try {
            $this->sut::pub_validate([1 => $handle]);
        } finally {
            fclose($handle);
        }
    }

    /**
     * Test validate rejects excessive depth param
     *
     * @return void
     */
    #[Test]
    #[TestDox('Calling validate at depth=9 (exceeds limit of 8) throws moodle_exception')]
    public function test_validate_rejects_excessive_depth_param(): void {
        $this->expectException(moodle_exception::class);
        $this->sut::pub_validate([1 => 'x'], 9);
    }

    /**
     * Test validate rejects deeply nested array
     *
     * @return void
     */
    #[Test]
    #[TestDox('Array nested beyond depth 6 throws moodle_exception')]
    public function test_validate_rejects_deeply_nested_array(): void {
        $this->expectException(moodle_exception::class);
        $deep = 'leaf';
        for ($i = 0; $i < 9; $i++) {
            $deep = [$deep];
        }
        $this->sut::pub_validate([1 => $deep]);
    }

    // =========================================================================
    // 7. normalise_search_restriction_keys – Valid positive-integer keys
    // =========================================================================

    /**
     * Test normalise valid keys
     *
     * @param int|string $key
     * @param int $expectedkey
     * @return void
     */
    #[Test]
    #[DataProvider('provide_valid_key_inputs')]
    #[TestDox('Valid itemid key is normalised to the expected positive integer')]
    public function test_normalise_valid_keys(int|string $key, int $expectedkey): void {
        $result = $this->sut::pub_normalise([$key => 'value']);
        $this->assertArrayHasKey($expectedkey, $result);
        $this->assertSame('value', $result[$expectedkey]);
    }

    /**
     * Provide valid key inputs.
     *
     * @return array<string, array{int|string, int}>
     */
    public static function provide_valid_key_inputs(): array {
        return [
            'integer key 1'      => [1, 1],
            'integer key 99'     => [99, 99],
            'string key "1"'     => ['1', 1],
            'string key "42"'    => ['42', 42],
        ];
    }

    // =========================================================================
    // 8. normalise_search_restriction_keys – Invalid keys
    // =========================================================================

    /**
     * Test normalise invalid keys throw
     *
     * @param mixed $key
     * @return void
     */
    #[Test]
    #[DataProvider('provide_invalid_key_inputs')]
    #[TestDox('Invalid itemid key throws moodle_exception')]
    public function test_normalise_invalid_keys_throw(mixed $key): void {
        $this->expectException(moodle_exception::class);
        $this->sut::pub_normalise([$key => 'value']);
    }

    /**
     * Provide invalid key inputs.
     *
     * @return array<string, array{mixed}>
     */
    public static function provide_invalid_key_inputs(): array {
        return [
            'zero'               => [0],
            'negative int'       => [-1],
            'string zero'        => ['0'],
            'negative string'    => ['-1'],
            'non-numeric string' => ['foo'],
            'float-like string'  => ['1.5'],
            'empty string'       => [''],
            'sql injection'      => ["1 OR 1=1"],
            'null byte'          => ["1\x00"],
        ];
    }

    // =========================================================================
    // 9. decode_search_restrictions – Full pipeline (happy path)
    // =========================================================================

    /**
     * Test full pipeline valid
     *
     * @param string $raw
     * @param array $expected
     * @return void
     */
    #[Test]
    #[DataProvider('provide_full_pipeline_valid')]
    #[TestDox('Full pipeline accepts valid input and returns normalised array')]
    public function test_full_pipeline_valid(string $raw, array $expected): void {
        $result = $this->sut->pub_decode($raw);
        $this->assertSame($expected, $result);
    }

    /**
     * Provide full pipeline valid.
     *
     * @return array<string, array{string, array}>
     */
    public static function provide_full_pipeline_valid(): array {
        return [
            'empty string returns empty array' => [
                '',
                [],
            ],
            'whitespace-only string returns empty array' => [
                '   ',
                [],
            ],
            'json with positive int keys' => [
                '{"1":"term_a","2":"term_b"}',
                [1 => 'term_a', 2 => 'term_b'],
            ],
            'legacy serialized flat array' => [
                serialize([5 => 'legacy_value']),
                [5 => 'legacy_value'],
            ],
            'realistic payload from view_responsesearch' => [
                // Simulates what view_responsesearch::get_searchquery() now encodes.
                json_encode([1 => 'value1', 3 => ['nested' => 'data']], JSON_UNESCAPED_UNICODE),
                [1 => 'value1', 3 => ['nested' => 'data']],
            ],
        ];
    }

    // =========================================================================
    // 10. decode_search_restrictions – Full pipeline (attack payloads)
    // =========================================================================

    /**
     * Test full pipeline rejects attacks
     *
     * @param string $payload
     * @return void
     */
    #[Test]
    #[DataProvider('provide_attack_payloads')]
    #[TestDox('Full pipeline rejects known attack payload')]
    public function test_full_pipeline_rejects_attacks(string $payload): void {
        $this->expectException(moodle_exception::class);
        $this->sut->pub_decode($payload);
    }

    /**
     * Provide attack payloads
     *
     * @return array<string, array{string}>
     */
    public static function provide_attack_payloads(): array {
        return [
            // --- Object injection ---
            'serialized plain object' => [
                serialize(new security_test_gadget('attack')),
            ],
            'hand-crafted O: payload' => [
                'O:38:"mod_surveypro\tests\fixtures\security_test_gadget":1:{s:4:"data";s:6:"pwned!"}',
            ],
            'hand-crafted stdClass' => [
                'O:8:"stdClass":1:{s:4:"evil";s:3:"rce";}',
            ],

            // --- Key injection ---
            'json with zero key' => [
                '{"0":"value"}',
            ],
            'json with negative key' => [
                '{"-1":"value"}',
            ],
            'json with non-numeric key' => [
                '{"../../etc/passwd":"value"}',
            ],
            'json with sql-injection key' => [
                '{"1 OR 1=1":"value"}',
            ],

            // --- Structure attacks ---
            'json scalar string at top level' => ['"attack"'],
            'json scalar integer at top level' => ['1337'],
            'malformed json'                   => ['{broken'],

            // --- Encoding tricks ---
            'null byte in json value' => ["{\"1\":\"val\x00ue\"}"],
        ];
    }

    // =========================================================================
    // 11. Roundtrip: view_responsesearch output → view_responselist input
    // =========================================================================

    /**
     * Test json roundtrip responsesearch to responselist
     *
     * @return void
     */
    #[Test]
    #[TestDox('JSON produced by view_responsesearch is correctly consumed by view_responselist')]
    public function test_json_roundtrip_responsesearch_to_responselist(): void {
        // Mirrors exactly what view_responsesearch::get_searchquery() now returns.
        $searchfields = [1 => 'alpha', 5 => 'beta', 12 => ['nested_a', 'nested_b']];
        $encoded = json_encode($searchfields, JSON_UNESCAPED_UNICODE);

        $this->assertIsString($encoded, 'json_encode must not fail on safe scalar data.');

        $decoded = $this->sut->pub_decode($encoded);

        $this->assertArrayHasKey(1, $decoded);
        $this->assertArrayHasKey(5, $decoded);
        $this->assertArrayHasKey(12, $decoded);
        $this->assertSame('alpha', $decoded[1]);
        $this->assertSame('beta', $decoded[5]);
        $this->assertSame(['nested_a', 'nested_b'], $decoded[12]);
    }
}
