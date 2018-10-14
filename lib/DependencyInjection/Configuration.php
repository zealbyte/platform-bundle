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
	use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
	use Symfony\Component\Config\Definition\Builder\TreeBuilder;
	use Symfony\Component\Config\Definition\ConfigurationInterface;

	/**
	 * Configuration
	 *
	 * @author Dustin Martella <dustin.martella@zealbyte.com>
	 */
	class Configuration implements ConfigurationInterface
	{
		public function getConfigTreeBuilder ()
		{
			$treeBuilder = new TreeBuilder();
			$rootNode = $treeBuilder->root('platform');

			$rootNode
				->children()
					->scalarNode('name')->defaultValue('ZealByte')->end()
					->scalarNode('theme')->defaultValue('grey')->end()
				->end();

			$this->addFluentdConfigSection($rootNode);
			$this->addContextConfigSection($rootNode);
			$this->addPackageManagerConfigSection($rootNode);
			$this->addBowerPackageManagerSection($rootNode);
			$this->addPackageManagePackages($rootNode);

			return $treeBuilder;
		}

		private function addFluentdConfigSection (ArrayNodeDefinition $rootNode) : void
		{
			$rootNode
				->children()
					->arrayNode('fluentd')
					->addDefaultsIfNotSet()
					->children()
						->scalarNode('address')->defaultValue('localhost')->end()
						->scalarNode('port')->defaultValue(24224)->end()
						->scalarNode('level')->defaultValue('error')->end()
					->end()
				->end();
		}

		private function addContextConfigSection (ArrayNodeDefinition $rootNode) : void
		{
			$rootNode
				->children()
					->arrayNode('context')
					->addDefaultsIfNotSet()
					->children()
						->scalarNode('default_view')->defaultValue('@Platform/context.html.twig')->end()
						->arrayNode('options')
							->useAttributeAsKey('context')
							->arrayPrototype()
							->children()
								->scalarNode('view')->isRequired()->cannotBeEmpty()->end()
								->arrayNode('package')
									->scalarPrototype()->end()
								->end()
							->end()
						->end()
					->end()
				->end();
		}

		private function addPackageManagerConfigSection (ArrayNodeDefinition $rootNode) : void
		{
			$rootNode
				->children()
					->arrayNode('package_manager')
					->addDefaultsIfNotSet()
					->children()
						->scalarNode('prefix')->defaultValue('')->end()
						->arrayNode('ignore')
							->scalarPrototype()->end()
						->end()
					->end()
				->end();
		}

		private function addBowerPackageManagerSection (ArrayNodeDefinition $rootNode) : void
		{
			$rootNode
				->children()
					->arrayNode('bower')
					->addDefaultsIfNotSet()
					->children()
						->booleanNode('enabled')->defaultValue(false)->end()
						->scalarNode('bin_path')->defaultValue('/usr/bin/bower')->end()
						->scalarNode('list_args')->defaultValue('list --json')->end()
						->scalarNode('working_directory')->defaultValue(null)->end()
					->end()
				->end();
		}

		private function addPackageManagePackages (ArrayNodeDefinition $rootNode) : void
		{
			$rootNode
				//->fixXmlConfig('package')
				->children()
					->arrayNode('packages')
						->arrayPrototype()
						->children()
							->scalarNode('name')->isRequired()->cannotBeEmpty()->end()
							->scalarNode('version')->isRequired()->cannotBeEmpty()->end()
							->scalarNode('baseurl')->isRequired()->cannotBeEmpty()->end()
							->scalarNode('basedir')->isRequired()->cannotBeEmpty()->end()
							->arrayNode('files')
								->isRequired()
								->requiresAtLeastOneElement()
								->scalarPrototype()->end()
							->end()
							->arrayNode('dependencies')
								->scalarPrototype()->end()
							->end()
						->end()
					->end()
				->end();
		}

	}
}
