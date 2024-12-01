<?php

namespace MuckiRestic\Test;

class TestData
{

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