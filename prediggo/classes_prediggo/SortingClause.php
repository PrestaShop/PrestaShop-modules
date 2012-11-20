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
     const TYPED_QUERY = "SEARCH_SORTING_CODE_0";

     /**
      * Based on past purchases
      */
     const PAST_PURCHASE = "SEARCH_SORTING_CODE_1";

     /**
      * Based on the user's browsing profile
      */
     const BROWSING_PROFILE = "SEARCH_SORTING_CODE_2";

     /**
      * Based on item popularity
      */
     const DECREASING_POPULARITY = "SEARCH_SORTING_CODE_3";

     /**
      * Based on price, highest first
      */
     const DECREASING_PRICE = "SEARCH_SORTING_CODE_4";

     /**
      * Based on price, lowest first
      */
     const INCREASING_PRICE = "SEARCH_SORTING_CODE_5";

     /**
      * based on poupularity and relevance
      */
     const POPULARITY_AND_USER_SEARCH = "SEARCH_SORTING_CODE_6";

     /**
      * Reverse alphabetical
      */
     const DECREASING_APHABETICAL = "SEARCH_SORTING_CODE_7";

     /**
      * Alphabetical
      */
     const INCREASING_APHABETICAL = "SEARCH_SORTING_CODE_8";

      /**
      * Based on relevance and user context
      */
     const TYPED_QUERY_AND_CONTEXT = "SEARCH_SORTING_CODE_9";

   
}
