<?php


require_once "AutoCompleteRequest.php";
require_once "BeginProductImportRequest.php";
require_once "GetSearchPageRecommendationRequest.php";
require_once "GetLandingPageRecommendationRequest.php";
require_once "GetCategoryRecommendationRequest.php";
require_once "GetBasketRecommendationRequest.php";
require_once "RegisterUserRequest.php";
require_once "SetCategoryRequest.php";
require_once "GetItemRecommendationRequest.php";
require_once "GetUserRecommendationRequest.php";
require_once "GetTopNSalesRequest.php";
require_once "GetTopNViewedRequest.php";
require_once "NotifyPrediggoRequest.php";
require_once "GetAdvertisementRequest.php";
require_once "GetAdvertisementClickUrlRequest.php";

require_once "BeginProductImportRequest.php";
require_once "ProductImportRequest.php";
require_once "EndProductImportRequest.php";

require_once "BeginUserProfileImportRequest.php";
require_once "UserProfileImportRequest.php";
require_once "EndUserProfileImportRequest.php";


require_once "PrediggoException.php";




/**
 * Service interface for querying the prediggo server....
 *
 * @package prediggo4php
 *
 * @author Stef
 */
class PrediggoService
{

    /**
     * An errorCode => errorMessage associative array.
     */
    private static $returnCodesAndMessages = array(

        0 => 'Request OK but no results.',
        1 => 'Request OK.',
        2 => 'No results found',
        100 => 'Top n sales returned because some error occured.',
        -9 => 'Bad shopID in request.',
        -10 => 'Null parameter in request.',
        -11 => 'Shop identification failure.',
        -12 => 'User identification failure.',
        -13 => 'Call to unimplemented method.',
        -14 => 'No subplatform found.',
        -101 => 'Could not load user profile.',
        -102 => 'No ontologies found.',
        -103 => 'Error when loading the data in the ontology.',
        -200 => 'Unknown Error.'
    );


    /**
     * Returns the error message for a given status code.
     * @param integer $returnCode the status code
     * @return string the error message corresponding to the status code or 'No message found'.
     */
    public static function getStatusMessageForStatusCode($returnCode)
    {
        if( array_key_exists( $returnCode, self::$returnCodesAndMessages ) )
            return self::$returnCodesAndMessages[$returnCode];
        else
            return 'No message found';

    }


    /**
     * Executes a getItemRecommendation query.
     * @param GetItemRecommendationParam $param An object containing all the necessary parameters for this query
     * @return GetItemRecommendationResult An object containing the results.
     * @throws PrediggoException in case of errors
     */
    public static function  getItemRecommendation( GetItemRecommendationParam $param )
    {
        $request = new GetItemRecommendationRequest($param);

        self::executeCall($request);
        return $request->getResultObject();
    }



    /**
     * Executes a getTopNSales query.
     * @param GetTopNSalesParam $param An object containing all the necessary parameters for this query
     * @return GetTopNSalesResult An object containing the results.
     * @throws PrediggoException in case of errors
     */
    public static function getTopNSales( GetTopNSalesParam $param)
    {
        $request = new GetTopNSalesRequest($param);

        self::executeCall($request);
        return $request->getResultObject();
    }



    /**
     * Executes a getTopNViewed query.
     * @param GetTopNViewedParam $param An object containing all the necessary parameters for this query
     * @return GetTopNViewedResult An object containing the results.
     * @throws PrediggoException in case of errors
     */
    public static function getTopNViewed( GetTopNViewedParam $param)
    {
        $request = new GetTopNViewedRequest($param);

        self::executeCall($request);
        return $request->getResultObject();
    }



    /**
     * Executes a getBasketRecommendation query.
     * @param GetBasketRecommendationParam $param An object containing all the necessary parameters for this query
     * @return GetBasketRecommendationResult An object containing the results.
     * @throws PrediggoException in case of errors
     */
    public static function getBasketRecommendation( GetBasketRecommendationParam $param)
    {
        $request = new GetBasketRecommendationRequest($param);

        self::executeCall($request);
        return $request->getResultObject();
    }


