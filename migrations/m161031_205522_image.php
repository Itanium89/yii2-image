<?php

use yii\db\Migration;

class m161031_205522_image extends Migration
{
    public function up()
    {
        $this->createTable('{{image}}', [
            'id' => $this->primaryKey(),
            'modelRegistrationId' => $this->integer()->notNull(),
            'modelId' => $this->integer()->null(),
            'name' => $this->string()->notNull(),
            'attribute' => $this->string()->notNull(),
            'isMain' => $this->smallInteger(),
            'sort' => $this->integer(),
        ]);

    }

    public function down()
    {
        $this->dropTable('{{image}}');
    }
}
