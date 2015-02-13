<?php

/**
 * Shopgate GmbH
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file AFL_license.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to interfaces@shopgate.com so we can send you a copy immediately.
 *
 * @author     Shopgate GmbH, Schloßstraße 10, 35510 Butzbach <interfaces@shopgate.com>
 * @copyright  Shopgate GmbH
 * @license    http://opensource.org/licenses/AFL-3.0 Academic Free License ("AFL"), in the version 3.0
 *
 * User: awesselburg
 * Date: 07.03.14
 * Time: 08:17
 *
 * File: Product.php
 *
 * @method                                      setUid(string $value)
 * @method string                               getUid()
 *
 * @method                                      setLastUpdate(string $value)
 * @method string                               getLastUpdate()
 *
 * @method                                      setName(string $value)
 * @method string                               getName()
 *
 * @method                                      setTaxPercent(float $value)
 * @method float                                getTaxPercent()
 *
 * @method                                      setTaxClass(string $value)
 * @method string                               getTaxClass()
 *
 * @method                                      setCurrency(string $value)
 * @method string                               getCurrency()
 *
 * @method                                      setDescription(string $value)
 * @method string                               getDescription()
 *
 * @method                                      setDeeplink(string $value)
 * @method string                               getDeeplink()
 *
 * @method                                      setPromotionSortOrder(int $value)
 * @method int                                  getPromotionSortOrder()
 *
 * @method                                      setInternalOrderInfo(string $value)
 * @method string                               getInternalOrderInfo()
 *
 * @method                                      setAgeRating(int $value)
 * @method int                                  getAgeRating()
 *
 * @method                                      setPrice(Shopgate_Model_Catalog_Price $value)
 * @method Shopgate_Model_Catalog_Price         getPrice()
 *
 * @method                                      setWeight(float $value)
 * @method float                                getWeight()
 *
 * @method                                      setWeightUnit(string $value)
 * @method string                               getWeightUnit()
 *
 * @method                                      setImages(array $value)
 * @method array                                getImages()
 *
 * @method                                      setCategoryPaths(array $value)
 * @method array                                getCategoryPaths()
 *
 * @method                                      setShipping(Shopgate_Model_Catalog_Shipping $value)
 * @method Shopgate_Model_Catalog_Shipping      getShipping()
 *
 * @method                                      setManufacturer(Shopgate_Model_Catalog_Manufacturer $value)
 * @method Shopgate_Model_Catalog_Manufacturer  getManufacturer()
 *
 * @method                                      setVisibility(Shopgate_Model_Catalog_Visibility $value)
 * @method Shopgate_Model_Catalog_Visibility    getVisibility()
 *
 * @method                                      setProperties(array $value)
 * @method array                                getProperties()
 *
 * @method                                      setStock(Shopgate_Model_Catalog_Stock $value)
 * @method Shopgate_Model_Catalog_Stock         getStock()
 *
 * @method                                      setIdentifiers(array $value)
 * @method array                                getIdentifiers()
 *
 * @method                                      setTags(array $value)
 * @method array                                getTags()
 *
 * @method                                      setRelations(array $value)
 * @method array                                getRelations()
 *
 * @method                                      setAttributeGroups(array $value)
 * @method array                                getAttributeGroups()
 *
 * @method                                      setAttributes(array $value)
 * @method array                                getAttributes()
 *
 * @method                                      setInputs(array $value)
 * @method array                                getInputs()
 *
 * @method                                      setAttachments(array $value)
 * @method array                                getAttachments()
 *
 * @method                                      setIsDefaultChild(bool $value)
 * @method bool                                 getIsDefaultChild()
 *
 * @method                                      setChildren(array $value)
 *
 * @method                                      setDisplayType(string $value)
 * @method string                               getDisplayType()
 *
 */
class Shopgate_Model_Catalog_Product extends Shopgate_Model_AbstractExport
{

    /**
     * define identifier uid
     */
    const DEFAULT_IDENTIFIER_UID = 'uid';

    /**
     * define remove empty children nodes
     */
    const DEFAULT_CLEAN_CHILDREN_NODES = true;

    /**
     * define default item identifier
     */
    const DEFAULT_ITEM_IDENTIFIER = 'item';

    /**
     * define clean children
     */
    const DEFAULT_CLEAN_CHILDREN = true;

