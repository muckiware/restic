<?php declare(strict_types=1);
/**
 * MuckiRestic
 *
 * @category   Library
 * @package    MuckiRestic
 * @copyright  Copyright (c) 2024 by Muckiware
 * @license    MIT
 * @author     Muckiware
 *
 */
namespace MuckiRestic\ResultParser;

use MuckiRestic\Entity\Result\BackupResultEntity;
use MuckiRestic\Entity\Result\SnapshotsResultEntity;
use MuckiRestic\Entity\Result\FilesDirsResultEntity;

class BackupResultParser extends OutputParser
{
    public static function textParserBackupResult(string $input): BackupResultEntity
    {
        $result = self::getResultFromTextOutput($input);

        $backupResultEntity = new BackupResultEntity();

        if($result && array_key_exists('snapshots', $result)) {

            $snapshotsResultEntity = new SnapshotsResultEntity();
            $snapshotsResultEntity->setOld($result['snapshots']['old']);
            $snapshotsResultEntity->setNew($result['snapshots']['new']);
            $backupResultEntity->setSnapshots($snapshotsResultEntity);
        }

        if($result && array_key_exists('files', $result)) {

            $filesResultEntity = new FilesDirsResultEntity();
            $filesResultEntity->setNew($result['files']['new']);
            $filesResultEntity->setChanged($result['files']['changed']);
            $filesResultEntity->setUnmodified($result['files']['unmodified']);
            $backupResultEntity->setFiles($filesResultEntity);
        }

        if($result && array_key_exists('directories', $result)) {

            $directoriesResultEntity = new FilesDirsResultEntity();
            $directoriesResultEntity->setNew($result['directories']['new']);
            $directoriesResultEntity->setChanged($result['directories']['changed']);
            $directoriesResultEntity->setUnmodified($result['directories']['unmodified']);
            $backupResultEntity->setDirectories($directoriesResultEntity);
        }

        if($result && array_key_exists('processed', $result)) {
            $backupResultEntity->setProcessed($result['processed']);
        }

        return $backupResultEntity;
    }

    /**
     * @param string $input
     * @return array<mixed>
     */
    protected static function getResultFromTextOutput(string $input): array
    {
        $result = array();
        $inputArray = array_filter(explode("\n", $input), function($value) {
            return $value !== '';
        });

        foreach ($inputArray as $value) {

            if(str_contains($value, 'using parent snapshot')) {
                $result['snapshots']['old'] = str_replace('using parent snapshot', '', $value);
            }
            if(str_contains($value, 'Files:')) {
                $result['files'] =  self::transformArray(
                    array_filter(
                        array_map('trim', explode(',', str_replace('Files:', '', $value)))
                    ));
            }

            if(str_contains($value, 'Dirs:')) {
                $result['directories'] =  self::transformArray(
                    array_filter(
                        array_map('trim', explode(',', str_replace('Dirs:', '', $value)))
                    ));
            }

            if (preg_match('/^snapshot [a-f0-9]+ saved$/', $value)) {
                $result['snapshots']['new'] = str_replace('snapshot ', '', str_replace(' saved', '', $value));
            }

            if(str_contains($value, 'Added to the repository:')) {
                $result['processed']['added'] = str_replace('Added to the repository:', '', $value);
            }
        }

        return $result;
    }
}
