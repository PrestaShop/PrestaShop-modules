<?php
require_once "CancellableOption.php";
require_once "CancellableOptionGroup.php";
require_once "DrillDownOptionGroup.php";
require_once "DrillDownOption.php";
require_once "RedirectionObject.php";
require_once "SortingOption.php";

require_once "GetSearchPageRecommendationResult.php";
require_once "GetSearchRecommendationBaseResultHandler.php";


/**
 * XML content handler specific to GetSearchPageRecommendation requests.
 *
 * @package prediggo4php
 * @subpackage xmlhandlers
 *
 * @author Stef
 */
class GetSearchPageRecommendationResultHandler extends GetSearchRecommendationBaseResultHandler
{

    protected $backTrackHashed = array();


    /**
     * Handles the current node in the xml reading loop.
     * @param DOMNode $node The current xml node
     * @param GetSearchPageRecommendationResult $resultObj The object which will be returned to the end-user
     * @return boolean True if the node was handled
     */
    protected function handleXmlReaderCurrentNode(DOMNode $node,  $resultObj)
    {
        if( parent::handleXmlReaderCurrentNode($node, $resultObj ))
            return true;

        switch ($node->nodeName)
        {
            case "redirectionObjects":
                
                $this->readRedirectionObjects($node, $resultObj );
                return true;

            case "sortingoptions":

                $this->readSortingOptions($node, $resultObj);
                return true;


            case "filteroptions":

                $this->readFilterOptions($node, $resultObj);
                return true;

            case "backtrackoptions":

                $this->readBackTrackOptions($node, $resultObj);
                return true;


        }

        return false;
    }


    /**
     * Reads a backtrackoptions element and adds the content to the result object.
     * @param DOMNode $backTrackOptionsNode the node being inspected
     * @param GetSearchPageRecommendationResult $resultObj the result object to fill
     */
    private function readBackTrackOptions( DOMNode $backTrackOptionsNode, GetSearchPageRecommendationResult $resultObj )
    {
        foreach( $backTrackOptionsNode->childNodes as $domainNode)
        {
            if( $domainNode->nodeName == "backtrackoption")
            {
                $option = new CancellableOption();
                $option->setTextValue( $domainNode->textContent );

                $groupFilteredName = '';
                $groupMultiSelection = false;

                //read attributes
                foreach ( $domainNode->attributes as $attribute )
                {
                    switch ($attribute->name)
                    {
                        case "attname":
                            $groupFilteredName = $attribute->value ;
                            break;

                        case "searchRefiningOptions":
                            $option->setSearchRefiningOption( $attribute->value );
                            break;

                        case "discreteVal":
                            $option->setDiscreteValue( $attribute->value );
                            break;

                        case "minVal":
                            $option->setRangeValueMin(  $attribute->value );
                            break;

                        case "maxVal":
                            $option->setRangeValueMax(  $attribute->value );
                            break;

                        case "multiplevaluesselection" :
                            if($attribute->value == "true")
                                $groupMultiSelection = true;
                            break;
                    }
                }

                $group = NULL;

                if( !array_key_exists( $groupFilteredName,  $this->backTrackHashed ) )
                {
                    $group = new CancellableOptionGroup();
                    $group->setFilteredAttributeName( $groupFilteredName );
                    $group->setMultiSelect( $groupMultiSelection ) ;
                    $resultObj->addCancellableFiltersGroup($group);

                    $this->backTrackHashed[ $groupFilteredName ] = $group;
                }
                else
                {
                    $group = $this->backTrackHashed[ $groupFilteredName ];
                }

                $group->addFilteringOption($option);
            }
        }
    }

