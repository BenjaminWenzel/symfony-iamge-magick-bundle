<?php

/***************************************************************
 *
 *  This file is part of the Snowcap ImBundle package.
 *
 *  (c) 2015 Benjamin Wenzel <benjamin.wenzel@mail.de>, Hartwig Communictaion & Events
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

namespace Hartwig\Bundle\ImageMagickBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * Class HartwigImageMagickExtension
 * @package Hartwig\Bundle\ImageMagickBundle\DependencyInjection
 */
class HartwigImageMagickExtension extends Extension {

	/**
	 * @param array                                                   $configs
	 * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
	 */
	public function load( array $configs, ContainerBuilder $container ) {
		$configuration = new Configuration();
		$config = $this->processConfiguration( $configuration, $configs );

		$loader = new Loader\YamlFileLoader( $container, new FileLocator( __DIR__ . "/../Resources/config" ) );
		$loader->load( "services.yml" );

		$container->setParameter( "hartwig_image_magick.web_path", $config[ "web_path" ] );
		$container->setParameter( "hartwig_image_magick.cache_path", $config[ "cache_path" ] );
		$container->setParameter( "hartwig_image_magick.timeout", $config[ "timeout" ] );
	}
}
