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
use MuckiRestic\Library\Manage;
use MuckiRestic\Library\ManageFactory;
use ReflectionMethod;

class ManageTest extends TestCase
{
    /**
     * @throws \ReflectionException
     */
    public function testCreateFactoryInstanceReturnsBackupFactory(): void
    {
        $method = new ReflectionMethod(Manage::class, 'createFactoryInstance');
        $factory = $method->invoke(new Manage());

        $this->assertInstanceOf(ManageFactory::class, $factory);
    }
}