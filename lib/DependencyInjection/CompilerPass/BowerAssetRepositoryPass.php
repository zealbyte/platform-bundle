<?php

/*
 * This file is part of the ZealByte Platform Bundle.
 *
 * (c) ZealByte <info@zealbyte.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ZealByte\Bundle\PlatformBundle\DependencyInjection\CompilerPass
{
	use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
	use Symfony\Component\DependencyInjection\ContainerBuilder;
	use ZealByte\Bundle\PlatformBundle\BowerPackageManager;

	/**
	 * Bower Asset Repo Compiler Pass
	 *
	 * @author Dustin Martella <dustin.martella@zealbyte.com>
	 */
	class BowerAssetRepositoryPass implements CompilerPassInterface
	{
		const ASSET_PACKAGE_REPOSITORY_TAG = 'platform.asset_repository';
		const BOWER_ENABLED_PROPERTY = 'platform.asset_packages.repository.bower.enabled';
		const BOWER_PACKAGE_REPOSITORY = 'ZealByte\Platform\Assets\Repository\BowerRepository';

		public function process (ContainerBuilder $container)
		{
			if (!$container->hasDefinition(self::BOWER_PACKAGE_REPOSITORY))
				return;

			if (!$container->getParameter(self::BOWER_ENABLED_PROPERTY))
				return;

			$bowerRepositoryDefinition = $container->getDefinition(self::BOWER_PACKAGE_REPOSITORY);
			$bowerRepositoryDefinition->addTag(self::ASSET_PACKAGE_REPOSITORY_TAG);
		}

	}
}
