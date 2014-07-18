<?php
/**
 * @author    SysPay Ltd.
 * @copyright 2012-2014 SysPay Ltd.
 * @license   http://opensource.org/licenses/MIT MIT License
 */

/**
 * Base class for entities
 */
abstract class Syspay_Merchant_Entity
{
    /**
     * Get an array representation of the object to build the request.
     * It will collect all protected properties.
     *
     * @return array An array to be used in the request
     */
    public function toArray()
    {
        $data = array();

        $r = new ReflectionClass($this);
        $properties = $r->getProperties(ReflectionProperty::IS_PROTECTED);
        foreach ($properties as $property) {
            $name = $property->getName();

            if (true === isset($this->$name)) {
                $data[$name] = $this->$name;
            }
        }
        return $data;
    }

    /**
     * Get the entity type
     * @return string Entity type (as seen in the API)
     */
    public function getType()
    {
        // static:: has only been introduced as of PHP 5.3.
        return constant(get_class($this) . '::TYPE');
    }
}
