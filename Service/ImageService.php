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

namespace Hartwig\Bundle\ImageMagickBundle\Service;
use Hartwig\Bundle\ImageMagickBundle\Exception\InvalidArgumentException;
use Hartwig\Bundle\ImageMagickBundle\Exception\RuntimeException;
use Hartwig\Bundle\ImageMagickBundle\Exception\FileNotFoundException;
use Symfony\Component\Process\Process;

/**
 * Class ImageService
 * @package Hartwig\Bundle\ImageMagickBundle\Service
 */
class ImageService {

	/**
	 * @const string
	 */
	const SECURITY_KEY = "Js5L8E7QS";

	/**
	 * @var string
	 */
	protected $rootDir = "";

	/**
	 * @var string
	 */
	protected $webDir = "";

	/**
	 * @var string
	 */
	protected $cacheDir = "";

	/**
	 * @var string
	 */
	protected $binDir = "";

	/**
	 * ImageMagickWrapper constructor.
	 *
	 * @param string $rootDir
	 * @param string $webDir
	 * @param string $cacheDir
	 */
	public function __construct( $rootDir, $webDir, $cacheDir ) {
		$this->rootDir = $rootDir;
		$this->webDir = $webDir;
		$this->cacheDir = $cacheDir;
		$this->binDir = str_replace( "/convert", "", exec( "which convert" ) );
	}

	/**
	 * @return string
	 */
	private function getAbsWebDir() {
		return realpath( $this->rootDir . "/" . $this->webDir );
	}

	/**
	 * @return string
	 */
	private function getAbsCacheDir() {
		/** @var string $absCacheDir */
		$absCacheDir = $this->getAbsWebDir() . "/" . $this->cacheDir;
		if( !is_dir( $absCacheDir ) ) {
			mkdir( $absCacheDir, 0777, TRUE );
		}

		return $absCacheDir;
	}

	/**
	 * @param string $image
	 * @param string $format
	 *
	 * @return string
	 */
	public function processImage( $image, $format ) {
		$image = ltrim( $image );
		$this->checkImageExists( $image );

		/** @var array $pathInfo */
		$pathInfo = pathinfo( $image );
		/** @var array $imageSize */
		$imageSize = getimagesize( $image );
		/** @var array $imageData */
		$imageData = array(
				"path"      => $pathInfo[ "dirname" ],
				"filename"  => $pathInfo[ "basename" ],
				"extension" => $pathInfo[ "extension" ],
				"width"     => $imageSize[ 0 ],
				"height"    => $imageSize[ 1 ],
				"mimeType"  => $imageSize[ "mime" ]
		);

		if( preg_match( "/^([0-9]+)x([0-9]+)$/", $format, $matches ) ) {
			$processingInstructions = array(
					"command" => "convert",
					"width"   => intval( $matches[ 1 ] ),
					"height"  => intval( $matches[ 2 ] )
			);
		} elseif( preg_match( "/^([0-9]+)x$/", $format, $matches ) ) {
			$processingInstructions = array(
					"command" => "convert",
					"width"   => intval( $matches[ 1 ] ),
					"height"  => intval( ( $imageData[ "height" ] * ( intval( $matches[ 1 ] ) ) ) / $imageData[ "width" ] )
			);
		} elseif( preg_match( "/^([0-9]+c)x([0-9]+)$/", $format, $matches ) ) {
			$processingInstructions = array(
					"command" => "convert",
					"width"   => intval( ( $imageData[ "width" ] * ( intval( $matches[ 2 ] ) ) ) / $imageData[ "height" ] ) . "^",
					"height"  => intval( $matches[ 2 ] ),
					"options" => array(
							"-gravity" => "center",
							"-extent"  => rtrim( $matches[ 1 ], "c" ) . "x" . intval( $matches[ 2 ] )
					)
			);
		} elseif( preg_match( "/^([0-9]+)x([0-9]+c)$/", $format, $matches ) ) {
			$processingInstructions = array(
					"command" => "convert",
					"width"   => intval( $matches[ 1 ] ),
					"height"  => intval( ( $imageData[ "height" ] * ( intval( $matches[ 1 ] ) ) ) / $imageData[ "width" ] ) . "^",
					"options" => array(
							"-gravity" => "center",
							"-extent"  => intval( $matches[ 1 ] ) . "x" . rtrim( $matches[ 2 ], "c" )
					)
			);
		} elseif( preg_match( "/^([0-9]+c)x([0-9]+c)$/", $format, $matches ) ) {
			/** @var bool $cropWidth */
			$cropWidth = FALSE;
			/** @var bool $cropHeight */
			$cropHeight = FALSE;
			/** @var int $desiredWidth */
			$desiredWidth = rtrim( $matches[ 1 ], "c" );
			/** @var int $desiredHeight */
			$desiredHeight = rtrim( $matches[ 2 ], "c" );
			/** @var double $factor */
			$factor = $imageData[ "width" ] / $desiredWidth;
			/** @var int $heightAfterResize */
			$heightAfterResize = intval( $imageData[ "height" ] / $factor );
			if( $heightAfterResize > $desiredHeight ) {
				$cropHeight = TRUE;
			} elseif( $heightAfterResize < $desiredHeight ) {
				$cropWidth = TRUE;
			}
			$processingInstructions = array(
					"command" => "convert",
					"width"   => $cropWidth ? intval( $imageData[ "width" ] / $factor ) . "^" : $desiredWidth,
					"height"  => $cropHeight ? intval( $imageData[ "height" ] / $factor ) . "^" : $desiredHeight
			);
			if( $cropWidth || $cropHeight ) {
				$processingInstructions[ "options" ] = array(
						"-gravity" => "center",
						"-extent"  => $desiredWidth . "x" . $desiredHeight
				);
			}
		} else {
			throw new \InvalidArgumentException( "Unsupported format" );
		}
		/** @var string $processedFilename */
		$processedFilename = $this->generateFilename( $image, $processingInstructions );
		if( !file_exists( $this->getAbsCacheDir() . "/" . $processedFilename ) ) {
			$this->exec( $this->getAbsWebDir() . "/" . $image, $this->getAbsCacheDir() . "/" . $processedFilename, $processingInstructions );
		}
		$image = $this->cacheDir . "/" . $processedFilename;

		return $image;
	}

