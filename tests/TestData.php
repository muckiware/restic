<?php

namespace MuckiRestic\Test;

class TestData
{
    public const RESTIC_TEST_PATH = './bin/restic_0.17.3_linux_386';

    public const RESTIC_TEST_PATH_0_14 = './bin/restic_0.14.0_linux_386';

    public const RESTIC_TEST_PATH_0_15 = './bin/restic_0.15.2_linux_386';
    public const RESTIC_TEST_PATH_0_16 = './bin/restic_0.16.5_linux_386';
    public const REPOSITORY_TEST_PATH = './var/testRep';
    public const BACKUP_TEST_PATH = './var/testBackup';
    public const REPOSITORY_TEST_PASSWORD = '12345%ASDEee';
    public const RESTORE_TEST_PATH = './var/testRestore';
    public const BACKUP_TEST_FILES = [
        'TEST file content 1',
        'TEST file content 2',
        'TEST file content 3'
    ];

    public const NEXT_BACKUP_TEST_FILES = [
        'TEST file content 4',
        'TEST file content 5',
        'TEST file content 6',
        'TEST file content 7',
        'TEST file content 8',
        'TEST file content 9'
    ];

    public const PARAMETER_CONFIG_BACKUP = array(

        "parameters" => [
            [
                "name" => "resticBinaryPath",
                "type" => "string",
                "required" => true
            ],
            [
                "name" => "repositoryPassword",
                "type" => "string",
                "required" => true
            ],
            [
                "name" => "repositoryPath",
                "type" => "string",
                "required" => true
            ],
            [
                "name" => "backupPath",
                "type" => "string",
                "required" => true
            ]
        ]
    );
}