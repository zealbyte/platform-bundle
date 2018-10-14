<?php

/*
 * This file is part of the ZealByte Platform Bundle.
 *
 * (c) ZealByte <info@zealbyte.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ZealByte\Bundle\PlatformBundle\Menu
{
	use Symfony\Component\Routing\RouteCollection;
	use Symfony\Component\Routing\RouterInterface;
	use Symfony\Component\Translation\TranslatorInterface;
	use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
	use Knp\Menu\FactoryInterface;
	use Knp\Menu\MenuItem;
	use ZealByte\Bundle\MenuBundle\Builder\MenuBuilderInterface;

	/**
	 * Route Option Menu Builder
	 *
	 * @author Dustin Martella <dustin.martella@zealbyte.com>
	 */
	class RouteOptionMenuBuilder implements MenuBuilderInterface
	{
		private $router;

		private $translator;

		private $authorizationChecker;

		private $optionName;

		private $children = [];

		public function __construct (RouterInterface $router, TranslatorInterface $translator = null, AuthorizationCheckerInterface $authorization_checker = null)
		{
			$this->setRouter($router);

			if ($translator)
				$this->setTranslator($translator);

			if ($authorization_checker)
				$this->setAuthorizationChecker($authorization_checker);
		}

		public function build (FactoryInterface $factory, string $name, array $options = []) : MenuItem
		{
			$menu = $this->initMenu($factory, $options);
			$this->buildMenuRoute($menu, $options);

			return $menu;
		}

		public function has (string $name) : bool
		{
			return ($name == $this->optionName);
		}

		public function setTranslator (TranslatorInterface $translator) : self
		{
			$this->translator = $translator;

			return $this;
		}

		public function setAuthorizationChecker (AuthorizationCheckerInterface $authorization_checker)
		{
			$this->authorizationChecker = $authorization_checker;
		}

		public function setRouter (RouterInterface $router) : self
		{
			$this->router = $router;

			return $this;
		}

		public function setMenuName (string $name) : MenuBuilderInterface
		{
			$this->optionName = $name;

			return $this;
		}

		private function initMenu (FactoryInterface $factory, array $options) : MenuItem
		{
			$name = $this->optionName;

			$options = array_merge($options, [
				'label' => $this->findLabel($name)
			]);

			return $factory->createItem($name, $options);
		}

		private function buildMenuRoute (MenuItem $menu, array $options)
		{
			$this->children = [$this->optionName => $menu];

			foreach ($this->router->getRouteCollection() as $routeName => $route) {
				$value = $route->getOption($this->optionName);

				if ($this->authorizationChecker && $route->hasDefault('_role'))
					$options['role'] = $route->getDefault('_role');

				if ($value) {
					$paths = $this->findMenuPaths([$value, $routeName]);
					$this->buildMenuPaths($routeName, $paths, $options);
				}
			}
		}

		private function buildMenuPaths (string $route_name, array $paths, array $options)
		{
			foreach ($paths as $path)
				$this->buildMenuRouteItem($route_name, $path, $options);
		}

		private function buildMenuRouteItem (string $route_name, string $path, array $options)
		{
			$paths = explode('/', $path);
			$path = array_pop($paths);
			$name = $this->translator ? strtolower($path) : $path;
			$parent = $this->buildMenuRouteItemParents($paths, $options);
			$child = $this->buildMenuRouteItemChild($route_name, $name, $parent, $options);
		}

		private function buildMenuRouteItemChild (string $route_name, string $name, string $parent, array $options)
		{
			$child = "$parent.$name";

			if (empty($this->children[$child])) {
				$this->newChild($route_name, $name, $parent, $child, $options);
			} else {
				$this->replaceChild($route_name, $name, $parent, $child, $options);
			}

			return $child;
		}

		private function buildMenuRouteItemParents (array $paths, array $options)
		{
			$parent = $this->optionName;

			foreach ($paths as $path) {
				$name = $this->translator ? strtolower($path) : $path;
				$child = "$parent.$name";

				if (empty($this->children[$child])) {
					$options = array_merge($options, [
						'label' => $this->findLabel($name),
					]);

					$this->children[$child] = $this->children[$parent]->addChild($name, $options);
				}

				$parent = $child;
			}

			return $parent;
		}

		private function newChild (string $route_name, string $name, string $parent, string $child, array $options)
		{
			$options = array_merge($options, [
				'label' => $this->findLabel($name),
				'route' => $route_name
			]);

			$this->children[$child] = $this->children[$parent]->addChild($name, $options);
		}

		private function replaceChild (string $route_name, string $name, string $parent, string $child, array $options)
		{
			$orphans = $this->children[$child]->getChildren();
			$this->children[$parent]->removeChild($name);
			$this->newChild($route_name, $name, $parent, $child, $options);

			if (!empty($orphans)) {
				$this->children[$child]->setChildren($orphans);

				foreach ($orphans as $orphan)
					$orphan->setParent($this->children[$child]);
			}
		}

		private function findMenuPaths (array $candidates) : array
		{
			foreach ($candidates as $candidate) {
				if (is_string($candidate)) {
					return [$this->filterMenuPath($candidate)];
				} elseif (is_array($candidate)) {
					return array_map([$this, 'filterMenuPath'], $candidate);
				}
			}

			return [];
		}

		private function filterMenuPath (string $path) : string
		{
			return str_replace(['\\'], '/', $path);
		}

		private function findLabel (string $name)
		{
			return $this->translator ? $this->translator->trans("menu.$this->optionName.$name") : $name;
		}

	}
}
