<?php

declare(strict_types=1);

namespace Fopost\Wp\Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;

/**
 * Base test case for the FoPost WordPress plugin.
 *
 * WP function stubs are loaded via tests/bootstrap.php in the global
 * namespace so they are available to every class under test.
 */
abstract class TestCase extends BaseTestCase
{
}
