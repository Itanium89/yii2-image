<?php
/**
 * Created by PhpStorm.
 * User: Itanium
 * Date: 31.10.2016
 * Time: 22:42
 */

namespace itanium\image\imageManager;


use itanium\image\imageManager\models\Image;
use yii\base\Component;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\BaseFileHelper;

class ImageComponent extends Component
{
    public $path;
    public $cachePath;
    public $configPath;
    public $placeholderPath = 'uploads/placeholder';

    public $modelRegistrationArray = [];


    public function init()
    {
        $basePath = Yii::$app->getBasePath() . DIRECTORY_SEPARATOR . $this->configPath;
        $path = $basePath . DIRECTORY_SEPARATOR . 'imageComponent' . DIRECTORY_SEPARATOR . 'modelRegistration.php';
        $this->modelRegistrationArray = require($path);

        parent::init();
    }

    public function getPath($owner)
    {
        $class = get_class($owner);
        $model = $this->getModelRegistrationKey($class);
        $model = str_replace("\\", '.', $model);
        $path = $this->path . '/' . $model;
        if (!file_exists($path)) {
            BaseFileHelper::createDirectory($path, 0777);
        }
        if (isset($owner->id)) {
            $path .= '/' . $owner->id;
            if (!file_exists($path)) {
                BaseFileHelper::createDirectory($path, 0777);
            }
        }
        return $path;
    }

    public function getPathById($id, $modelId)
    {
        $model = $this->getModelRegistrationKeyById($id);
        $model = str_replace("\\", '.', $model);
        $path = $this->path . '/' . $model;
        if (!file_exists($path)) {
            BaseFileHelper::createDirectory($path, 0777);
        }
        if (isset($modelId)) {
            $path .= '/' . $modelId;
            if (!file_exists($path)) {
                BaseFileHelper::createDirectory($path, 0777);
            }
        }
        return $path;
    }

    public function getCachePathById($id, $modelId)
    {
        $model = $this->getModelRegistrationKeyById($id);
        $model = str_replace("\\", '.', $model);
        $path = $this->cachePath . '/' . $model;
        if (!file_exists($path)) {
            BaseFileHelper::createDirectory($path, 0777);
        }
        if (isset($modelId)) {
            $path .= '/' . $modelId;
            if (!file_exists($path)) {
                BaseFileHelper::createDirectory($path, 0777);
            }
        }
        return $path;
    }


    public function getCachePath($owner)
    {
        $class = get_class($owner);
        $model = $this->getModelRegistrationKey($class);
        $model = str_replace("\\", '.', $model);
        $path = $this->cachePath . '/' . $model;
        if (!file_exists($path)) {
            BaseFileHelper::createDirectory($path, 0777);
        }
        if (isset($owner->id)) {
            $path .= '/' . $owner->id;
            if (!file_exists($path)) {
                BaseFileHelper::createDirectory($path, 0777);
            }
        }
        return $path;
    }

    public function saveImage($owner, $attribute, $name)
    {
        $class = get_class($owner);
        if (array_key_exists($class, $this->modelRegistrationArray)) {
            $id = $this->getModelRegistrationId($class);
            $image = new Image();
            $image->modelRegistrationId = $id;
            $image->attribute = $attribute;
            $image->name = $name;
            $image->modelId = isset($owner->id) ? $owner->id : null;
            $image->save();
        }
    }

    public function getModelRegistrationId($class)
    {
        return ArrayHelper::getValue($this->modelRegistrationArray, $class);
    }

    public function getModelRegistrationKeyById($id)
    {
        return array_search($id, $this->modelRegistrationArray);
    }

    public function getModelRegistrationKey($class)
    {
        if (!array_key_exists($class, $this->modelRegistrationArray)) {
            if (empty($this->modelRegistrationArray)) {
                $id = 1;
            } else {
                $id = max($this->modelRegistrationArray);
            }
            $this->modelRegistrationArray[$class] = $id;
        }
        return key(ArrayHelper::getColumn($this->modelRegistrationArray, $class));
    }

    public function setModelRegistration()
    {
        $basePath = Yii::$app->getBasePath() . DIRECTORY_SEPARATOR . $this->configPath;
        $path = $basePath . DIRECTORY_SEPARATOR . 'imageComponent' . DIRECTORY_SEPARATOR . 'modelRegistration.php';
        file_put_contents($path, "<?php\n return " . var_export($this->modelRegistrationArray, true) . ';');
    }

    public function getPlaceHolderPath()
    {
        $path = $this->placeholderPath . DIRECTORY_SEPARATOR;
        if (!file_exists($path)) {
            BaseFileHelper::createDirectory($path, 0777);
        }
        return $path;
    }
}