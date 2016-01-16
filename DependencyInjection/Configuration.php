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

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration
 * @package Hartwig\Bundle\ImageMagickBundle\DependencyInjection
 */
class Configuration implements ConfigurationInterface {

	/**
	 * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder
	 */
	public function getConfigTreeBuilder() {
		$treeBuilder = new TreeBuilder();
		$rootNode = $treeBuilder->root( 'hartwig_image_magick' );

		$rootNode
				->children()
				->scalarNode( "cache_path" )
				->info( "Relative path to the image cache folder." )
				->defaultValue( "cache/im" )
				->end()
				->scalarNode( "web_path" )
				->info( "Relative path to the web folder." )
				->defaultValue( "../web" )
				->end()
				->integerNode( "timeout" )
				->info( "Sets the process timeout (max. runtime)." )
				->defaultValue( 60 )
				->end()
				->end();


		// Here you should define the parameters that are allowed to
		// configure your bundle. See the documentation linked above for
		// more information on that topic.

		return $treeBuilder;
	}
}
