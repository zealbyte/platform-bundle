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
	use Symfony\Component\DependencyInjection\Collection;
	use Symfony\Component\DependencyInjection\Definition;
	use Symfony\Component\DependencyInjection\Parameter;
	use Symfony\Component\DependencyInjection\Reference;
	use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
	use Symfony\Component\DependencyInjection\ContainerBuilder;
	use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
	use ZealByte\Platform\Assets\Repository\RepositoryInterface;
	use ZealByte\Bundle\PlatformBundle\PackageMods;

	/**
	 * Asset Repo Compiler Pass
	 *
	 * @author Dustin Martella <dustin.martella@zealbyte.com>
	 */
	class AssetRepositoryPass implements CompilerPassInterface
	{
		const ASSET_PACKAGE_REPOSITORY_DEFAULT_PRIORITY = 10;
		const ASSET_PACKAGE_REPOSITORY_TAG = 'platform.asset_repository';
		const ASSET_PACKAGES_IGNORE_PARAMETER = 'platform.config.asset_packages.ignore';
		const PLATFORM_PACKAGE_MANAGER = 'ZealByte\Platform\Assets\PackageManager';
		const PACKAGE_MODS = '';

		public function process (ContainerBuilder $container)
		{
			if (!$container->hasDefinition(self::PLATFORM_PACKAGE_MANAGER))
				return;

			$packageManagerDefinition = $container->getDefinition(self::PLATFORM_PACKAGE_MANAGER);
			$ignorePackages = $container->getParameter(self::ASSET_PACKAGES_IGNORE_PARAMETER);

			foreach($ignorePackages as $packageName)
				$packageManagerDefinition->addMethodCall('addIgnore', [$packageName]);

			foreach ($container->findTaggedServiceIds(self::ASSET_PACKAGE_REPOSITORY_TAG) as $repositoryId => $tags) {
				$repositoryDefinition = $container->getDefinition($repositoryId);
				$repositoryClass = $container->getParameterBag()->resolveValue($repositoryDefinition->getClass());

				if (!is_subclass_of($repositoryClass, RepositoryInterface::class))
					throw new InvalidArgumentException("Asset reps must inheirt ".RepositoryInterface::class);

				$priority = isset($tags['priority']) ?
					$tags['priority'] : self::ASSET_PACKAGE_REPOSITORY_DEFAULT_PRIORITY;

				$packageManagerDefinition->addMethodCall('addRepository',
					[new Reference($repositoryId), $priority]
				);
			}

			$this->modPackages($container);
		}

		// @TODO: This is a hack
		private function modPackages (ContainerBuilder $container)
		{
			$definition = $container->getDefinition(self::PLATFORM_PACKAGE_MANAGER);
			$container->register(PackageMods::class, PackageMods::class);

			$definition
				->addMethodCall('modPackage', ['chosen', [new Reference(PackageMods::class), 'modPackageChosen']])
				->addMethodCall('modPackage', ['Flot', [new Reference(PackageMods::class), 'modPackageFlot']])
				->addMethodCall('modPackage', ['iCheck', [new Reference(PackageMods::class), 'modPackageICheck']])
				->addMethodCall('modPackage', ['jquery-ui', [new Reference(PackageMods::class), 'modPackageJqueryUi']])
				->addMethodCall('modPackage', ['jquery.sparkline', [new Reference(PackageMods::class), 'modPackageSparkline']])
				->addMethodCall('modPackage', ['jqvmap', [new Reference(PackageMods::class), 'modPackageJqvmap']])
				->addMethodCall('modPackage', ['outlayer', [new Reference(PackageMods::class), 'modPackageOutlayer']])
				->addMethodCall('modPackage', ['masonry', [new Reference(PackageMods::class), 'modPackageMasonry']])
				//->addMethodCall('modPackage', ['mui', [new Reference(PackageMods::class), 'modPackageMui']])
				->addMethodCall('modPackage', ['select2', [new Reference(PackageMods::class), 'modPackageSelect2']])
				->addMethodCall('modPackage', ['vue', [new Reference(PackageMods::class), 'modPackageVue']]);
		}

	}
}
