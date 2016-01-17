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

namespace Hartwig\Bundle\ImageMagickBundle\Twig;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ImageExtension
 * @package Hartwig\Bundle\ImageMagickBundle\Twig
 */
class ImageExtension extends \Twig_Extension
{

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * @var \Hartwig\ImageMagickBundle\Service\ImageService
     */
    protected $imageService;

    /**
     * ImageExtension constructor.
     *
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     */
    public function __construct( ContainerInterface $container = NULL )
    {
        $this->container = $container;
        $this->imageService = $container->get( "hartwig_image_magick.service.image_service" );
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFunction(
                "imageResize",
                array(
                    $this,
                    "imageResize"
                ),
                array(
                    "pre_escape" => "html",
                    "is_safe" => array("html")
                )
            ),
        );
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction(
                "image",
                array(
                    $this,
                    "renderImage"
                ),
                array(
                    "pre_escape" => "html",
                    "is_safe" => array("html")
                )
            ),
        );
    }

    /**
     * @param string $imageSource
     * @param string $format
     *
     * @return string
     */
    public function imageResize( $imageSource, $format )
    {
        $temp = 1;

        return "";
    }

    /**
     * @param string $imageSource
     * @param string $format
     * @param null $id
     * @param string $class
     * @param string $title
     * @param string $alt
     * @param array $arguments
     * @return string
     */
    public function renderImage( $imageSource, $format = NULL, $id = NULL, $class = NULL, $title = NULL, $alt = NULL, array $arguments = array() )
    {
        if ( !empty($imageSource) ) {
            /** @var string $processedImage */
            $processedImage = $this->imageService->processImage( $imageSource, $format );
        }

        if ( !empty($processedImage) ) {
            $imageSource = $processedImage;
        }

        /** @var \Symfony\Component\Routing\RequestContext $request */
        $request = $this->container->get( "router.request_context" );
        /** @var string $url */
        $url = $request->getScheme() . "://" . $request->getHost() . "/" . $imageSource;

        /** @var array $tag */
        $tag = array();
        $tag[] = "<img";
        $tag[] = "src='" . $url . "'";
        if ( !empty($id) ) {
            $tag[] = "id='" . $id . "'";
        }
        if ( !empty($class) ) {
            $tag[] = "class='" . $class . "'";
        }
        if ( !empty($title) ) {
            $tag[] = "title='" . $title . "'";
        }
        if ( !empty($alt) ) {
            $tag[] = "alt='" . $alt . "'";
        }
        /** @var array $imageSize */
        $imageSize = getimagesize( $imageSource );
        $tag[] = "width='" . $imageSize[0] . "' height='" . $imageSize[1] . "'";

        if ( !empty($arguments) ) {
            /**
             * @var string $key
             * @var string $value
             */
            foreach ($arguments as $key => $value) {
                if ( is_string( $key ) && is_string( $value ) ) {
                    $tag[] = $key . "='" . $value . "'";
                }
            }
        }

        $tag[] = "/>";

        return implode( " ", $tag );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return "image_extension";
    }
}