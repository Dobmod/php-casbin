<?php

declare(strict_types=1);

namespace Casbin\Log\Logger;

use Casbin\Log\Logger;

/**
 * Class DefaultLogger.
 *
 * @author techlee@qq.com
 */
class DefaultLogger implements Logger
{
    /**
     * DefaultLogger is the implementation for a Logger using golang log.
     *
     * @var bool
     */
    public bool $enabled = false;

    /**
     * @var string
     */
    public string $name = 'casbin.log';

    /**
     * @var string
     */
    public string $path = '/tmp';

    /**
     * DefaultLogger constructor.
     */
    public function __construct()
    {
        $this->path = sys_get_temp_dir();
    }

    /**
     * enableLog.
     *
     * @param bool $enable
     */
    public function enableLog(bool $enable): void
    {
        $this->enabled = $enable;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Log model information.
     * 
     * @param array $model
     * 
     * @return void
     */
    public function logModel(array $model): void
    {
        if (!$this->enabled) {
            return;
        }

        $str = 'Model: ';
        foreach ($model as $v) {
            $str .= sprintf("%s " . PHP_EOL, '[' . implode(' ', $v) . ']');
        }

        $this->print($str);
    }

    /**
     * Log enforcer information.
     * 
     * @param string $matcher
     * @param array $request
     * @param bool $result
     * @param array $explains
     * 
     * @return void
     */
    public function logEnforce(string $matcher, array $request, bool $result, array $explains): void
    {
        if (!$this->enabled) {
            return;
        }

        $reqStr = 'Request: ';
        $reqStr .= implode(', ', array_values($request));
        $reqStr .= sprintf(" ---> %s" . PHP_EOL, var_export($result, true));

        $reqStr .= 'Hit Policy: ';
        $reqStr .= implode(', ', array_values($explains));
        if (\count($explains) > 0) {
            $reqStr .= PHP_EOL;
        }

        $this->print($reqStr);
    }

    /**
     * Log policy information.
     * 
     * @param array $policy
     * 
     * @return void
     */
    public function logPolicy(array $policy): void
    {
        if (!$this->enabled) {
            return;
        }

        $str = 'Policy: ';
        foreach ($policy as $ptype => $ast) {
            $str .= $ptype . ' : [';
            foreach ($ast as $rule) {
                $str .= '[' . implode(' ', $rule) . '] ';
            }
            $str .= PHP_EOL;
        }
        if ($str !== 'Policy: ') {
            $str = rtrim($str) . ']' . PHP_EOL;
        }

        $this->print($str);
    }

    /**
     * Log role information.
     * 
     * @param array $roles
     * 
     * @return void
     */
    public function logRole(array $roles): void
    {
        if (!$this->enabled) {
            return;
        }

        $this->printf('Roles: %s', implode(PHP_EOL, $roles));
    }

    /**
     * Log error information.
     * 
     * @param \Exception $err
     * @param string ...$msg
     * 
     * @return void
     */
    public function logError(\Exception $e, string ...$msg): void
    {
        if (!$this->enabled) {
            return;
        }

        $errorDetails = sprintf('Error: %s', $e->getMessage());

        if (!empty($msg)) {
            $errorDetails .= ' ' . implode(' ', $msg);
        }

        $this->print($errorDetails, ...$msg);
    }

    /**
     * @param mixed ...$v
     */
    protected function print(...$v): void
    {
        $content = date('Y-m-d H:i:s ');
        foreach ($v as $value) {
            if (\is_array($value)) {
                $value = json_encode($value);
            } elseif (\is_object($value)) {
                $value = json_encode($value);
            }
            $content .= $value;
        }
        $content .= PHP_EOL;
        $this->save($content);
    }

    /**
     * @param string $format
     * @param mixed  ...$v
     */
    protected function printf(string $format, ...$v): void
    {
        $content = sprintf($format, ...$v);
        $this->print($content);
    }

    /**
     * @param string $content
     */
    protected function save(string $content): void
    {
        $file = $this->path . DIRECTORY_SEPARATOR . $this->name;
        file_put_contents($file, $content, FILE_APPEND | LOCK_EX);
    }
}
