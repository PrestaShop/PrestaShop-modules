<?php

/*
 * This file is part of the Jirafe.
 * (c) Jirafe <http://www.jirafe.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Jirafe API site object.
 *
 * @author knplabs.com
 */
class Jirafe_Api_Resource_Site extends Jirafe_Api_Object
{
    /**
     * Initializes site object.
     *
     * @param   integer                     $id         site ID
     * @param   Jirafe_Api_Collection_Sites $collection sites collection
     * @param   Jirafe_Client               $client     API client
     */
    public function __construct($id, Jirafe_Api_Collection_Sites $collection, Jirafe_Client $client)
    {
        parent::__construct($id, $collection, $client);
    }

    /**
     * Returns site visits report instance.
     *
     * @return  Jirafe_Api_Report_Visits
     */
    public function visits()
    {
        return new Jirafe_Api_Report_Visits($this, $this->getClient());
    }

    /**
     * Returns site visitors report instance.
     *
     * @return  Jirafe_Api_Report_Visitors
     */
    public function visitors()
    {
        return new Jirafe_Api_Report_Visitors($this, $this->getClient());
    }

    /**
     * Returns site bounces report instance.
     *
     * @return  Jirafe_Api_Report_Bounces
     */
    public function bounces()
    {
        return new Jirafe_Api_Report_Bounces($this, $this->getClient());
    }

    /**
     * Returns site average report instance.
     *
     * @return  Jirafe_Api_Report_Average
     */
    public function average()
    {
        return new Jirafe_Api_Report_Average($this, $this->getClient());
    }

    /**
     * Returns site revenues report instance.
     *
     * @return  Jirafe_Api_Report_Revenues
     */
    public function revenues()
    {
        return new Jirafe_Api_Report_Revenues($this, $this->getClient());
    }

    /**
     * Returns site keywords report instance.
     *
     * @return  Jirafe_Api_Report_Keywords
     */
    public function keywords()
    {
        return new Jirafe_Api_Report_Keywords($this, $this->getClient());
    }

    /**
     * Returns site referers report instance.
     *
     * @return  Jirafe_Api_Report_Referers
     */
    public function referers()
    {
        return new Jirafe_Api_Report_Referers($this, $this->getClient());
    }

    /**
     * Returns site exits report instance.
     *
     * @return  Jirafe_Api_Report_Exits
     */
    public function exits()
    {
        return new Jirafe_Api_Report_Exits($this, $this->getClient());
    }
}
