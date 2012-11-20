<?php

/*
 * This file is part of the Jirafe.
 * (c) Jirafe <http://www.jirafe.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Jirafe API sites collection.
 *
 * @author knplabs.com
 */
class Jirafe_Api_Collection_Sites extends Jirafe_Api_Collection
{
    /**
     * Initializes sites collection.
     *
     * @param   Jirafe_Api_Resource_Application $parent application resource
     * @param   Jirafe_Client                   $client API client
     */
    public function __construct(Jirafe_Api_Resource_Application $parent, Jirafe_Client $client)
    {
        parent::__construct($parent, $client);
    }

    /**
     * Returns site object with specified id.
     *
     * @param   integer $id
     *
     * @return  Jirafe_Api_Resource_Site
     */
    public function get($id)
    {
        return new Jirafe_Api_Resource_Site($id, $this, $this->getClient());
    }

    /**
     * Fetches all sites from collection.
     *
     * @return  array
     */
    public function fetchAll()
    {
        $response = $this->doGet();

        if ($response->hasError()) {
            throw new Jirafe_Exception(sprintf(
                '%d: %s', $response->getErrorCode(), $response->getErrorMessage()
            ));
        }

        return $response->getJson();
    }
}
