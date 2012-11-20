<?php

/*
 * This file is part of the Jirafe.
 * (c) Jirafe <http://www.jirafe.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Jirafe API revenues report.
 *
 * @author knplabs.com
 */
class Jirafe_Api_Report_Revenues extends Jirafe_Api_Report
{
    /**
     * Fetches revenues report.
     *
     * @param   string|array    $date       single date string or two dates array
     * @param   array           $breakdown  optional breakdown list
     *
     * @return  array
     */
    public function fetch($dates, array $breakdown = array())
    {
        $query = array('date' => implode(',', (array) $dates));

        if (count($breakdown)) {
            $query += array('breakdown' => implode(',', $breakdown));
        }

        $response = $this->doReportGet(null, $query);

        if ($response->hasError()) {
            throw new Jirafe_Exception(sprintf(
                '%d: %s', $response->getErrorCode(), $response->getErrorMessage()
            ));
        }

        return $response->getJson();
    }

    /**
     * Fetches average revenues ordervalue report.
     *
     * @param   string|array    $date       single date string or two dates array
     * @param   array           $breakdown  optional breakdown list
     * @param   array           $vs         optional vs list
     *
     * @return  array
     */
    public function fetchAverage($dates, array $breakdown = array(), array $vs = array())
    {
        $query = array('date' => implode(',', (array) $dates));

        if (count($breakdown)) {
            $query += array('breakdown' => implode(',', $breakdown));
        }

        if (count($vs)) {
            $query += array('vs' => implode(',', $vs));
        }

        $response = $this->doReportGet('average', $query);

        if ($response->hasError()) {
            throw new Jirafe_Exception(sprintf(
                '%d: %s', $response->getErrorCode(), $response->getErrorMessage()
            ));
        }

        return $response->getJson();
    }
}
