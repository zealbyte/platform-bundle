<?php

/*
 * This file is part of the ZealByte Platform Bundle.
 *
 * (c) ZealByte <info@zealbyte.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ZealByte\Bundle\PlatformBundle\Controller
{
	use RuntimeException;
	use Symfony\Component\HttpFoundation\Request;
	use ZealByte\Platform\Context\ContextInterface;
	use ZealByte\Platform\Component\ComponentInterface;
	use ZealByte\Platform\ZealBytePlatform;

	/**
	 * Platform Controller Trait
	 *
	 * @author Dustin Martella <dustin.martella@zealbyte.com>
	 */
	trait ContextControllerTrait
	{
		public function createContext (Request $request, ?ComponentInterface $component = null, ?array $tags = null, ?string $view = null, ?int $status = null) : ContextInterface
		{
			if (!$this->has(ZealBytePlatform::CONTEXT_FACTORY))
				throw new RuntimeException("Context Factory Service is not loaded.");

			if ($tags)
				$this->setContextTags($tags);

			return $this->get(ZealBytePlatform::CONTEXT_FACTORY)->createContext($request, $component, $view, $status);
		}

		public function setContextTag (string $tag, $value) : void
		{
			if (!$this->has(ZealBytePlatform::CONTEXT_TAGS))
				throw new RuntimeException("Context Tag Manager Service is not loaded.");

			$this->get(ZealBytePlatform::CONTEXT_TAGS)->set($tag, $value);
		}

		public function setContextTags (array $tags) : void
		{
			if (!$this->has(ZealBytePlatform::CONTEXT_TAGS))
				throw new RuntimeException("Context Tag Manager Service is not loaded.");

			$contextTags = $this->get(ZealBytePlatform::CONTEXT_TAGS);

			foreach ($tags as $tag => $value)
				if ($contextTags->has($tag))
					$contextTags->set($tag, $value);
		}

	}
}
