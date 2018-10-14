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
	use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
	use Symfony\Component\DependencyInjection\Reference;
	use ZealByte\Platform\Context\Tag\ContextTagManager;
	use ZealByte\Platform\Context\Tag\ContextTagInterface;

	/**
	 * Context Tag Compiler Pass
	 *
	 * @author Dustin Martella <dustin.martella@zealbyte.com>
	 */
	class ContextTagRegistrationPass implements CompilerPassInterface
	{
		const CONTEXTTAG_TAG = 'platform.contexttag';
		const CONTEXTTAG_CONTAINER_DEFINITION = 'ZealByte\Platform\Context\Tag\ContextTagManager';

		public function process (ContainerBuilder $container)
		{
			if (!$container->hasDefinition(self::CONTEXTTAG_CONTAINER_DEFINITION))
				return;

			$definition = $container->getDefinition(self::CONTEXTTAG_CONTAINER_DEFINITION);

			foreach ($container->findTaggedServiceIds(self::CONTEXTTAG_TAG) as $id => $tags) {
				$tagDefinition = $container->getDefinition($id);
				$tagClass = $container->getParameterBag()->resolveValue($tagDefinition->getClass());

				if (!is_subclass_of($tagClass, ContextTagInterface::class))
					throw new \InvalidArgumentException("Service \"$id\" must implement ".ContextTagInterface::class.".");

				foreach ($tags as $attributes) {
					if (empty($attributes['alias']))
						throw new InvalidArgumentException("The \"platform.context\" tag for \"$id\" must have an alias attribute.");

					$definition->addMethodCall('registerTag', [new Reference($id), $attributes['alias']]);
				}
			}
		}

	}
}
