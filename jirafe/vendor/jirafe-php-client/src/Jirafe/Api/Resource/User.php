<?php

/*
 * This file is part of the Jirafe.
 * (c) Jirafe <http://www.jirafe.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Jirafe API user object.
 *
 * @author knplabs.com
 */
class Jirafe_Api_Resource_User extends Jirafe_Api_Object
{
    /**
     * Initializes user object.
     *
     * @param   string                      $username   username
     * @param   Jirafe_Api_Collection_Users $collection users collection
     * @param   Jirafe_Client               $client     API client
     */
    public function __construct($username, Jirafe_Api_Collection_Users $collection, Jirafe_Client $client)
    {
        parent::__construct($username, $collection, $client);
    }
}
