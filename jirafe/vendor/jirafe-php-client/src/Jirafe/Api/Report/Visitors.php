<?php

/*
 * This file is part of the Jirafe.
 * (c) Jirafe <http://www.jirafe.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Jirafe API visitors report.
 *
 * @author knplabs.com
 */
class Jirafe_Api_Report_Visitors extends Jirafe_Api_Report
{
    /**
     * Fetches visitors interest report.
     *
     * @param   string|array    $date       single date string or two dates array
     * @param   array           $breakdown  optional breakdown list
     *
     * @return  array
     */
    public function fetchInterest($dates, array $breakdown = array())
    {
        $query = array('date' => implode(',', (array) $dates));

        if (count($breakdown)) {
            $query += array('breakdown' => implode(',', $breakdown));
        }

        $response = $this->doReportGet('interest', $query);

        if ($response->hasError()) {
            throw new Jirafe_Exception(sprintf(
                '%d: %s', $response->getErrorCode(), $response->getErrorMessage()
            ));
        }

        return $response->getJson();
    }
}