    /**
     * weight units
     */
    const DEFAULT_WEIGHT_UNIT_KG      = 'kg';
    const DEFAULT_WEIGHT_UNIT_OUNCE   = 'oz';
    const DEFAULT_WEIGHT_UNIT_GRAM    = 'g';
    const DEFAULT_WEIGHT_UNIT_POUND   = 'lb';
    const DEFAULT_WEIGHT_UNIT_DEFAULT = self::DEFAULT_WEIGHT_UNIT_GRAM;

    /**
     * @deprecated use Shopgate_Model_Catalog_Product::DEFAULT_WEIGHT_UNIT_GRAM
     */
    const DEFAULT_WEIGHT_UNIT_GRAMM   = self::DEFAULT_WEIGHT_UNIT_GRAM;
    /**
     * tax
     */
    const DEFAULT_NO_TAXABLE_CLASS_NAME = 'no tax class';

    /**
     * display_type
     */
    const DISPLAY_TYPE_DEFAULT = 'default';
    const DISPLAY_TYPE_SIMPLE  = 'simple';
    const DISPLAY_TYPE_SELECT  = 'select';
    const DISPLAY_TYPE_LIST    = 'list';


    /**
     * @var string
     */
    protected $itemNodeIdentifier = '<items></items>';

    /**
     * @var string
     */
    protected $identifier = 'items';

    /**
     * define xsd file location
     *
     * @var string
     */
    protected $xsdFileLocation = 'catalog/products.xsd';

    /**
     * @var bool
     */
    protected $isChild = false;

    /**
     * define allowed methods
     *
     * @var array
     */
    protected $allowedMethods = array(
        'Uid',
        'LastUpdate',
        'Name',
        'TaxPercent',
        'TaxClass',
        'Currency',
        'Description',
        'Deeplink',
        'PromotionSortOrder',
        'InternalOrderInfo',
        'Price',
        'Weight',
        'WeightUnit',
        'Images',
        'CategoryPaths',
        'Shipping',
        'Manufacturer',
        'Visibility',
        'Properties',
        'Stock',
        'Identifiers',
        'Tags',
        'Relations',
        'AttributeGroups',
        'Attributes',
        'Inputs',
        'Attachments',
        'IsDefaultChild',
        'Children',
        'AgeRating',
        'DisplayType'
    );

    /**
     * @var array
     */
    protected $fireMethods = array(
        'setLastUpdate',
        'setUid',
        'setName',
        'setTaxPercent',
        'setTaxClass',
        'setCurrency',
        'setDescription',
        'setDeeplink',
        'setPromotionSortOrder',
        'setInternalOrderInfo',
        'setAgeRating',
        'setWeight',
        'setWeightUnit',
        'setPrice',
        'setShipping',
        'setManufacturer',
        'setVisibility',
        'setStock',
        'setImages',
        'setCategoryPaths',
        'setProperties',
        'setIdentifiers',
        'setTags',
        'setRelations',
        'setAttributeGroups',
        'setInputs',
        'setAttachments',
        'setChildren',
        'setDisplayType'
    );

    /**
     * init default object
     */
    public function __construct()
    {
        $this->setData(
             array(
                 'price'            => new Shopgate_Model_Catalog_Price(),
                 'shipping'         => new Shopgate_Model_Catalog_Shipping(),
                 'manufacturer'     => new Shopgate_Model_Catalog_Manufacturer(),
                 'visibility'       => new Shopgate_Model_Catalog_Visibility(),
                 'stock'            => new Shopgate_Model_Catalog_Stock(),
                 'inputs'           => array(),
                 'children'         => array(),
                 'attribute_groups' => array(),
                 'relations'        => array(),
                 'tags'             => array(),
                 'identifiers'      => array(),
                 'properties'       => array(),
                 'category_paths'   => array(),
                 'images'           => array(),
                 'attachments'      => array(),
                 'attributes'       => array()
             )
        );
    }

    /**
     * get is child
     *
     * @return bool
     */
    public function getIsChild()
    {
        return $this->isChild;
    }

    /**
     * set is child
     *
     * @param $value
     */
    public function setIsChild($value)
    {
        $this->isChild = $value;
    }

