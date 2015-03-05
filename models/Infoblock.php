<?php
/**
 * Infoblock
 *
 * @author Semenov Alexander <semenov@skeeks.com>
 * @link http://skeeks.com/
 * @copyright 2010-2014 SkeekS (Sx)
 * @date 09.11.2014
 * @since 1.0.0
 */

namespace skeeks\cms\models;

use skeeks\cms\components\registeredWidgets\Model;
use skeeks\cms\helpers\UrlHelper;
use skeeks\cms\models\behaviors\HasMultiLangAndSiteFields;
use skeeks\cms\models\behaviors\HasRef;
use Yii;

/**
 * @property $config
 *
 * Class Publication
 * @package skeeks\cms\models
 */
class Infoblock extends Core
{
    use behaviors\traits\HasFiles;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%cms_infoblock}}';
    }
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return array_merge(parent::behaviors(), [

            HasMultiLangAndSiteFields::className() =>
            [
                'class' => HasMultiLangAndSiteFields::className(),
                'fields' => ['config']
            ],

            [
                "class"  => behaviors\Serialize::className(),
                'fields' => ['rules']
            ],

            behaviors\HasFiles::className() =>
            [
                "class"     => behaviors\HasFiles::className(),
                "groups"    => [],
            ],
        ]);
    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();

        $scenarios['create'] = ['code', 'name', 'description', 'widget'];
        $scenarios['update'] = ['code', 'name', 'description', 'widget'];

        return $scenarios;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(parent::rules(), [
            [['name', 'widget'], 'required'],
            [['description', 'widget', 'rules', 'template'], 'string'],
            [['code'], 'unique'],
            [['code'], 'validateCode'],
            [["images", "files", "image_cover", "image", 'config', 'multiConfig'], 'safe'],
        ]);
    }

    public function validateCode($attribute)
    {
        if(!preg_match('/^[a-z]{1}[a-z0-1]{2,11}$/', $this->$attribute))
        {
            $this->addError($attribute, 'Используйте только буквы латинского алфавита и цифры. Начинаться должен с буквы. Пример block1.');
        }
    }


    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return  array_merge(parent::attributeLabels(), [
            'id' => Yii::t('app', 'ID'),
            'name' => Yii::t('app', 'Name'),
            'code' => Yii::t('app', 'Code'),
            'description' => Yii::t('app', 'Description'),
            'widget' => Yii::t('app', 'Widget'),
            'config' => Yii::t('app', 'Config'),
            'rules' => Yii::t('app', 'Rules'),
            'template' => Yii::t('app', 'Template'),
            'priority' => Yii::t('app', 'Priority'),
            'status' => Yii::t('app', 'Status'),
            'image' => Yii::t('app', 'Image'),
            'image_cover' => Yii::t('app', 'Image Cover'),
            'images' => Yii::t('app', 'Images'),
            'files' => Yii::t('app', 'Files'),
        ]);
    }


    /**
     * @param $id
     * @return static
     */
    static public function fetchById($id)
    {
        return static::find()->where(['id' => (int) $id])->one();
    }

    /**
     * @param $code
     * @return static
     */
    static public function fetchByCode($code)
    {
        return static::find()->where(['code' => (string) $code])->one();
    }

    /**
     * @return bool
     */
    public function isAllow()
    {
        return true;
    }

    /**
     * @return string
     */
    public function getWidgetClassName()
    {
        return (string) $this->widget;
    }

    /**
     * @return array
     */
    public function getWidgetConfig()
    {
        return (array) $this->multiConfig;
    }


    /**
     * @return array
     */
    public function getMultiConfig()
    {
        return (array) $this->getMultiFieldValue('config');
    }

    /**
     * @param $value
     * @return $this
     */
    public function setMultiConfig($value)
    {
        return $this->setMultiFieldValue('config', $value);
    }


    /**
     * @return array
     */
    public function getWidgetRules()
    {
        return (array) $this->rules;
    }

    /**
     * @return string
     */
    public function getWidgetTemplate()
    {
        return (string) $this->template;
    }


    /**
     * @param array $config
     * @return string
     */
    public function run($config = [])
    {
        $result = "";
        if (!$this->isAllow() || !$model = $this->getRegisterdWidgetModel())
        {
            return $result;
        }

        $config = array_merge($this->multiConfig, $config);
        $widget = $model->createWidget($config);

        return $widget->run();
    }

    /**
     * @return null|WidgetDescriptor
     */
    public function getRegisterdWidgetModel()
    {
        return \Yii::$app->registeredWidgets->getDescriptor($this->getWidgetClassName());
    }
}
