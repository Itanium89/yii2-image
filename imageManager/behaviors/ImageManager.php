<?php
/**
 * Created by PhpStorm.
 * User: Itanium
 * Date: 31.10.2016
 * Time: 22:05
 */

namespace itanium\image\imageManager\behaviors;


use itanium\image\imageManager\ImageComponent;
use itanium\image\imageManager\ImageMagic;
use yii\base\Behavior;
use yii\base\InvalidConfigException;
use yii\db\ActiveRecord;
use yii\helpers\BaseFileHelper;
use yii\validators\Validator;
use yii\web\UploadedFile;

class ImageManager extends Behavior
{
    /**
     * @var array
     */
    public $attributes = [];
    /**
     * @var array
     */
    public $rules = [];
    /**
     * @var array
     */
    protected $_rules = [];

    public function init()
    {
        if (!empty($this->attributes)) {
            foreach ($this->attributes as $attribute) {
                $this->_rules[] = $this->addRule($attribute);
            }
        }
        parent::init();
    }

    public function addRule($attribute)
    {
        if (array_key_exists($attribute, $this->rules)) {
            array_unshift($this->rules[$attribute], [$attribute]);
            return $this->rules[$attribute];
        }
        return [[$attribute], 'file', 'skipOnEmpty' => true, 'maxFiles' => 4, 'extensions' => 'png, jpg'];
    }

    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_VALIDATE => 'beforeSave',
            ActiveRecord::EVENT_AFTER_INSERT => 'afterInsert',
            ActiveRecord::EVENT_AFTER_UPDATE => 'afterUpdate',
            ActiveRecord::EVENT_BEFORE_VALIDATE => 'beforeValidate',
        ];
    }

    public function afterInsert()
    {
        $this->saveImages();
    }

    public function afterUpdate()
    {
        $this->saveImages(true);
    }

    public function beforeSave()
    {
        return;
    }

    public function beforeValidate()
    {
        foreach ($this->attributes as $attribute) {
            if (!is_null($this->getImage($attribute))) {
                $this->owner->$attribute = $this->getImage($attribute);
            }
        }
    }

    public function saveImages($isUpdate = false)
    {
        foreach ($this->attributes as $attribute) {
            $image = $this->getImage($attribute);
            if (!is_null($image) && $image instanceof UploadedFile) {
                $this->saveImage($image, $attribute);
            } elseif (is_array($image)) {
                foreach ($image as $img) {
                    $this->saveImage($img, $attribute);
                }
            }
        }
    }

    public function saveImage(UploadedFile $image, $attribute)
    {
        $name = sha1($image->baseName . time()) . '.' . $image->extension;
        $path = \Yii::$app->imageComponent->getPath($this->owner);
        if ($image->saveAs($path . '/' . $name)) {
            \Yii::$app->imageComponent->saveImage($this->owner, $attribute, $name);
        }
    }

    public function getImage($attribute)
    {
        $className = get_class($this->owner);
        $className = explode('\\', $className);
        $className = array_pop($className);
        if (isset($_FILES[$className]['name'][$attribute]) && !is_array($_FILES[$className]['name'][$attribute])) {
            return UploadedFile::getInstance($this->owner, $attribute);
        }
        return UploadedFile::getInstances($this->owner, $attribute);
    }

    public function getImageManager()
    {
        return new ImageMagic([
            'model' => $this->owner
        ]);
    }

    /**
     * @inheritdoc
     */
    public function attach($owner)
    {
        parent::attach($owner);
        $validators = $owner->validators;

        foreach ($this->_rules as $rule) {
            if ($rule instanceof Validator) {
                $validators->append($rule);
                $this->validators[] = $rule; // keep a reference in behavior
            } elseif (is_array($rule) && isset($rule[0], $rule[1])) { // attributes, validator type
                $validator = Validator::createValidator($rule[1], $owner, (array)$rule[0], array_slice($rule, 2));
                $validators->append($validator);
                $owner->validators[] = $validator; // keep a reference in behavior
            } else {
                throw new InvalidConfigException('Invalid validation rule: a rule must specify both attribute names and validator type.');
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function detach()
    {
        $ownerValidators = $this->owner->validators;
        $cleanValidators = [];
        foreach ($ownerValidators as $validator) {
            if (!in_array($validator, $this->validators)) {
                $cleanValidators[] = $validator;
            }
        }
        $ownerValidators->exchangeArray($cleanValidators);
        parent::detach();
    }
}