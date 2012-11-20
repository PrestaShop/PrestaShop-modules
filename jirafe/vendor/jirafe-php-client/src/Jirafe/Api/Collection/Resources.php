<?php

/*
 * This file is part of the Jirafe.
 * (c) Jirafe <http://www.jirafe.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Jirafe API application resources collection.
 * Also known as Sync resource.
 *
 * @author knplabs.com
 */
class Jirafe_Api_Collection_Resources extends Jirafe_Api_Collection
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
     * Synchronize application resources and application itself.
     *
     * @param   array   $sites  sites to sync
     * @param   array   $users  users to sync
     * @param   array   $params other params including application fields and onboarding
     *  for example:
     *    array(
     *      'opt_in' => 1, // allow onboarding, sends confirmation to join the cloud for new users
     *      'platform_type' => 'magento',
     *      'platform_version' => '1.0.0',
     *      'plugin_version' => '0.1.0',
     *    )
     *
     * @return  array           syncronized array('users' => array(), 'sites' => array())
     */
    public function sync(array $sites = array(), array $users = array(), array $params = array())
    {
        $response = $this->doPost(array(), array_merge($params, compact('sites', 'users')));

        if ($response->hasError()) {
            throw new Jirafe_Exception(sprintf(
                '%d: %s', $response->getErrorCode(), $response->getErrorMessage()
            ));
        }

        return $response->getJson();
    }
}
