<?php declare(strict_types=1);
/**
 * MuckiRestic
 *
 * @category   Library
 * @package    MuckiRestic
 * @copyright  Copyright (c) 2026 by Muckiware
 * @license    MIT
 * @author     Muckiware
 *
 */
namespace MuckiRestic\Test\Library\Backup;

use Aws\MockHandler;
use Aws\Result;
use Aws\S3\S3Client;
use PHPUnit\Framework\TestCase;

use MuckiRestic\Library\Backup\AmazonS3;

class AmazonS3Test extends TestCase
{
    /**
     * listObjectsV2 omits the Contents key entirely when the bucket holds no objects,
     * and Aws\Result returns null for a key that is not present. Iterating that null
     * raises a PHP warning, which a consuming Symfony or Shopware application turns
     * into an ErrorException through its error handler — aborting the repository
     * initialisation. The error handler installed here reproduces that.
     *
     * Reached through createRepository(true, RepositoryLocationTypes::AWSS3) whenever
     * the bucket is already empty: a first init with overwrite, or a re-init after the
     * bucket was emptied.
     */
    public function testRemoveOldRepositoryToleratesAnEmptyBucket(): void
    {
        $mockHandler = new MockHandler();
        $mockHandler->append(new Result(['IsTruncated' => false, 'KeyCount' => 0]));

        $subject = new class extends AmazonS3 {
            public S3Client $injectedClient;

            public function getS3Client(): S3Client
            {
                return $this->injectedClient;
            }
        };

        $subject->injectedClient = new S3Client([
            'version' => 'latest',
            'region' => 'eu-central-1',
            'credentials' => ['key' => 'key', 'secret' => 'secret'],
            'handler' => $mockHandler,
        ]);
        $subject->setAwsS3BucketName('a-bucket-without-objects');

        set_error_handler(static function (int $severity, string $message, string $file, int $line): bool {
            throw new \ErrorException($message, 0, $severity, $file, $line);
        });

        try {
            $subject->removeOldRepository();
        } finally {
            restore_error_handler();
        }

        $this::assertCount(
            0,
            $mockHandler,
            'Only the listObjectsV2 call may reach the client; deleteObjects must not run for an empty bucket, '
            .'and a second call would exhaust the mock queue.'
        );
    }
}
