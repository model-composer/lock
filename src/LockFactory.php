<?php namespace Model\Lock;

use Symfony\Component\Lock\LockInterface;

class LockFactory extends \Symfony\Component\Lock\LockFactory
{
	/**
	 * Creates a lock for the given resource.
	 *
	 * @param string $resource The resource to lock
	 * @param float|null $ttl Maximum expected lock duration in seconds
	 * @param bool $autoRelease Whether to automatically release the lock or not when the lock instance is destroyed
	 * @param float|null $timeout Seconds to wait for the lock to be aquired - if null, it will not be a blocking wait
	 *
	 * @return LockInterface
	 */
	public function createLock(string $resource, ?float $ttl = 300.0, bool $autoRelease = true, ?float $timeout = null): LockInterface
	{
		$lock = parent::createLock($resource, $ttl, $autoRelease);

		if ($timeout === null) {
			if (!$lock->acquire())
				throw new \Exception('Can\'t acquire ' . $resource . ' lock');
		} else {
			$start = microtime(true);
			while (!$lock->acquire()) {
				if (microtime(true) - $start > $timeout)
					throw new \Exception('Lock ' . $resource . 'could not be acquired in ' . $timeout . ' seconds');

				usleep(100000);
			}
		}

		return $lock;
	}
}
