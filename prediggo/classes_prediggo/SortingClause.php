<?php

/**
 * An enum of possible sorting clauses offered by prediggo.
 *
 * @package prediggo4php
 * @subpackage constants
 *
 * @author Stef
 */
class SortingClause 
{

    /**
    * Based on relevance
    */
    const TYPED_QUERY = 1;

    /**
    * Based on past purchases
    */
    const PAST_PURCHASE = 2;

    /**
    * Based on the user's browsing profile
    */
    const BROWSING_PROFILE = 3;

    /**
    * Based on item popularity
    */
    const DECREASING_POPULARITY = 4;

    /**
    * Based on price, highest first
    */
    const DECREASING_PRICE = 5;

    /**
    * Based on price, lowest first
    */
    const INCREASING_PRICE = 6;

    /**
    * based on poupularity and relevance
    */
    const POPULARITY_AND_USER_SEARCH = 7;

    /**
    * Reverse alphabetical
    */
    const DECREASING_APHABETICAL = 8;


    /**
    * Alphabetical
    */
    const  INCREASING_APHABETICAL = 9;

    /**
    * Based on relevance and user context
    */
    const  TYPED_QUERY_AND_CONTEXT = 10;



    /**
    * Relevance in priority, then popularity if relevance is equal
    */
    const  TYPED_QUERY_THEN_POPULARITY =  11;

    /**
    * Relevance 75%, popularity 25%
    */
    const TYPED_QUERY_AND_POPULARITY_75_25 = 12;

    /**
    * Relevance 50%, custom score 25%
    */
    const TYPED_QUERY_AND_SCORE = 13;


    /**
    * Relevance 25%, custom score 75%
    */
    const TYPED_QUERY_AND_SCORE_25_75 = 14;

    /**
    * Sort by item date, most recent first
    */
    const DECREASING_ITEM_DATE = 15;

    /**
    * Sort by item date, oldest first
    */
    const INCREASING_ITEM_DATE = 16;
   
}
