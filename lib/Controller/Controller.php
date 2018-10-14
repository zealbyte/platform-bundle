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
	use Symfony\Bundle\FrameworkBundle\Controller\Controller as SymfonyController;

	/**
	 * Platform Controller Abstract
	 *
	 * @author Dustin Martella <dustin.martella@zealbyte.com>
	 */
	abstract class Controller extends SymfonyController
	{
		use ContextControllerTrait;
	}
}
