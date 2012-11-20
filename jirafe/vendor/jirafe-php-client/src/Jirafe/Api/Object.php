<?php

/*
 * This file is part of the Jirafe.
 * (c) Jirafe <http://www.jirafe.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Jirafe API object abstraction.
 *
 * @author knplabs.com
 */
abstract class Jirafe_Api_Object extends Jirafe_Api_Resource
{
    /**
     * Updates API object with provided values.
     *
     * @param   array   $values
     */
    public function update(array $values)
    {
        $response = $this->doPut(array(), $values);

        if ($response->hasError()) {
            throw new Jirafe_Exception(sprintf(
                '%d: %s', $response->getErrorCode(), $response->getErrorMessage()
            ));
        }
    }

    /**
     * Fetches API object values.
     *
     * @return  array
     */
    public function fetch()
    {
        $response = $this->doGet();

        if ($response->hasError()) {
            throw new Jirafe_Exception(sprintf(
                '%d: %s', $response->getErrorCode(), $response->getErrorMessage()
            ));
        }

        return $response->getJson();
    }

    /**
     * Deletes API object from collection.
     */
    public function delete()
    {
        $response = $this->doDelete();

        if ($response->hasError()) {
            throw new Jirafe_Exception(sprintf(
                '%d: %s', $response->getErrorCode(), $response->getErrorMessage()
            ));
        }
    }
}
