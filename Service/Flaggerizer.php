<?php
/**
 * @author: Stefan Beier
 */

namespace HorribleSolutions\FlaggerizerBundle\Service;

use Imagick;

/**
 * Class Flaggerizer
 *
 * @package AppBundle\Service
 */
class Flaggerizer
{

    /**
     * @var string
     */
    protected $flagBaseUrl;

    /**
     * @var float
     */
    protected $gamma;

    /**
     * @var float
     */
    protected $opacity;

    /**
     * @param string $flagBaseUrl
     * @param float  $gamma
     * @param float  $opacity
     */
    public function __construct($flagBaseUrl, $gamma, $opacity)
    {
        $this->flagBaseUrl = $flagBaseUrl;
        $this->gamma       = $gamma;
        $this->opacity     = $opacity;
    }

    /**
     * @param string $imageUrl
     * @param string $flag
     *
     * @return Imagick
     */
    public function render($imageUrl, $flag)
    {
        $flagCodeUrl = sprintf($this->flagBaseUrl, $flag);

        try {
            $image = new Imagick($imageUrl);
        } catch (\Exception $e) {
            return null;
        }

        try {
            $flagImage = new Imagick($flagCodeUrl);
        } catch (\Exception $e) {
            return null;
        }

        $flagImage->scaleImage(
            $image->getImageWidth(),
            $image->getImageHeight(),
            false
        );

        $flagImage->gammaImage($this->gamma);
        $flagImage->setImageOpacity($this->opacity);

        $image->compositeImage($flagImage, Imagick::COMPOSITE_DEFAULT, 0, 0);

        return $image;
    }

    /**
     * @param string $flag
     *
     * @return bool
     */
    public function checkFlag($flag)
    {
        $flagCodeUrl = sprintf($this->flagBaseUrl, $flag);

        return file_get_contents($flagCodeUrl) == false ? false : true;
    }

}