    /**
     * generate xml result object
     *
     * @param Shopgate_Model_XmlResultObject $itemsNode
     *
     * @return Shopgate_Model_XmlResultObject
     */
    public function asXml(Shopgate_Model_XmlResultObject $itemsNode)
    {
        /**
         * global info
         *
         * @var $itemNode Shopgate_Model_XmlResultObject
         */
        $itemNode = $itemsNode->addChild(self::DEFAULT_ITEM_IDENTIFIER);

        $itemNode->addAttribute('uid', $this->getUid());
        $itemNode->addAttribute('last_update', $this->getLastUpdate());
        $itemNode->addChildWithCDATA('name', $this->getName());
        $itemNode->addChild('tax_percent', $this->getTaxPercent());
        $itemNode->addChild('tax_class', $this->getTaxClass());
        $itemNode->addChild('currency', $this->getCurrency());
        $itemNode->addChildWithCDATA('description', $this->getDescription());
        $itemNode->addChildWithCDATA('deeplink', $this->getDeeplink());
        $itemNode->addChild('promotion')->addAttribute('sort_order', $this->getPromotionSortOrder());
        $itemNode->addChildWithCDATA('internal_order_info', $this->getInternalOrderInfo());
        $itemNode->addChild('age_rating', $this->getAgeRating());
        $itemNode->addChild('weight', $this->getWeight())->addAttribute('unit', $this->getWeightUnit());

        /**
         * is default child
         */
        if ($this->getIsChild()) {
            $itemNode->addAttribute('default_child', $this->getIsDefaultChild());
        }

        /**
         * prices / tier prices
         */
        $this->getPrice()->asXml($itemNode);

        /**
         * images
         *
         * @var Shopgate_Model_XmlResultObject $imagesNode
         * @var Shopgate_Model_Media_Image     $imageItem
         */
        $imagesNode = $itemNode->addChild('images');
        foreach ($this->getImages() as $imageItem) {
            $imageItem->asXml($imagesNode);
        }

        /**
         * categories
         *
         * @var Shopgate_Model_XmlResultObject      $categoryPathNode
         * @var Shopgate_Model_Catalog_CategoryPath $categoryPathItem
         */
        $categoryPathNode = $itemNode->addChild('categories');
        foreach ($this->getCategoryPaths() as $categoryPathItem) {
            $categoryPathItem->asXml($categoryPathNode);
        }

        /**
         * shipping
         */
        $this->getShipping()->asXml($itemNode);

        /**
         * manufacture
         */
        $this->getManufacturer()->asXml($itemNode);

        /**
         * visibility
         */
        $this->getVisibility()->asXml($itemNode);

        /**
         * properties
         *
         * @var Shopgate_Model_XmlResultObject  $propertiesNode
         * @var Shopgate_Model_Catalog_Property $propertyItem
         */
        $propertiesNode = $itemNode->addChild('properties');
        foreach ($this->getProperties() as $propertyItem) {
            $propertyItem->asXml($propertiesNode);
        }

        /**
         * stock
         */
        $this->getStock()->asXml($itemNode);

        /**
         * identifiers
         *
         * @var Shopgate_Model_XmlResultObject    $identifiersNode
         * @var Shopgate_Model_Catalog_Identifier $identifierItem
         */
        $identifiersNode = $itemNode->addChild('identifiers');
        foreach ($this->getIdentifiers() as $identifierItem) {
            $identifierItem->asXml($identifiersNode);
        }

        /**
         * tags
         *
         * @var Shopgate_Model_XmlResultObject $tagsNode
         * @var Shopgate_Model_Catalog_Tag     $tagItem
         */
        $tagsNode = $itemNode->addChild('tags');
        foreach ($this->getTags() as $tagItem) {
            $tagItem->asXml($tagsNode);
        }

        /**
         * relations
         *
         * @var Shopgate_Model_XmlResultObject  $relationsNode
         * @var Shopgate_Model_Catalog_Relation $relationItem
         */
        $relationsNode = $itemNode->addChild('relations');
        foreach ($this->getRelations() as $relationItem) {
            $relationItem->asXml($relationsNode);
        }

        /**
         * attribute / options
         *
         * @var Shopgate_Model_XmlResultObject        $attributeGroupsNode
         * @var Shopgate_Model_XmlResultObject        $attributesNode
         * @var Shopgate_Model_Catalog_Attribute      $attributeItem
         * @var Shopgate_Model_Catalog_AttributeGroup $attributeGroupItem
         */
        if ($this->getIsChild()) {
            $attributesNode = $itemNode->addChild('attributes');
            foreach ($this->getAttributes() as $attributeItem) {
                $attributeItem->asXml($attributesNode);
            }
        } else {
            $attributeGroupsNode = $itemNode->addChild('attribute_groups');
            foreach ($this->getAttributeGroups() as $attributeGroupItem) {
                $attributeGroupItem->asXml($attributeGroupsNode);
            }
        }

        /**
         * inputs
         *
         * @var Shopgate_Model_XmlResultObject $inputsNode
         * @var Shopgate_Model_Catalog_Input   $inputItem
         */
        $inputsNode = $itemNode->addChild('inputs');
        foreach ($this->getInputs() as $inputItem) {
            $inputItem->asXml($inputsNode);
        }

        $itemNode->addChild('display_type', $this->getDisplayType());

        /**
         * children
         *
         * @var Shopgate_Model_XmlResultObject $childrenNode
         * @var object                         $itemNode ->children
         * @var Shopgate_Model_Catalog_Product $child
         * @var Shopgate_Model_XmlResultObject $childXml
         */
        if (!$this->getIsChild()) {
            $childrenNode = $itemNode->addChild('children');
            foreach ($this->getChildren() as $child) {
                $child->asXml($childrenNode);
            }
            /**
             * remove empty nodes
             */
            if (self::DEFAULT_CLEAN_CHILDREN_NODES && count($this->getChildren()) > 0) {
                foreach ($itemNode->children as $childXml) {
                    $itemNode->replaceChild($this->removeEmptyNodes($childXml), $itemNode->children);
                }
            }
        }

        return $itemsNode;
    }

