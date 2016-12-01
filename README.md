# Yii2-image components

Install
============================


1) Migration:
```
./yii migrate --migrationPath=@vendor/itanium89/yii2-image/migrations 
```
2) Configurable config, like example:
```
'components' => [
     ....
        'imageComponent' => [
            'class' => \itanium\image\imageManager\ImageComponent::className(),
            'path' => 'uploads/origin',
            'cachePath' => 'uploads/cache',
            'configPath' => 'config'
        ],
     ....
]
```
3) Add behavior to your model:
```
class Article extends \yii\db\ActiveRecord
{
    public $testImage;
    public $images;
    
    ....
    public function behaviors()
    {
        return [
            'imageManager' => [
                'class' => \itanium\image\imageManager\behaviors\ImageManager::className(),
                'attributes' => [
                    'testImage',
                    'images'
                ],
                'rules' => [
                    'testImage' => [
                        'file',
                        'skipOnEmpty' => false,
                        'extensions' => 'png, jpg'
                    ]
                ]
            ]
        ];
    }
    ....
```

Base usage
============================

```
Upload image :

<?= $form->field($model, 'testImage')->fileInput(['rows' => 6]) ?>
<?= $form->field($model, 'images[]')->fileInput(['multiple' => true]) ?>


Draw image :
<?php foreach ($model->imageManager->gets('images') as $image) : ?>
    <?= Html::img($image->thumbnail(['100', '100'])) ?>
<?php endforeach; ?>

<?= Html::img($model->imageManager->get('testImage')->thumbnail(['300', '300'])) ?>
```