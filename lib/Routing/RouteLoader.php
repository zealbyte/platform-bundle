<?php

/*
 * This file is part of the ZealByte Platform Bundle.
 *
 * (c) ZealByte <info@zealbyte.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ZealByte\Bundle\PlatformBundle\Routing
{
	use RuntimeException;
	use Symfony\Component\Config\Loader\FileLoader;
	use Symfony\Component\Config\Resource\FileResource;
	use Symfony\Component\Routing\Route;
	use Symfony\Component\Routing\RouteCollection;
	use Symfony\Component\Yaml\Exception\ParseException;
	use Symfony\Component\Yaml\Parser as YamlParser;
	use Symfony\Component\Yaml\Yaml;

	/**
	 * Platform Route Loader
	 *
	 * @author Dustin Martella <dustin.martella@zealbyte.com>
	 */
	class RouteLoader extends FileLoader
	{
		private $yamlParser;

		private $types = [
			'platform' => [],
		];

		public function load ($resource, $type = null)
		{
			if (!array_key_exists($type, $this->types))
				throw new RuntimeException("The identity route loader does not have a $type type.");

			return $this->loadRoutes($resource, $type);
		}

		public function supports ($resource, $type = null)
		{
			return in_array($type, array_keys($this->types));
		}

		private function loadRoutes (string $resource, string $type)
		{
			$path = $this->locator->locate($resource);

			if (!stream_is_local($path))
				throw new \InvalidArgumentException(sprintf('This is not a local file "%s".', $path));

			if (!file_exists($path))
				throw new \InvalidArgumentException(sprintf('File "%s" not found.', $path));

			if (null === $this->yamlParser) {
				$this->yamlParser = new YamlParser();
			}

			try {
				$config = $this->yamlParser->parseFile($path, Yaml::PARSE_CONSTANT);
			} catch (ParseException $e) {
				throw new \InvalidArgumentException(sprintf('The file "%s" does not contain valid YAML.', $path), 0, $e);
			}

			$collection = new RouteCollection();
			$collection->addResource(new FileResource($path));

			// empty file
			if (null === $config)
				return $collection;

			// not an array
			if (!\is_array($config))
				throw new \InvalidArgumentException(sprintf('The file "%s" must contain a YAML array.', $path));

			foreach ($this->loadRoutesGenerator($type, $config) as $name => $args) {
				if (!is_array($args) || !isset($args['path']))
					throw new InvalidArgumentException("All defined routes must have a path.");

				$collection->add($name, $this->buildRoute($args));
			}

			return $collection;
		}

		private function buildRoute (array $args) : Route
		{
			$route = new Route($args['path']);

			$this->applyDefaults($route, $args);
			$this->applyRequirements($route, $args);
			$this->applyOptions($route, $args);
			$this->applyHost($route, $args);
			$this->applySchemes($route, $args);
			$this->applyMethods($route, $args);
			$this->applyCondition($route, $args);

			return $route;
		}

		private function applyDefaults (Route $route, array $args) : void
		{
			$defaults = [];

			if (isset($args['defaults']) && is_array($args['defaults']))
				$defaults = $args['defaults'];

			if (isset($args['controller']))
				$defaults['_controller'] = $args['controller'];

			if (isset($args['role']))
				$defaults['_role'] = $args['role'];

			if (isset($args['context']))
				$defaults['_context'] = $args['context'];

			$route->setDefaults($defaults);
		}

		private function applyRequirements (Route $route, array $args) : void
		{
			if (isset($args['requirements']))
				$route->setRequirements($args['requirements']);
		}

		private function applyOptions (Route $route, array $args) : void
		{
			$options = [];

			if (isset($args['options']) && is_array($args['options']))
				$options = $args['options'];

			if (isset($args['navigation']))
				$options['navigation'] = $args['navigation'];

			$route->setOptions($options);
		}

		private function applyHost (Route $route, array $args) : void
		{
			if (isset($args['host']))
				$route->setHost($args['host']);
		}

		private function applySchemes (Route $route, array $args) : void
		{
			if (isset($args['schemes']))
				$route->setSchemes($args['schemes']);
		}

		private function applyMethods (Route $route, array $args) : void
		{
			if (isset($args['methods']))
				$route->setMethods($args['methods']);
		}

		private function applyCondition (Route $route, array $args) : void
		{
			if (isset($args['condition']))
				$route->setCondition($args['condition']);
		}

		private function loadRoutesGenerator (string $type, array $config)
		{
			$filteredConfig = [];

			foreach ($config as $name => $params) {
				$name = !empty($params['name']) ? $params['name'] : $name;

				$filteredConfig[$name] = $params;
			}

			return $filteredConfig;
		}

	}
}
