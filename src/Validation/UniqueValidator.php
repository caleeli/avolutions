<?php
/**
 * AVOLUTIONS
 *
 * Just another open source PHP framework.
 *
 * @copyright	Copyright (c) 2019 - 2021 AVOLUTIONS
 * @license		MIT License (http://avolutions.org/license)
 * @link		http://avolutions.org
 */

namespace Avolutions\Validation;

use Avolutions\Orm\EntityCollection;

/**
 * UniqueValidator
 *
 * The UniqueValidator validates if the value of an Entity attribute is unique in database.
 *
 * @author	Alexander Vogt <alexander.vogt@avolutions.org>
 * @since	0.6.0
 */
class UniqueValidator extends AbstractValidator
{
    /**
     * isValid
     *
     * Checks if the passed value is valid considering the validator type and passed options.
     *
     * @param $value The value to validate.
     *
     * @return bool Data is valid (true) or not (false).
     */
    public function isValid($value) {
        $EntityCollection = new EntityCollection($this->Entity->getEntityName());
        $exists = $EntityCollection->where($this->property.' = \''.$value.'\'')->getFirst();

        return ($exists == null);
    }
}