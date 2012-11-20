<?php


/**
 * Constants representing recommendation dimensions.
 *
 * @package prediggo4php
 * @subpackage constants
 *
 * @author Stef
 */
class RecDimensionConstants
{
        /**
        * The recommendation is based on the item's name (case of serials / trilogies ...).
        */
        const NAME = "name";

        /**
        * The recommendation is based on items proximity .
        */
        const PROXIMITY = "proximity";


        /**
        * The recommendation is based on ontology filtering.
        */
        const ONTOLOGY_FILTERING = "ontologyFiltering";

        /**
        * The recommendation is personalized taking the user profile into account (previous purchases...).
        */
        const PERSONALIZED = "personalized";

        /**
        * The recommendation is based on item popularity.
        */
        const POPULARITY = "popularity";

        /**
        * The recommendation is based on similarity with an another item
        */
        const SIMILARITY = "similarity";

        /**
        * The recommendation is based on what the user has previously visited in the session.
        */
        const BROWSING = "browsing";

        /**
        * Based on top sales
        */
        const TOP_N_SALES = "topNSales";


        /**
        * Based on multi-attirubtes utility function
        */
        const MAUT = "maut";

        /**
        * Based on user's past purchases
        */
        const USER_PAST_PURCHASES = "userPastPurchase";

        /**
        * Based on query typed in a search engine
        */
        const SEARCH = "search";
}

