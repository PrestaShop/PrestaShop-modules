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
 * Class that represents a Sender Document
 */
class PagSeguroSenderDocument
{

    /***
     * The type of document
     * @var string
     */
    private $type;

    /***
     * The value of document
     * @var string
     */
    private $value;

    /***
     * @param $type
     * @param $value
     */
    public function __construct($type, $value)
    {
        if ($type && $value) {
            $this->setType($type);
            $this->setValue($value);
        }
    }

    /***
     * Get document type
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /***
     * Set document type
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = Tools::strtoupper($type);
    }

    /***
     * Get document value
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /***
     * Set document value
     * @param string $value
     */
    public function setValue($value)
    {
        $this->value = PagSeguroHelper::getOnlyNumbers($value);
    }

    /***
     * Gets toString class
     * @return string
     */
    public function toString()
    {
        $document = array();
        $document['type'] = $this->type;
        $document['value'] = $this->value;

        return "PagSeguroSenderDocument: " . var_export($document, true);
    }
}
