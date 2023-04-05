<?php namespace Model\Lock;

use Symfony\Component\Lock\SharedLockInterface;

class Acquire
{
	public static function read(SharedLockInterface $lock, ?float $timeout = null): void
	{
		self::acquire('read', $lock, $timeout);
	}

	public static function write(SharedLockInterface $lock, ?float $timeout = null): void
	{
		self::acquire('write', $lock, $timeout);
	}

	private static function acquire(string $type, SharedLockInterface $lock, ?float $timeout = null): void
	{
		$methodName = match ($type) {
			'read' => 'acquireRead',
			'write' => 'acquire',
			default => throw new \Exception('Invalid lock type'),
		};

		if ($timeout === null) {
			if (!$lock->{$methodName}())
				throw new \Exception('Can\'t acquire ' . $type . ' lock');
		} else {
			$start = microtime(true);
			while (!$lock->{$methodName}()) {
				if (microtime(true) - $start > $timeout)
					throw new \Exception($type . ' lock could not be acquired in ' . $timeout . ' seconds');

				usleep(100000);
			}
		}
	}
}