    /**
     * add image
     *
     * @param Shopgate_Model_Media_Image $image
     */
    public function addImage(Shopgate_Model_Media_Image $image)
    {
        $images = $this->getImages();
        array_push($images, $image);
        $this->setImages($images);
    }

    /**
     * add child
     *
     * @param Shopgate_Model_Catalog_Product $child
     */
    public function addChild($child)
    {
        $children = $this->getChildren();
        array_push($children, $child);
        $this->setChildren($children);
    }

    /**
     * @return array
     */
    public function getChildren()
    {
        if (self::DEFAULT_CLEAN_CHILDREN) {
            foreach (parent::getData('children') as $child) {
                $this->cleanChildData($this, $child);
            }
        }

        return parent::getData('children');
    }

    /**
     * add category
     *
     * @param Shopgate_Model_Catalog_CategoryPath $categoryPath
     */
    public function addCategoryPath(Shopgate_Model_Catalog_CategoryPath $categoryPath)
    {
        $categoryPaths = $this->getCategoryPaths();
        array_push($categoryPaths, $categoryPath);
        $this->setCategoryPaths($categoryPaths);
    }

    /**
     * add attribute group
     *
     * @param Shopgate_Model_Catalog_AttributeGroup $attributeGroup
     */
    public function addAttributeGroup($attributeGroup)
    {
        $attributesGroups = $this->getAttributeGroups();
        array_push($attributesGroups, $attributeGroup);
        $this->setAttributeGroups($attributesGroups);
    }

    /**
     * add property
     *
     * @param Shopgate_Model_Catalog_Property $property
     */
    public function addProperty($property)
    {
        $properties = $this->getProperties();
        array_push($properties, $property);
        $this->setProperties($properties);
    }

    /**
     * add identifier
     *
     * @param Shopgate_Model_Catalog_Identifier $identifier
     */
    public function addIdentifier($identifier)
    {
        $identifiers = $this->getIdentifiers();
        array_push($identifiers, $identifier);
        $this->setIdentifiers($identifiers);
    }

    /**
     * add tag
     *
     * @param Shopgate_Model_Catalog_Tag $tag
     */
    public function addTag($tag)
    {
        $tags = $this->getTags();
        array_push($tags, $tag);
        $this->setTags($tags);
    }

    /**
     * add relation
     *
     * @param Shopgate_Model_Catalog_Relation $relation
     */
    public function addRelation($relation)
    {
        $relations = $this->getRelations();
        array_push($relations, $relation);
        $this->setRelations($relations);
    }

    /**
     * add input
     *
     * @param Shopgate_Model_Catalog_Input $input
     */
    public function addInput($input)
    {
        $inputs = $this->getInputs();
        array_push($inputs, $input);
        $this->setInputs($inputs);
    }

    /**
     * add attribute option
     *
     * @param Shopgate_Model_Catalog_Attribute $attribute
     */
    public function addAttribute($attribute)
    {
        $attributes = $this->getAttributes();
        array_push($attributes, $attribute);
        $this->setAttributes($attributes);
    }

	/**
	 * @param Shopgate_Model_XmlResultObject $childItem
	 *
	 * @return SimpleXMLElement
	 */
	public function removeEmptyNodes($childItem) {

		$doc = new DOMDocument;
		$doc->preserveWhiteSpace = false;
		$doc->loadXML($childItem->asXML());

		$xpath = new DOMXPath($doc);

		foreach($xpath->query('//*[not(node()) or normalize-space() = ""]') as $node) {
			$node->parentNode->removeChild($node);
		}

		return simplexml_import_dom($doc);
	}

