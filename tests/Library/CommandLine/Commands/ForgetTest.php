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
namespace MuckiRestic\Test\Library\CommandLine\Commands;

use PHPUnit\Framework\TestCase;

use MuckiRestic\Library\Configuration;
use MuckiRestic\Core\CommandParameterConfiguration;
use MuckiRestic\Test\TestData;
use MuckiRestic\Entity\CommandEntity;
use MuckiRestic\Entity\ParameterEntity;
use MuckiRestic\Exception\InvalidConfigurationException;
use MuckiRestic\Core\Commands;
use MuckiRestic\Library\CommandLine\Commands\Forget;
use MuckiRestic\Library\Manage;

class ForgetTest extends TestCase
{
    /**
     * @throws InvalidConfigurationException
     */
    public function testCheckInputParametersByCommand(): void
    {
        $manage = Manage::create();
        $manage->setBinaryPath(TestData::RESTIC_TEST_PATH_0_18);
        $manage->setRepositoryPassword(TestData::REPOSITORY_TEST_PASSWORD);
        $manage->setRepositoryPath(TestData::REPOSITORY_TEST_PATH);
        $forgetCommand = $manage->getCommandStringByCommand(Commands::FORGET);

        $this::assertEquals(TestData::RESTIC_TEST_PATH_0_18.' forget -r ./var/testRep --prune --json', $forgetCommand);
    }
}
