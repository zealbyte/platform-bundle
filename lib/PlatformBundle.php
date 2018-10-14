<?php

/*
 * This file is part of the ZealByte Platform Bundle.
 *
 * (c) ZealByte <info@zealbyte.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ZealByte\Bundle\PlatformBundle
{
	use Symfony\Component\HttpKernel\Bundle\Bundle;
	use Symfony\Component\DependencyInjection\ContainerBuilder;
	use Symfony\Component\DependencyInjection\Compiler\PassConfig;
	use ZealByte\Bundle\PlatformBundle\DependencyInjection\CompilerPass\AssetRepositoryPass;
	use ZealByte\Bundle\PlatformBundle\DependencyInjection\CompilerPass\BowerAssetRepositoryPass;
	use ZealByte\Bundle\PlatformBundle\DependencyInjection\CompilerPass\ContextTagRegistrationPass;
	use ZealByte\Bundle\PlatformBundle\DependencyInjection\CompilerPass\PlatformAssetRepositoryPass;

	/**
	 * Platform Bundle
	 *
	 * @author Dustin Martella <dustin.martella@zealbyte.com>
	 */
	class PlatformBundle extends Bundle
	{
		const VERSION = '0.3a';

    /**
     * Boots the Bundle.
     */
    public function boot ()
    {
    }

    /**
     * Shutdowns the Bundle.
     */
    public function shutdown ()
    {
    }

    /**
     * Builds the bundle.
     */
		public function build (ContainerBuilder $container)
		{
			$container->addCompilerPass(new ContextTagRegistrationPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION);
			$container->addCompilerPass(new PlatformAssetRepositoryPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION);
			$container->addCompilerPass(new BowerAssetRepositoryPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION);
			$container->addCompilerPass(new AssetRepositoryPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION);
		}

	}
}
