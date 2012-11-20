<?php

/*
 * This file is part of the Jirafe.
 * (c) Jirafe <http://www.jirafe.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Jirafe API users collection.
 *
 * @author knplabs.com
 */
class Jirafe_Api_Collection_Users extends Jirafe_Api_Collection
{
    /**
     * Initializes users collection.
     *
     * @param   Jirafe_Client   $client API client
     */
    public function __construct(Jirafe_Client $client)
    {
        parent::__construct(null, $client);
    }

    /**
     * Returns user object with specified username.
     *
     * @param   string  $username
     *
     * @return  Jirafe_Api_Resource_User
     */
    public function get($username)
    {
        return new Jirafe_Api_Resource_User($username, $this, $this->getClient());
    }

    /**
     * Fetches all users from collection.
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

    /**
     * Creates user in collection.
     *
     * @param string    $username
     * @param string    $email
     *
     * @return  Jirafe_HttpConnection_Response
     */
    public function create($username, $email)
    {
        $response = $this->doPost(array(), array('username' => $username, 'email' => $email), false);

        if ($response->hasError()) {
            throw new Jirafe_Exception(sprintf(
                '%d: %s', $response->getErrorCode(), $response->getErrorMessage()
            ));
        }

        return $response->getJson();
    }
}