	/**
	 * @param string $image
	 *
	 * @throws FileNotFoundException
	 */
	private function checkImageExists( $image ) {
		if( !file_exists( $this->webDir . "/" . $image ) ) {
			throw new FileNotFoundException( "Image could not be found." );
		}
	}

	/**
	 * @param string $image
	 * @param array  $processingInstructions
	 *
	 * @return string
	 */
	private function generateFilename( $image, $processingInstructions ) {
		/** @var array $filename */
		$filename = array();
		$filename[] = $image;
		$filename[] = $processingInstructions[ "command" ];
		if( isset( $processingInstructions[ "options" ] ) ) {
			/**
			 * @var string $key
			 * @var string $value
			 */
			foreach( $processingInstructions[ "options" ] as $key => $value ) {
				$filename[] = $key . " " . $value;
			}
		}
		$filename[] = $processingInstructions[ "width" ];
		$filename[] = $processingInstructions[ "height" ];
		$filename[] = self::SECURITY_KEY;

		return sha1( implode( "_", $filename ) ) . ".png";
	}

	private function exec( $source, $target, $processingInstructions ) {
		/** @var array $allowedCommands */
		$allowedCommands = array(
				"convert"
		);
		/** @var array $allowedOptions */
		$allowedOptions = array(
				"-gravity",
				"-extent"
		);

		if( !in_array( $processingInstructions[ "command" ], $allowedCommands ) ) {
			throw new InvalidArgumentException( "Invalid image magick command." );
		}

		/** @var array $command */
		$command = array();
		$command[] = $this->binDir . "/" . $processingInstructions[ "command" ];

		if( $processingInstructions[ "command" ] === "convert" ) {
			$command[] = escapeshellarg( $source );
			if( $processingInstructions[ "width" ] && $processingInstructions[ "height" ] ) {
				$command[] = "-resize";
				$command[] = $processingInstructions[ "width" ] . "x" . $processingInstructions[ "height" ];
			}
			if( isset( $processingInstructions[ "options" ] ) ) {
				/**
				 * @var string $key
				 * @var string $value
				 */
				foreach( $processingInstructions[ "options" ] as $key => $value ) {
					if( !in_array( $key, $allowedOptions ) ) {
						throw new InvalidArgumentException( "Invalid image magick option." );
					}
					$command[] = $key . " " . $value;
				}
			}

			$command[] = $target;
		}

		/** @var $process Process */
		$process = new Process( implode( " ", $command ) );
		// $process->setTimeout( $this->timeout );
		$process->run();

		if( !$process->isSuccessful() ) {
			throw new RuntimeException( $process->getErrorOutput() );
		}
	}
}