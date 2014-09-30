<?php
/**
 * 2007-2014 [PagSeguro Internet Ltda.]
 *
 * NOTICE OF LICENSE
 *
 *Licensed under the Apache License, Version 2.0 (the "License");
 *you may not use this file except in compliance with the License.
 *You may obtain a copy of the License at
 *
 *http://www.apache.org/licenses/LICENSE-2.0
 *
 *Unless required by applicable law or agreed to in writing, software
 *distributed under the License is distributed on an "AS IS" BASIS,
 *WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *See the License for the specific language governing permissions and
 *limitations under the License.
 *
 *  @author    PagSeguro Internet Ltda.
 *  @copyright 2007-2014 PagSeguro Internet Ltda.
 *  @license   http://www.apache.org/licenses/LICENSE-2.0
 */

/***
 * Represent a metadata
 */
class PagSeguroMetaData
{

    /***
     * @var array
     */
    private $items;

    /***
     * @param array $items
     */
    public function __construct(array $items = null)
    {
        if (!is_null($items) && count($items) > 0) {
            $this->setItems($items);
        }
    }

    /***
     * @param PagSeguroMetaDataItem $metaDataItem
     */
    public function addItem(PagSeguroMetaDataItem $metaDataItem)
    {
        $this->items[] = $metaDataItem;
    }

    /***
     * @param array $items
     */
    public function setItems(array $items)
    {
        $this->items = $items;
    }

    /***
     * @return array
     */
    public function getItems()
    {
        if ($this->items == null) {
            $this->items = array();
        }
        return $this->items;
    }
}
