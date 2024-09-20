<?php

namespace Casbin\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Casbin\Log\Logger\DefaultLogger;

/**
 * DefaultLoggerTest.
 *
 * @author techlee@qq.com
 */
class DefaultLoggerTest extends TestCase
{
    public function testDefaultLogger()
    {
        $logger = new DefaultLogger();

        $logger->enableLog(true);
        $enable = $logger->isEnabled();
        $this->assertTrue($enable);

        $path = $logger->path;
        $name = $logger->name;
        $logfile = $path . DIRECTORY_SEPARATOR . $name;

        if (file_exists($logfile)) {
            unlink($logfile);
        }

        $logger->logModel([]);
        $logger->logEnforce('my_matcher', ['bob'], true, []);
        $logger->logPolicy([]);
        $logger->logRole([]);
        $logger->logError(new \Exception('test'));
        $pattern = '/^.*? Model:\s*' . PHP_EOL .
            '^.*? Request: bob ---> true' . PHP_EOL .
            'Hit Policy:\s*' . PHP_EOL .
            '^.*? Policy:\s*' . PHP_EOL .
            '^.*? Roles:\s*' . PHP_EOL .
            '^.*? Error: test' . PHP_EOL . '$/m';

        $this->assertTrue(file_exists($logfile));
        $this->assertMatchesRegularExpression($pattern, file_get_contents($logfile));
    }
}
