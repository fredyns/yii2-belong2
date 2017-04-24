<?php

namespace fredyns\belong2;

use yii\db\ActiveRecord;

/**
 * Description of BelongToModelBehavior
 *
 * @author Fredy Nurman Saleh <email@fredyns.net>
 */
class BelongToModelBehavior
{

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
                'belongTo' => function($thisBehavior, $ownerModel) {
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
}