    /**
     * Executes a getCategoryRecommendation query.
     * @param GetCategoryRecommendationParam $param An object containing all the necessary parameters for this query
     * @return GetCategoryRecommendationResult An object containing the results.
     * @throws PrediggoException in case of errors
     */
    public static function getCategoryRecommendation( GetCategoryRecommendationParam $param)
    {
        $request = new GetCategoryRecommendationRequest($param);

        self::executeCall($request);
        return $request->getResultObject();
    }


    /**
     * Executes a getLandingPageRecommendation query.
     * @param GetLandingPageRecommendationParam $param An object containing all the necessary parameters for this query
     * @return GetLandingPageRecommendationResult An object containing the results.
     * @throws PrediggoException in case of errors
     */
    public static function getLandingPageRecommendation( GetLandingPageRecommendationParam $param)
    {
        $request = new GetLandingPageRecommendationRequest($param);

        self::executeCall($request);
        return $request->getResultObject();
    }


    /**
     * Executes a getSearchPageRecommendation query.
     * @param GetSearchPageRecommendationParam $param An object containing all the necessary parameters for this query
     * @return GetSearchPageRecommendationResult An object containing the results.
     * @throws PrediggoException in case of errors
     */
    public static function getSearchPageRecommendation( GetSearchPageRecommendationParam $param)
    {
        $request = new GetSearchPageRecommendationRequest($param);

        self::executeCall($request);
        return $request->getResultObject();
    }


    /**
     * Executes a getUserRecommendation query.
     * @param GetUserRecommendationParam $param An object containing all the necessary parameters for this query
     * @return GetUserRecommendationResult An object containing the results.
     * @throws PrediggoException in case of errors
     */
    public static function getUserRecommendation(GetUserRecommendationParam $param)
    {
        $request = new GetUserRecommendationRequest($param);

        self::executeCall($request);
        return $request->getResultObject();
    }



    /**
     * Executes a registerUser query.
     * @param RegisterUserParam $param An object containing all the necessary parameters for this query
     * @return RegisterUserResult An object containing the results.
     * @throws PrediggoException in case of errors
     */
    public static function registerUser(RegisterUserParam $param)
    {
        $request = new RegisterUserRequest($param);

        self::executeCall($request);
        return $request->getResultObject();
    }


    /**
     * Executes a setCategory query.
     * @param SetCategoryParam $param An object containing all the necessary parameters for this query
     * @return SetCategoryResult An object containing the results.
     * @throws PrediggoException in case of errors
     */
    public static function setCategory(SetCategoryParam $param)
    {
        $request = new SetCategoryRequest($param);

        self::executeCall($request);
        return $request->getResultObject();
    }


    /**
     * Executes a notifyPrediggo query.
     * @param NotifyPrediggoParam $param An object containing all the necessary parameters for this query
     * @return NotifyPrediggoResult An object containing the results.
     * @throws PrediggoException in case of errors
     */
    public static function notifyPrediggo(NotifyPrediggoParam $param)
    {
        $request = new NotifyPrediggoRequest($param);

        self::executeCall($request);
        return $request->getResultObject();
    }


     /**
     * Execute an autoComplete query.
     * Please refer to the documentation for a complete description of use cases and parameters.
     * @param AutoCompleteParam $param An object containing all the necessary parameters for this query.
     * @return AutoCompleteResult An object containing the results.
     * @throws PrediggoException in case of errors
     */
    public static function autoComplete(AutoCompleteParam $param)
    {
        $request = new AutoCompleteRequest($param);

        self::executeCall($request);
        return $request->getResultObject();
    }



    /**
     * Executes a getAdvertisement query.
     * @param GetAdvertisementParam $param An object containing all the necessary parameters for this query
     * @return GetAdvertisementResult An object containing the results.
     * @throws PrediggoException in case of errors
     */
    public static function getAdvertisement( GetAdvertisementParam $param)
    {
        $request = new GetAdvertisementRequest($param);

        self::executeCall($request);
        return $request->getResultObject();
    }


