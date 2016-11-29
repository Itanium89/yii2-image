<?php

namespace yii\itanium\imageManager\models;

use Yii;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\ImageInterface;
use yii\helpers\ArrayHelper;


/**
 * This is the model class for table "image".
 *
 * @property integer $id
 * @property integer $modelRegistrationId
 * @property integer $modelId
 * @property string $attribute
 * @property string $name
 * @property integer $isMain
 * @property integer $sort
 */
class Image extends \yii\db\ActiveRecord
{
    /**
     *
     */
    const MODE_THUMBNAIL_INSET = 'inset';

    /**
     *
     */
    const MODE_THUMBNAIL_OUTBOUND = 'outbound';

    /**
     * @var string
     */
    public $mode = self::MODE_THUMBNAIL_OUTBOUND;

    /**
     * @var array
     */
    public $modes = [
        self::MODE_THUMBNAIL_OUTBOUND => ImageInterface::THUMBNAIL_OUTBOUND,
        self::MODE_THUMBNAIL_INSET => ImageInterface::THUMBNAIL_INSET
    ];

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'image';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['modelRegistrationId', 'attribute', 'name'], 'required'],
            [['modelRegistrationId', 'modelId', 'isMain', 'sort'], 'integer'],
            [['attribute', 'name'], 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'modelRegistrationId' => 'Model Registration ID',
            'modelId' => 'Model ID',
            'attribute' => 'Attribute',
            'isMain' => 'Is Main',
            'sort' => 'Sort',
            'name' => 'Name',
        ];
    }

    /**
     * @param $size
     * @return string
     */
    public function thumbnail($size)
    {
        $imagine = new Imagine();
        $size = new Box($size[0], $size[1]);
        $mode = $this->getMode(self::MODE_THUMBNAIL_OUTBOUND);
        $originalPath = $this->getOriginalPath();
        $cachePath = $this->getCachePath();
        if (file_exists($originalPath)) {
            $imageCache = $this->getCacheImageSizeName($size, $mode);
            $cachePath .= '/' . $imageCache;
            if (!file_exists($cachePath)) {
                $imagine->open($originalPath)
                    ->thumbnail($size, $mode)
                    ->save($cachePath);
            }
            return '/' . $cachePath;
        }
        return (new PlaceHolder())->draw($size, 'PLACEHOLDER');
    }

    //    public function getImage($size)
//    {
//        $imagine = new Imagine();
//        $size = new Box($size[0], $size[1]);
//
//        $mode = $this->getMode(self::MODE_THUMBNAIL_OUTBOUND);
//
//        $originalPath = $this->getOriginalPath($attribute);
//        $cachePath = $this->getCachePath($attribute);
//        if (file_exists($originalPath)) {
//            $imageCache = $this->getCacheImageSizeName($attribute, $size, $mode);
//            $cachePath .= '/' . $imageCache;
//            if (!file_exists($cachePath)) {
//                $imagine->open($originalPath)
//                    ->thumbnail($size, $mode)
//                    ->save($cachePath);
//            }
//        }
//        return '/' . $cachePath;
//    }
//

    /**
     * @param Box $size
     * @param $mode
     * @return string
     */
    public function getCacheImageSizeName(Box $size, $mode)
    {
        $imageName = explode('.', $this->name);
        return $imageName[0] . "_{$size->getWidth()}_{$size->getHeight()}_{$mode}." . $imageName[1];
    }

    /**
     * @return mixed
     */
    public function getCachePath()
    {
        return \Yii::$app->imageComponent->getCachePathById($this->modelRegistrationId, $this->modelId);
    }

    /**
     * @param $mode
     * @return mixed
     */
    public function getMode($mode)
    {
        return ArrayHelper::getValue($this->modes, $mode);
    }

    /**
     * @return string
     */
    public function getOriginalPath()
    {
        $path = \Yii::$app->imageComponent->getPathById($this->modelRegistrationId, $this->modelId);
        return $path . '/' . $this->name;
    }
}
