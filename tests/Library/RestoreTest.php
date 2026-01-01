<?php declare(strict_types=1);
/**
 * MuckiRestic
 *
 * @category   Library
 * @package    MuckiRestic
 * @copyright  Copyright (c) 2024-2026 by Muckiware
 * @license    MIT
 * @author     Muckiware
 *
 */
namespace MuckiRestic\Test\Library;

use PHPUnit\Framework\TestCase;
use MuckiRestic\Library\Restore;
use MuckiRestic\Library\RestoreFactory;
use ReflectionMethod;

class RestoreTest extends TestCase
{
    /**
     * @throws \ReflectionException
     */
    public function testCreateFactoryInstanceReturnsBackupFactory(): void
    {
        $method = new ReflectionMethod(Restore::class, 'createFactoryInstance');
        $factory = $method->invoke(new Restore());

        $this->assertInstanceOf(RestoreFactory::class, $factory);
    }
}