<?php

namespace Hiraeth\Cache;

use Hiraeth;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\ChainAdapter;
use RuntimeException;

/**
 *
 */
class PoolManagerProvider implements Hiraeth\Provider
{
	/**
	 * {@inheritDoc}
	 */
	static public function getInterfaces(): array
	{
		return [
			Hiraeth\Caching\PoolManager::class
		];
	}


	/**
	 * {@inheritDoc}
	 *
	 * @param Caching\PoolManager $instance
	 */
	public function __invoke(object $instance, Hiraeth\Application $app): object
	{
		$ephemeral = new ArrayAdapter();
		$defaults  = [
			'class'    => NULL,
			'disabled' => FALSE,
			'options'  => array()
		];

		foreach ($app->getConfig('*', 'cache', $defaults) as $path => $config) {
			if (!$config['class']) {
				continue;
			}

			$name    = basename($path);
			$drivers = array();

			if ($instance->has($name)) {
				throw new RuntimeException(sprintf(
					'Cannot configure cache "%s", another cache already exists with that name',
					$name
				));
			}

			if (!$config['disabled']) {
				$drivers[] = $app->get($config['class'], $config['options']);
			}

			$instance->add($name, new ChainAdapter($drivers + [$ephemeral]));
		}

		return $instance;
	}
}
