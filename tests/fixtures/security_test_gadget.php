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

namespace mod_surveypro\tests\fixtures;

/**
 * Minimal gadget class used exclusively as a canary in security tests.
 *
 * PURPOSE
 * -------
 * This class MUST exist in the PHP runtime during the test run so that
 * unserialize() can potentially instantiate it — exactly as a real gadget
 * class would exist in a production runtime.
 *
 * If the class were absent, unserialize() would silently return an
 * __PHP_Incomplete_Class object WITHOUT calling __wakeup(), producing a
 * false-negative: the security test would pass for the wrong reason.
 *
 * HOW IT WORKS
 * ------------
 * Both magic methods (__wakeup and __destruct) set the static $triggered flag.
 * The security tests assert that $triggered remains FALSE after processing
 * a serialized payload, proving that no gadget code was ever executed.
 *
 * In a real exploit chain these methods would contain RCE / SSRF / file-write
 * payloads (e.g. Monolog, Guzzle, or Symfony gadget chains).
 * Here they only flip a flag — harmless, but a faithful canary.
 *
 * IMPORTANT: This class must NEVER be loaded outside of test runs.
 *
 * @package   mod_surveypro
 * @category  test
 * @copyright 2025 Your Name <your@email.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class security_test_gadget {
    /**
     * Canary flag. Set to true if __wakeup() or __destruct() fires.
     * Reset to false in the test setUp() before each test case.
     *
     * @var bool
     */
    public static bool $triggered = false;

    /** @var string Arbitrary payload data carried by the gadget object. */
    public string $data;

    /**
     * Class constructor.
     *
     * @param string $data
     */
    public function __construct(string $data = '') {
        $this->data = $data;
    }

    /**
     * Primary attack vector: called automatically by unserialize().
     *
     * If this fires, the fix is broken.
     */
    public function __wakeup(): void {
        self::$triggered = true;
    }

    /**
     * Secondary attack vector: called when the object is garbage-collected.
     *
     * Chains like Monolog\Handler\SyslogUdpHandler exploit __destruct()
     * to open arbitrary network sockets. We just flip the flag.
     */
    public function __destruct() {
        self::$triggered = true;
    }
}