    /**
     * generate json result object
     *
     * @return array
     */
    public function asArray()
    {
        $productResult = new Shopgate_Model_Abstract();

        $productResult->setData('uid', $this->getUid());
        $productResult->setData('last_update', $this->getLastUpdate());
        $productResult->setData('name', $this->getName());
        $productResult->setData('tax_percent', $this->getTaxPercent());
        $productResult->setData('tax_class', $this->getTaxClass());
        $productResult->setData('currency', $this->getCurrency());
        $productResult->setData('description', $this->getDescription());
        $productResult->setData('deeplink', $this->getDeeplink());
        $productResult->setData('promotion_sort_order', $this->getPromotionSortOrder());
        $productResult->setData('internal_order_info', $this->getInternalOrderInfo());
        $productResult->setData('age_rating', $this->getAgeRating());
        $productResult->setData('weight', $this->getWeight());
        $productResult->setData('weight_unit', $this->getWeightUnit());
        $productResult->setData('display_type', $this->getDisplayType());

        //prices

        /**
         * images
         *
         * @var Shopgate_Model_Media_Image $image
         */
        $imagesData = array();
        foreach ($this->getImages() as $image) {
            array_push($imagesData, $image->asArray());
        }
        $productResult->setData('images', $imagesData);

        /**
         * category paths
         *
         * @var Shopgate_Model_Catalog_CategoryPath $categoryPath
         */
        $categoryPathsData = array();
        foreach ($this->getCategoryPaths() as $categoryPath) {
            array_push($categoryPathsData, $categoryPath->asArray());
        }
        $productResult->setData('categories', $categoryPathsData);

        /**
         * shipping
         */
        $productResult->setData('shipping', $this->getShipping()->asArray());

        /**
         * manufacturer
         */
        $productResult->setData('manufacturer', $this->getManufacturer()->asArray());

        /**
         * visibility
         */
        $productResult->setData('visibility', $this->getVisibility()->asArray());

        /**
         * properties
         *
         * @var Shopgate_Model_Catalog_Property $property
         */
        $propertiesData = array();
        foreach ($this->getProperties() as $property) {
            array_push($propertiesData, $property->asArray());
        }
        $productResult->setData('properties', $propertiesData);

        /**
         * stock
         */
        $productResult->setData('stock', $this->getStock()->asArray());

        /**
         * identifiers
         *
         * @var Shopgate_Model_Catalog_Identifier $identifier
         */
        $identifiersData = array();
        foreach ($this->getIdentifiers() as $identifier) {
            array_push($identifiersData, $identifier->asArray());
        }
        $productResult->setData('identifiers', $identifiersData);

        /**
         * tags
         *
         * @var Shopgate_Model_Catalog_Tag $tag
         */
        $tagsData = array();
        foreach ($this->getTags() as $tag) {
            array_push($tagsData, $tag->asArray());
        }
        $productResult->setData('tags', $tagsData);

        return $productResult->getData();
    }

    /**
     * generate csv result object
     */
    public function asCsv()
    {
    }

	/**
	 * @param Shopgate_Model_Abstract $parentItem
	 * @param Shopgate_Model_Abstract $childItem
	 */
	protected function cleanChildData($parentItem, $childItem) {
		foreach ($childItem->getData() as $childKey => $childValue) {
			/**
			 * array or object
			 */
			if (is_array($childValue) || $childValue instanceof Shopgate_Model_Abstract) {
				/**
				 * array
				 */
				if(is_array($childValue) && count($childValue) > 0) {
					if($childValue == $parentItem->getData($childKey)) {
						$childItem->setData($childKey, array());
					}
				} elseif ($childValue instanceof Shopgate_Model_Abstract) {
					if($childValue == $parentItem->getData($childKey)) {
						$class = get_class($childValue);
						$childItem->setData($childKey, new $class);
					}
				}
				/**
				 * string
				 */
			} else {
				if($childValue == $parentItem->getData($childKey)) {
					$childItem->setData($childKey, null);
				}
			}
		}
	}

    /**
     * @param array $data
     * @param int   $uid
     *
     * @return mixed
     */
    protected function getItemByUid($data, $uid)
    {
        /* @var Shopgate_Model_Abstract $item */
        foreach ($data as $item) {
            if ($item->getData(self::DEFAULT_IDENTIFIER_UID) == $uid) {
                return $item;
            }
        }

        return false;
    }
}