<?php

/*
 * This file is part of the Jirafe.
 * (c) Jirafe <http://www.jirafe.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Jirafe API application object.
 *
 * @author knplabs.com
 */
class Jirafe_Api_Resource_Application extends Jirafe_Api_Object
{
    /**
     * Initializes application object.
     *
     * @param   integer                             $id             application ID
     * @param   Jirafe_Api_Collection_Applications  $collection     applications collection
     * @param   Jirafe_Client                       $client         API client
     */
    public function __construct($id, Jirafe_Api_Collection_Applications $collection, Jirafe_Client $client)
    {
        parent::__construct($id, $collection, $client);
    }

    /**
     * Returns application sites collection or single site object.
     *
     * @param   $id     site id. If specified - method will return object instance instead of
     *                  collection
     *
     * @return  Jirafe_Api_Collection_Sites|Jirafe_Api_Resource_Site
     */
    public function sites($id = null)
    {
        $sites = new Jirafe_Api_Collection_Sites($this, $this->getClient());

        if (null !== $id) {
            return $sites->get($id);
        }

        return $sites;
    }

    /**
     * Returns applications resources collection (sync API).
     *
     * @return  Jirafe_Api_Collection_Resources
     */
    public function resources()
    {
        return new Jirafe_Api_Collection_Resources($this, $this->getClient());
    }
}