    /**
     * Executes a getAdvertisementClickUrl query.
     * @param GetAdvertisementClickUrlParam $param An object containing all the necessary parameters for this query
     * @return GetAdvertisementClickUrlResult An object containing the results.
     * @throws PrediggoException in case of errors
     */
    public static function getAdvertisementClickUrl( GetAdvertisementClickUrlParam $param)
    {
        $request = new GetAdvertisementClickUrlRequest($param);

        self::executeCall($request);
        return $request->getResultObject();
    }
    
    /**
     * Execute an beginProductImport query.
     * Please refer to the documentation for a complete description of use cases and parameters.
     * @param BeginProductImportParam $param An object containing all the necessary parameters for this query.
     * @return BeginProductImportResult An object containing the results.
     * @throws PrediggoException in case of errors
     */
    public static function beginProductImport(BeginProductImportParam $param)
    {
        $request = new BeginProductImportRequest($param);

        self::executeCall($request);
        return $request->getResultObject();
    }



    /**
     * Execute an ProductImport query.
     * Please refer to the documentation for a complete description of use cases and parameters.
     * @param ProductImportParam $param An object containing all the necessary parameters for this query.
     * @return ProductImportResult An object containing the results.
     * @throws PrediggoException in case of errors
     */
    public static function productImport(ProductImportParam $param)
    {
        $request = new ProductImportRequest($param);

        self::executeCall($request);
        return $request->getResultObject();
    }



    /**
     * Execute an endProductImport query.
     * Please refer to the documentation for a complete description of use cases and parameters.
     * @param EndProductImportParam $param An object containing all the necessary parameters for this query.
     * @return EndProductImportResult An object containing the results.
     * @throws PrediggoException in case of errors
     */
    public static function endProductImport(EndProductImportParam $param)
    {
        $request = new EndProductImportRequest($param);

        self::executeCall($request);
        return $request->getResultObject();
    }




    /**
     * Execute an beginUserProfileImport query.
     * Please refer to the documentation for a complete description of use cases and parameters.
     * @param BeginUserProfileImportParam $param An object containing all the necessary parameters for this query.
     * @return BeginUserProfileImportResult An object containing the results.
     * @throws PrediggoException in case of errors
     */
    public static function beginUserProfileImport(BeginUserProfileImportParam $param)
    {
        $request = new BeginUserProfileImportRequest($param);

        self::executeCall($request);
        return $request->getResultObject();
    }



    /**
     * Execute an UserProfileImport query.
     * Please refer to the documentation for a complete description of use cases and parameters.
     * @param UserProfileImportParam $param An object containing all the necessary parameters for this query.
     * @return UserProfileImportResult An object containing the results.
     * @throws PrediggoException in case of errors
     */
    public static function userProfileImport(UserProfileImportParam $param)
    {
        $request = new UserProfileImportRequest($param);

        self::executeCall($request);
        return $request->getResultObject();
    }



    /**
     * Execute an endUserProfileImport query.
     * Please refer to the documentation for a complete description of use cases and parameters.
     * @param EndUserProfileImportParam $param An object containing all the necessary parameters for this query.
     * @return EndUserProfileImportResult An object containing the results.
     * @throws PrediggoException in case of errors
     */
    public static function endUserProfileImport(EndUserProfileImportParam $param)
    {
        $request = new EndUserProfileImportRequest($param);

        self::executeCall($request);
        return $request->getResultObject();
    }




    /**
     * Executes a servlet call.
     * @param RequestTemplate $request the request to execute.
     */
    private static function executeCall( RequestTemplate $request)
    {
        //execute request
        $request->doWebRequest();

        //check response status
        self::checkStatus($request->getResultObject());
    }


    /**
     * Checks the result code of a query.
     * @param RequestResultBase $result the result object to test
     * @throws PrediggoException in case the result contains an error.
     */
    private static function checkStatus(RequestResultBase $result)
    {
        $result->setStatusMessage ( self::getStatusMessageForStatusCode( $result->getStatus() ));

        //error returned?
        if ($result->getStatus() < 0)
            throw new PrediggoException( $result->getStatusMessage(). " (".  $result->getStatus() .")" );
    }

}

