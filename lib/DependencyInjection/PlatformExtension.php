<?php

/*
 * This file is part of the ZealByte Platform Bundle.
 *
 * (c) ZealByte <info@zealbyte.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ZealByte\Bundle\PlatformBundle\DependencyInjection
{
	use ReflectionClass;
	use RuntimeException;
	use Symfony\Component\Config\FileLocator;
	use Symfony\Component\DependencyInjection\ContainerBuilder;
	use Symfony\Component\DependencyInjection\Loader;
	use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
	use Symfony\Component\HttpKernel\DependencyInjection\Extension;
	use ZealByte\Platform\ZealBytePlatform;
	use ZealByte\Platform\DBAL\Types;
	use ZealByte\Platform\Context\Tag\ContextTagInterface;
	use ZealByte\Bundle\PlatformBundle\PlatformBundle;
	use ZealByte\Bundle\PlatformBundle\DependencyInjection\CompilerPass\ContextTagRegistrationPass;
	use ZealByte\Platform\Assets\Package;
	use ZealByte\Platform\Assets\PackageFile;

	/**
	 * Platform Extension
	 *
	 * @author Dustin Martella <dustin.martella@zealbyte.com>
	 */
	class PlatformExtension extends Extension implements PrependExtensionInterface
	{
		/**
		 * {@inheritdoc}
		 */
		public function load (array $configs, ContainerBuilder $container)
		{
			$loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
			$loader->load('platform.xml');
			$loader->load('contexttags.xml');

			$configuration = $this->getConfiguration($configs, $container);
			$config = $this->processConfiguration($configuration, $configs);
			$pmConfig = $config['package_manager'];
			$fluentdConfig = $config['fluentd'];
			$contextConfig = $config['context'];

			$container->setParameter('platform.logger.fluentd.address', $fluentdConfig['address']);
			$container->setParameter('platform.logger.fluentd.port', intval($fluentdConfig['port']));
			$container->setParameter('platform.logger.fluentd.level', $this->levelToMonologConst($fluentdConfig['level']));

			$container->setParameter('platform.name', $config['name']);
			$container->setParameter('platform.theme', $config['theme']);
			$container->setParameter('platform.config.asset_packages.prefix', $pmConfig['prefix']);
			$container->setParameter('platform.config.asset_packages.ignore', $pmConfig['ignore']);

			$container->setParameter('platform.config.context.default_view', $contextConfig['default_view']);
			$container->setParameter('platform.config.context.options', $contextConfig['options']);

			$this->loadBowerConfigs($container, $config['bower']);

			if (!empty($config['packages']))
				$this->addAssetPackageManagerPackages($container, $config['packages']);
		}

		/**
		 * {@inheritdoc}
		 */
		public function prepend (ContainerBuilder $container)
		{
			$this->prependPlatform($container);
			$this->prependDoctrine($container);
			$this->prependFramework($container);
			$this->prependTwig($container);
		}

		private function levelToMonologConst ($level)
		{
			return is_int($level) ? $level : constant('Monolog\Logger::'.strtoupper($level));
		}

		private function addAssetPackageManagerPackages (ContainerBuilder $container, array $packages_config)
		{
			$container->setParameter('platform.asset_packages.platform_packages', $packages_config);
		}

		private function loadBowerConfigs (ContainerBuilder $container, array $bower_config)
		{
			$container->setParameter('platform.asset_packages.repository.bower.enabled', $bower_config['enabled']);
			$container->setParameter('platform.asset_packages.repository.bower.command_path', $bower_config['bin_path']);
			$container->setParameter('platform.asset_packages.repository.bower.command_arguments', $bower_config['list_args']);
		}

		private function prependPlatform (ContainerBuilder $container) : void
		{
			if (!$container->hasExtension('platform'))
				return;

			$config = [
				'packages' => [
					[
						'name' => 'zealbyte.platform',
						'version' => PlatformBundle::VERSION,
						'baseurl' => '/',
						'basedir' => '/bundles/platform',
						'files' => [
							'css/zbase.css',
							'js/zrequire.js',
						],
						'dependencies' => []
					],
				],
			];

			$container->prependExtensionConfig('platform', $config);
		}

		private function prependDoctrine (ContainerBuilder $container) : void
		{
			if (!$container->hasExtension('doctrine'))
				return;

			$config = [
				'dbal' => [
					'types' => [
						'csv' => Types\CsvType::class,
						'price' => Types\PriceType::class,
						'date' => Types\ZuluDateType::class,
						'datetime' => Types\ZuluDateTimeType::class,
					],
					'mapping_types' => [
						'enum' => 'string'
					],
				],
			];

			$container->prependExtensionConfig('doctrine', $config);
		}

		private function prependFramework (ContainerBuilder $container) : void
		{
			if (!$container->hasExtension('framework'))
				throw new RuntimeException('The zealbyte platform requires the framework extension.');

			$config = [
				'session' => [
					'storage_id' => 'session.storage.native',
					'handler_id' => 'session.handler.native_file',
					//'name' => 'SESSID',
					//'cookie_lifetime' => 3600,
					//'cookie_path' => '/',
					//'cookie_domain' => '',
					//'cookie_secure' => false,
					//'cookie_httponly' => true,
					//'gc_divisor' => 100,
					//'gc_probability' => 1,
					//'gc_maxlifetime' => 1440,
				]
			];

			$container->prependExtensionConfig('framework', $config);
		}

		private function prependTwig (ContainerBuilder $container) : void
		{
			if (!$container->hasExtension('twig'))
				throw new RuntimeException('The zealbyte platform requires the twig extension.');

			$config = [
				'paths' => [
					dirname((new ReflectionClass(ZealBytePlatform::class))->getFileName()).'/Resources/views' => 'Platform',
				],
			];

			$container->prependExtensionConfig('twig', $config);
		}

		public function getXsdValidationBasePath ()
		{
			return __DIR__.'/../Resources/config/schema';
		}

		public function getNamespace ()
		{
			return 'https://zealbyte.com/zealbyte/schema/dic/platform';
		}
	}
}