    /**
     * Reads a filteroptions element and adds the content to the result object.
     * @param DOMNode $filterOptionsNode the node being inspected
     * @param GetSearchPageRecommendationResult $resultObj the result object to fill
     */
    private function readFilterOptions( DOMNode $filterOptionsNode, GetSearchPageRecommendationResult $resultObj )
    {

        foreach( $filterOptionsNode->childNodes as $filterNode)
        {
            if( $filterNode->nodeName == "filter")
            {
                $group = new DrillDownOptionGroup();

                //read attributes
                foreach ( $filterNode->attributes as $attribute )
                {
                    switch ($attribute->name)
                    {
                        case "attname":
                            $group->setFilteredAttributeName(  $attribute->value );
                            break;

                        case "multiplevaluesselection"  :
                            if( $attribute->value == "true" )
                                $group->setMultiSelect( true );

                            break;

                    }
                }

                foreach( $filterNode->childNodes as $domainValuesNode)
                {
                    if( $domainValuesNode->nodeType == XML_ELEMENT_NODE )
                        $this->readFilterDomainValuesOptions( $domainValuesNode, $group);
                }

                $resultObj->addDrillDownOptionGroup($group);
            }
        }
    }


    /**
     * Reads a domainValues element and adds the content to the result object.
     * @param DOMNode $domainValuesNode the node being inspected
     * @param DrillDownOptionGroup $group the result object to fill
     */
    private function readFilterDomainValuesOptions( DOMNode $domainValuesNode, DrillDownOptionGroup $group )
    {

        foreach( $domainValuesNode->childNodes as $domainNode)
        {
            if( $domainNode->nodeName == "domainValue")
            {
                $drillOption = new DrillDownOption();
                $drillOption->setTextValue( $domainNode->textContent );

                //read attributes
                foreach ( $domainNode->attributes as $attribute )
                {
                    switch ($attribute->name)
                    {
                        case "searchRefiningOptions":
                            $drillOption->setSearchRefiningOption( $attribute->value );
                            break;

                        case "nbOccurences":
                            $drillOption->setNbOccurences( intval( $attribute->value) );
                            break;

                        case "discreteVal":
                            $drillOption->setDiscreteValue( $attribute->value );
                            break;

                        case "minVal":
                            $drillOption->setRangeValueMin(  $attribute->value );
                            break;

                        case "maxVal":
                            $drillOption->setRangeValueMax(  $attribute->value );
                            break;

                        case "selected":

                            if( $attribute->value == "true" )
                                $drillOption->setSelected( true );
                            break;
                    }
                }

                $group->addFilteringOption($drillOption);
            }
        }
    }

    /**
     * Reads a sortingoptions element and adds the content to the result object.
     * @param DOMNode $sortingOptionsNode the node being inspected
     * @param GetSearchPageRecommendationResult $resultObj the result object to fill
     */
    private function readSortingOptions( DOMNode $sortingOptionsNode, GetSearchPageRecommendationResult $resultObj )
    {
        foreach( $sortingOptionsNode->childNodes as $optionNode)
        {
            if( $optionNode->nodeName == "sortingoption")
            {
                $sorting = new SortingOption();


                //read attributes
                foreach ( $optionNode->attributes as $attribute )
                {
                    switch ($attribute->name)
                    {
                        case "searchRefiningOptions":
                            $sorting->setSearchRefiningOption( $attribute->value );
                            break;

                        case "sortingCode":
                            $sorting->setClause( $attribute->value );
                            break;
                    }
                }

                $resultObj->addSortingOption($sorting);
            }
        }
    }

    
    /**
     * Reads a redirectionObjects element and adds the content to the result object.
     * @param DOMNode $redirectionObjectsNode the node being inspected
     * @param GetSearchPageRecommendationResult $resultObj the result object to fill
     */
    private function readRedirectionObjects( DOMNode $redirectionObjectsNode, GetSearchPageRecommendationResult $resultObj )
    {
        foreach( $redirectionObjectsNode->childNodes as $redirectionNode)
        {
            if( $redirectionNode->nodeName == "redirectionObject")
            {
                $redir = new RedirectionObject();
                

                //read attributes
                foreach ( $redirectionNode->attributes as $attribute )
                {
                    switch ($attribute->name)
                    {
                        case "redirectionURL":
                            $redir->setTargetUrl( $attribute->value );
                            break;
                        
                        case "redirectionLabelToUser":
                            $redir->setLabel( $attribute->value );
                            break;
                            
                        case "redirectionPictureURL" :
                            $redir->setPictureUrl( $attribute->value );
                            break;
                    }
                }

                $resultObj->addCustomRedirection($redir);
            }
        }
    }
}

