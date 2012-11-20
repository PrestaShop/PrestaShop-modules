<?php

/*
 * This file is part of the Jirafe.
 * (c) Jirafe <http://www.jirafe.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Jirafe API applications collection.
 *
 * @author knplabs.com
 */
class Jirafe_Api_Collection_Applications extends Jirafe_Api_Collection
{
    /**
     * Initializes applications collection.
     *
     * @param   Jirafe_Client   $client API client
     */
    public function __construct(Jirafe_Client $client)
    {
        parent::__construct(null, $client);
    }

    /**
     * Returns application object instance with specified id.
     *
     * @param   integer $id
     *
     * @return  Jirafe_Api_Resource_Application
     */
    public function get($id)
    {
        return new Jirafe_Api_Resource_Application($id, $this, $this->getClient());
    }

    /**
     * Creates application on collection.
     *
     * @param   string  $name   application name
     * @param   string  $url    application base url
     *
     * @return  array
     */
    public function create($name, $url, $platformType = 'generic', $platformVersion = '1.0.0', $pluginVersion = '0.1.0')
    {
        $response = $this->doPost(array(), array(
            'name'             => $name,
            'url'              => $url,
            'platform_type'    => $platformType,
            'platform_version' => $platformVersion,
            'plugin_version'   => $pluginVersion,
        ), false);

        if ($response->hasError()) {
            throw new Jirafe_Exception(sprintf(
                '%d: %s', $response->getErrorCode(), $response->getErrorMessage()
            ));
        }

        return $response->getJson();
    }
}
