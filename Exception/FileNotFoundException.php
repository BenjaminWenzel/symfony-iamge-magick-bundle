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

namespace Hartwig\Bundle\ImageMagickBundle\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class FileNotFoundException
 * @package Hartwig\Bundle\ImageMagickBundle\Exception
 */
class FileNotFoundException extends NotFoundHttpException implements ExceptionInterface {
}