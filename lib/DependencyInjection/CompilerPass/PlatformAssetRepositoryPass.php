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
	use ZealByte\Platform\Assets\PackageFile;
	use ZealByte\Platform\Assets\Package;
	use ZealByte\Util;

	/**
	 * Platform Assets Repo Compiler Pass
	 *
	 * @author Dustin Martella <dustin.martella@zealbyte.com>
	 */
	class PlatformAssetRepositoryPass implements CompilerPassInterface
	{
		const CONFIGURED_PACKAGES_PARAMETER = 'platform.asset_packages.platform_packages';
		const PLATFORM_PACKAGE_REPOSITORY = 'ZealByte\Platform\Assets\Repository\Repository';

		public function process (ContainerBuilder $container)
		{
			if (!$container->hasParameter(self::CONFIGURED_PACKAGES_PARAMETER))
				return;

			$this->applyConfiguredPackages($container);
		}

		private function applyConfiguredPackages (ContainerBuilder $container) : void
		{
			if (!$container->hasDefinition(self::PLATFORM_PACKAGE_REPOSITORY))
				return;

			$configuredPackages = $container->getParameter(self::CONFIGURED_PACKAGES_PARAMETER);

			foreach ($configuredPackages as $packageParams)
				$this->applyConfiguredPackage($container, $packageParams);
		}

		private function applyConfiguredPackage (ContainerBuilder $container, array $package_params)
		{
			$repository = $container->getDefinition(self::PLATFORM_PACKAGE_REPOSITORY);
			$packageId = Util\Canonical::name(self::CONFIGURED_PACKAGES_PARAMETER.'.'.$package_params['name']);

			$packageDefinition = $container->register($packageId, Package::class)
				->addMethodCall('setName',    [$package_params['name']])
				->addMethodCall('setVersion', [$package_params['version']])
				->addMethodCall('setBaseUrl', [$package_params['baseurl']])
				->addMethodCall('setBasedir', [$package_params['basedir']]);

			if (!isset($package_params['dependencies']))
				$package_params['dependencies'] = [];

			foreach ($package_params['dependencies'] as $dependencyName)
				$packageDefinition->addMethodCall('addDependency', [$dependencyName]);

			foreach ($package_params['files'] as $filePath) {
				$fileDefinition = (new Definition(PackageFile::class))
					->addMethodCall('setPath', [$filePath]);

				$packageDefinition->addMethodCall('addFile', [$fileDefinition]);
			}

			$repository->addMethodCall('addPackage', [new Reference($packageId)]);
		}

	}
}
