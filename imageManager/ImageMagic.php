<?php
/**
 * Created by PhpStorm.
 * User: Itanium
 * Date: 01.11.2016
 * Time: 20:54
 */

namespace app\components\imageManager;


use app\components\imageManager\models\Image;
use yii\base\Object;
use yii\caching\Dependency;

class ImageMagic extends Object
{
    public $model;
    public $images;

    /**
     * ImageMagic constructor.
     * @param array $config
     */
    public function __construct($config = [])
    {
        $this->model = $config['model'];
        parent::__construct($config);
    }

    /**
     *
     */
    public function init()
    {
        $this->images = $this->getImages();
        parent::init();
    }


    /**
     * @return array|\yii\db\ActiveRecord[]
     */
    public function getImages()
    {
        $modelRegister = \Yii::$app->imageComponent->getModelRegistrationId(get_class($this->model));
        $result = Image::getDb()->cache(function ($db) use ($modelRegister) {
            $model = Image::find()
                ->where([
                    'modelId' => $this->model->id
                ]);
            if (isset($this->model->id)) {
                $model->andWhere(['modelRegistrationId' => $modelRegister]);
            }
            return $model->all();
        },6000);
        return $result;
    }

    /**
     * @param $attribute
     * @return mixed
     */
    public function get($attribute)
    {
        $images = $this->images;
        $images = array_filter($images, function ($item) use ($attribute) {
            return $item->attribute == $attribute;
        });
        return array_shift($images);
    }

    /**
     * @param $attribute
     * @return array
     */
    public function gets($attribute)
    {
        $images = $this->images;
        $images = array_filter($images, function ($item) use ($attribute) {
            return $item->attribute == $attribute;
        });
        return $images;
    }
}
