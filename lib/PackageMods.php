<?php

/*
 * This file is part of the ZealByte Platform Bundle.
 *
 * (c) ZealByte <info@zealbyte.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace ZealByte\Bundle\PlatformBundle
{
	use ZealByte\Platform\Assets\PackageInterface;
	use ZealByte\Platform\Assets\PackageFile;

	/**
	 * @todo this is a hack -- we need to stop using bower
	 *
	 * @author Dustin Martella <dustin.martella@zealbyte.com>
	 */
	class PackageMods
	{
		/**
		 *
		 */
		public function modPackageBootstrap (PackageInterface $package)
		{
			$package->addFile(new PackageFile('/dist/css/bootstrap.min.css'));
		}

		/**
		 *
		 */
		public function modPackageChosen (PackageInterface $package)
		{
			$package->addDependency('jquery');
		}

		/**
		 *
		 */
		public function modPackageFlot (PackageInterface $package)
		{
			$package->addFile(new PackageFile('jquery.flot.time.js'));
			$package->addFile(new PackageFile('jquery.flot.resize.js'));
			$package->addFile(new PackageFile('jquery.flot.pie.js'));
		}

		/**
		 *
		 */
		public function modPackageICheck (PackageInterface $package)
		{
			$package->addFile(new PackageFile('skins/all.css'));
		}

		/**
		 *
		 */
		public function modPackageJqueryUi (PackageInterface $package)
		{
			$package->addFile(new PackageFile('/themes/base/base.css'));
		}

		/**
		 *
		 */
		public function modPackageJqvmap (PackageInterface $package)
		{
			$package->addFile(new PackageFile('/dist/maps/jquery.vmap.world.js'));
			$package->addFile(new PackageFile('/examples/js/jquery.vmap.sampledata.js'));
		}

		/**
		 *
		 */
		public function modPackageMasonry (PackageInterface $package)
		{
			foreach ($package->getFiles() as $file)
				if ('masonry.js' == $file->getBasename())
					$package->delFile($file);

			$package->addFile(new PackageFile('/dist/masonry.pkgd.min.js'));
		}

		/**
		 *
		 */
		public function modPackageMui (PackageInterface $package)
		{
			foreach ($package->getFiles() as $file)
				if ('mui.js' == $file->getBasename())
					$package->delFile($file);
		}

		/**
		 *
		 */
		public function modPackageNicescroll (PackageInterface $package)
		{
			foreach ($package->getFiles() as $file)
				if ('jquery.nicescroll.js' == $file->getBasename())
					$package->delFile($file);

			$package->addFile(new PackageFile('jquery.nicescroll.min.js'));
		}

		/**
		 *
		 */
		public function modPackageOutlayer (PackageInterface $package)
		{
			foreach ($package->getFiles() as $file)
				if ('outlayer.js' == $file->getBasename())
					$package->delFile($file);

			$package->addFile(new PackageFile('item.js'));
			$package->addFile(new PackageFile('outlayer.js'));
		}

		/**
		 *
		 */
		public function modPackageSelect2 (PackageInterface $package)
		{
			$package->addFile(new PackageFile('/dist/css/select2.min.css'));
		}

		/**
		 *
		 */
		public function modPackageSparkline (PackageInterface $package)
		{
			$package->addFile(new PackageFile('/dist/jquery.sparkline.js'));
		}

		/**
		 *
		 */
		public function modPackageVue (PackageInterface $package)
		{
			$package->addFile(new PackageFile('/dist/vue.js'));
		}
	}
}
