<?php
namespace yii\itanium\imageManager\models;

use Imagine\Gd\Font;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;


/**
 * short description here
 *
 * @author Semeikin Anton semanton89@gmail.com
 * @version ${ID}
 * @copyright Blue Fountain Media
 */
class PlaceHolder
{
    public $fileName = 'placeholder';

    public function draw($size, $title = 'PLACE HOLDER')
    {
        $path = \Yii::$app->imageComponent->getPlaceHolderPath();
        $filename = $path . $this->getCacheImageSizeName($size);
        if (!file_exists($filename)) {
            $imagine = new Imagine();
            $palette = new \Imagine\Image\Palette\RGB();
            $palette->color('#000');
            $image = $imagine->create($size, $palette->color('#d9d9d9'));
            $image = $this->getText($size, $palette, $image, $title);
            $image->save($filename);
        }
        return '/' . $filename;
    }

    public function getText($size, $palette, $image, $title)
    {
        $countWord = iconv_strlen($title);
        $fontWeight = intval($size->getWidth() / $countWord);
        $textFont = new Font(__DIR__ . '/../fonts/font.ttf', $fontWeight, $palette->color('#000'));
        $width = intval(($size->getWidth() / 6) - ($fontWeight / 2));
        $height = intval(($size->getHeight() / 2) - ($fontWeight / 2));
        $centeredTextPosition = new \Imagine\Image\Point($width, $height);
        $image->draw()->text($title, $textFont, $centeredTextPosition);
        return $image;
    }

    public function getCacheImageSizeName(Box $size)
    {
        return $this->fileName . "_{$size->getWidth()}_{$size->getHeight()}_.jpg";
    }
}