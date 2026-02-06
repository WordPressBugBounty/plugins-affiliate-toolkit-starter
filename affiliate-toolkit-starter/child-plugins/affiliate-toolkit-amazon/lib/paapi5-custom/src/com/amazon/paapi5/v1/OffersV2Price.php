<?php

/**
 * Copyright 2020 Amazon.com, Inc. or its affiliates. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License").
 * You may not use this file except in compliance with the License.
 * A copy of the License is located at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * or in the "license" file accompanying this file. This file is distributed
 * on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either
 * express or implied. See the License for the specific language governing
 * permissions and limitations under the License.
 */

namespace Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1;

use \ArrayAccess;
use \Amazon\ProductAdvertisingAPI\v1\ObjectSerializer;

/**
 * OffersV2Price Class Doc Comment
 *
 * @category Class
 * @package  Amazon\ProductAdvertisingAPI\v1
 * @author   Product Advertising API team
 */
class OffersV2Price implements ModelInterface, ArrayAccess
{
    const DISCRIMINATOR = null;

    /**
      * The original name of the model.
      *
      * @var string
      */
    protected static $swaggerModelName = 'OffersV2Price';

    /**
      * Array of property to type mappings. Used for (de)serialization
      *
      * @var string[]
      */
    protected static $swaggerTypes = [
        'money' => '\Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\OffersV2Money',
        'pricePerUnit' => '\Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\OffersV2PricePerUnit',
        'savingBasis' => '\Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\OffersV2SavingBasis',
        'savings' => '\Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\OffersV2Savings'
    ];

    /**
      * Array of property to format mappings. Used for (de)serialization
      *
      * @var string[]
      */
    protected static $swaggerFormats = [
        'money' => null,
        'pricePerUnit' => null,
        'savingBasis' => null,
        'savings' => null
    ];

    /**
     * Array of property to type mappings. Used for (de)serialization
     *
     * @return array
     */
    public static function swaggerTypes()
    {
        return self::$swaggerTypes;
    }

    /**
     * Array of property to format mappings. Used for (de)serialization
     *
     * @return array
     */
    public static function swaggerFormats()
    {
        return self::$swaggerFormats;
    }

    /**
     * Array of attributes where the key is the local name,
     * and the value is the original name
     *
     * @var string[]
     */
    protected static $attributeMap = [
        'money' => 'Money',
        'pricePerUnit' => 'PricePerUnit',
        'savingBasis' => 'SavingBasis',
        'savings' => 'Savings'
    ];

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @var string[]
     */
    protected static $setters = [
        'money' => 'setMoney',
        'pricePerUnit' => 'setPricePerUnit',
        'savingBasis' => 'setSavingBasis',
        'savings' => 'setSavings'
    ];

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @var string[]
     */
    protected static $getters = [
        'money' => 'getMoney',
        'pricePerUnit' => 'getPricePerUnit',
        'savingBasis' => 'getSavingBasis',
        'savings' => 'getSavings'
    ];

    /**
     * Array of attributes where the key is the local name,
     * and the value is the original name
     *
     * @return array
     */
    public static function attributeMap()
    {
        return self::$attributeMap;
    }

    /**
     * Array of attributes to setter functions (for deserialization of responses)
     *
     * @return array
     */
    public static function setters()
    {
        return self::$setters;
    }

    /**
     * Array of attributes to getter functions (for serialization of requests)
     *
     * @return array
     */
    public static function getters()
    {
        return self::$getters;
    }

    /**
     * The original name of the model.
     *
     * @return string
     */
    public function getModelName()
    {
        return self::$swaggerModelName;
    }

    /**
     * Associative array for storing property values
     *
     * @var mixed[]
     */
    protected $container = [];

    /**
     * Constructor
     *
     * @param mixed[] $data Associated array of property values
     *                      initializing the model
     */
    public function __construct(array $data = null)
    {
        $this->container['money'] = isset($data['money']) ? $data['money'] : null;
        $this->container['pricePerUnit'] = isset($data['pricePerUnit']) ? $data['pricePerUnit'] : null;
        $this->container['savingBasis'] = isset($data['savingBasis']) ? $data['savingBasis'] : null;
        $this->container['savings'] = isset($data['savings']) ? $data['savings'] : null;
    }

    /**
     * Show all the invalid properties with reasons.
     *
     * @return array invalid properties with reasons
     */
    public function listInvalidProperties()
    {
        $invalidProperties = [];
        return $invalidProperties;
    }

    /**
     * Validate all the properties in the model
     * return true if all passed
     *
     * @return bool True if all properties are valid
     */
    public function valid()
    {
        return count($this->listInvalidProperties()) === 0;
    }

    /**
     * Gets money
     *
     * @return \Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\OffersV2Money
     */
    public function getMoney()
    {
        return $this->container['money'];
    }

    /**
     * Sets money
     *
     * @param \Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\OffersV2Money $money money
     *
     * @return $this
     */
    public function setMoney($money)
    {
        $this->container['money'] = $money;
        return $this;
    }

    /**
     * Gets pricePerUnit
     *
     * @return \Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\OffersV2PricePerUnit
     */
    public function getPricePerUnit()
    {
        return $this->container['pricePerUnit'];
    }

    /**
     * Sets pricePerUnit
     *
     * @param \Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\OffersV2PricePerUnit $pricePerUnit pricePerUnit
     *
     * @return $this
     */
    public function setPricePerUnit($pricePerUnit)
    {
        $this->container['pricePerUnit'] = $pricePerUnit;
        return $this;
    }

    /**
     * Gets savingBasis
     *
     * @return \Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\OffersV2SavingBasis
     */
    public function getSavingBasis()
    {
        return $this->container['savingBasis'];
    }

    /**
     * Sets savingBasis
     *
     * @param \Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\OffersV2SavingBasis $savingBasis savingBasis
     *
     * @return $this
     */
    public function setSavingBasis($savingBasis)
    {
        $this->container['savingBasis'] = $savingBasis;
        return $this;
    }

    /**
     * Gets savings
     *
     * @return \Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\OffersV2Savings
     */
    public function getSavings()
    {
        return $this->container['savings'];
    }

    /**
     * Sets savings
     *
     * @param \Amazon\ProductAdvertisingAPI\v1\com\amazon\paapi5\v1\OffersV2Savings $savings savings
     *
     * @return $this
     */
    public function setSavings($savings)
    {
        $this->container['savings'] = $savings;
        return $this;
    }

    /**
     * Returns true if offset exists. False otherwise.
     *
     * @param integer $offset Offset
     *
     * @return boolean
     */
    public function offsetExists($offset)
    {
        return isset($this->container[$offset]);
    }

    /**
     * Gets offset.
     *
     * @param integer $offset Offset
     *
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return isset($this->container[$offset]) ? $this->container[$offset] : null;
    }

    /**
     * Sets value based on offset.
     *
     * @param integer $offset Offset
     * @param mixed   $value  Value to be set
     *
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->container[] = $value;
        } else {
            $this->container[$offset] = $value;
        }
    }

    /**
     * Unsets offset.
     *
     * @param integer $offset Offset
     *
     * @return void
     */
    public function offsetUnset($offset)
    {
        unset($this->container[$offset]);
    }

    /**
     * Gets the string presentation of the object
     *
     * @return string
     */
    public function __toString()
    {
        if (defined('JSON_PRETTY_PRINT')) {
            return json_encode(
                ObjectSerializer::sanitizeForSerialization($this),
                JSON_PRETTY_PRINT
            );
        }

        return json_encode(ObjectSerializer::sanitizeForSerialization($this));
    }
}

