<?php

namespace fredyns\belong2;

use Yii;
use yii\base\InvalidConfigException;
use yii\behaviors\AttributeBehavior;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * Description of BelongToModelBehavior
 *
 * @author Fredy Nurman Saleh <email@fredyns.net>
 */
class BelongToModelBehavior extends AttributeBehavior
{
    public $attribute;
    public $belongTo;
    public $mirrorAttributes = [];

    /**
     * sample use of this class in Model::behaviors()
     */
    public function sampleBehaviors()
    {
        return [
            [
                // behavior definition
                'class' => BelongToModelBehavior::className(),
                // attribute of this behavior-owner which belong to another model
                'attribute' => 'attr-name',
                // define belong to model class only
                'belongTo' => ActiveRecord::className(),
                // define belong to model class with options/attributes
                'belongTo' => [
                    'class' => ActiveRecord::className(),
                    'attribute-1' => 'value-1',
                    'attribute-2' => 'value-2',
                ],
                // use closure to define belonging model. useful when need to update current belonging model.
                'belongTo' => function($ownerModel, $thisBehavior) {
                    //  // when updating owner model
                    //  if ($ownerModel->isNewRecord == false) {
                    //      // if there is previous belonging key
                    //      if ($oldKey = $ownerModel->getOldAttribute('key_id') > 0) {
                    //          // when belonging model exist
                    //          if ($oldBelonging = ActiveRecord::findOne($oldKey) !== null) {
                    //              // when belonging model only referenced once (current owner model)
                    //              if ($oldBelonging->getHasMany()->count() == 1) {
                    //                  // return those model so it will be updated too
                    //                  return $oldBelonging;
                    //              }
                    //          }
                    //      }
                    //  }
                    //
                    // then create new belonging model
                    return new ActiveRecord([
                        'attribute' => 'value',
                    ]);
                },
                // copy attribute value from behavior-owner to belonging-model's attributes
                'mirrorAttributes' => [
                    // same attribute name
                    'attributeName',
                    // different attribute name
                    'modelAttribute' => 'ownerAttribute',
                    // use closure to process value. ex: filter, translate etc.
                    'modelAttribute' => function($thisBehavior, $ownerModel) {
                        // script
                        return $value;
                    },
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if (empty($this->attribute)) {
            throw new InvalidConfigException("Related attribute must be set.");
        }

        if (empty($this->belongTo)) {
            throw new InvalidConfigException("Belonging model class must be set.");
        } elseif (!is_string($this->belongTo) && !is_array($this->belongTo) && !($this->belongTo instanceof \Closure)) {
            throw new InvalidConfigException("Belonging model setup invalid.");
        }

        if ($this->mirrorAttributes && !is_array($this->mirrorAttributes)) {
            throw new InvalidConfigException("Mirror attribute must be an array.");
        }

        if (empty($this->attributes)) {
            $this->attributes = [
                ActiveRecord::EVENT_BEFORE_INSERT => $this->attribute,
                ActiveRecord::EVENT_BEFORE_UPDATE => $this->attribute,
            ];
        }
    }

    /**
     * Evaluates the value of the user.
     * The return result of this method will be assigned to the current attribute(s).
     * @param Event $event
     * @return mixed the value of the user.
     */
    protected function getValue($event)
    {
        $value = ArrayHelper::getValue($this->owner, $this->attribute);

        if (empty($value)) {
            return NULL;
        } elseif (is_numeric($value)) {
            return $value;
        } else {
            $belongingModel = $this->getBelongingModel();
            $belongingModel = $this->copyAttributes($belongingModel);

            return $belongingModel->save(FALSE) ? $belongingModel->id : null;
        }
    }

    /**
     * get belonging model instance
     *
     * @return ActiveRecord
     */
    public function getBelongingModel()
    {
        $belongTo = $this->belongTo;

        if ($belongTo instanceof \Closure) {
            return $belongTo($this->owner, $this);
        }

        return Yii::createObject($belongTo);
    }

    /**
     * copy this owner attributes to model
     *
     * @param ActiveRecord $model
     * @return ActiveRecord
     */
    public function copyAttributes($model)
    {
        foreach ($this->mirrorAttributes as $targetAttribute => $sourceAttribute) {
            if (is_integer($targetAttribute)) {
                $targetAttribute = $sourceAttribute;
            }

            $model->{$targetAttribute} = $this->owner->{$sourceAttribute};
        }

        return $model;
    }
}