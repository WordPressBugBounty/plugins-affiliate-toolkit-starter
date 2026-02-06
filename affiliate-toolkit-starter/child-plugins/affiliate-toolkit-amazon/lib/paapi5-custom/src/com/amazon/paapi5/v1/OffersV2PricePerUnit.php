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
 * OffersV2PricePerUnit Class Doc Comment
 *
 * @category Class
 * @package  Amazon\ProductAdvertisingAPI\v1
 * @author   Product Advertising API team
 */
class OffersV2PricePerUnit implements ModelInterface, ArrayAccess
{
    const DISCRIMINATOR = null;

    protected static $swaggerModelName = 'OffersV2PricePerUnit';

    protected static $swaggerTypes = [
        'amount' => 'float',
        'currency' => 'string',
        'displayAmount' => 'string'
    ];

    protected static $swaggerFormats = [
        'amount' => null,
        'currency' => null,
        'displayAmount' => null
    ];

    public static function swaggerTypes()
    {
        return self::$swaggerTypes;
    }

    public static function swaggerFormats()
    {
        return self::$swaggerFormats;
    }

    protected static $attributeMap = [
        'amount' => 'Amount',
        'currency' => 'Currency',
        'displayAmount' => 'DisplayAmount'
    ];

    protected static $setters = [
        'amount' => 'setAmount',
        'currency' => 'setCurrency',
        'displayAmount' => 'setDisplayAmount'
    ];

    protected static $getters = [
        'amount' => 'getAmount',
        'currency' => 'getCurrency',
        'displayAmount' => 'getDisplayAmount'
    ];

    public static function attributeMap()
    {
        return self::$attributeMap;
    }

    public static function setters()
    {
        return self::$setters;
    }

    public static function getters()
    {
        return self::$getters;
    }

    public function getModelName()
    {
        return self::$swaggerModelName;
    }

    protected $container = [];

    public function __construct(array $data = null)
    {
        $this->container['amount'] = isset($data['amount']) ? $data['amount'] : null;
        $this->container['currency'] = isset($data['currency']) ? $data['currency'] : null;
        $this->container['displayAmount'] = isset($data['displayAmount']) ? $data['displayAmount'] : null;
    }

    public function listInvalidProperties()
    {
        $invalidProperties = [];
        return $invalidProperties;
    }

    public function valid()
    {
        return count($this->listInvalidProperties()) === 0;
    }

    public function getAmount()
    {
        return $this->container['amount'];
    }

    public function setAmount($amount)
    {
        $this->container['amount'] = $amount;
        return $this;
    }

    public function getCurrency()
    {
        return $this->container['currency'];
    }

    public function setCurrency($currency)
    {
        $this->container['currency'] = $currency;
        return $this;
    }

    public function getDisplayAmount()
    {
        return $this->container['displayAmount'];
    }

    public function setDisplayAmount($displayAmount)
    {
        $this->container['displayAmount'] = $displayAmount;
        return $this;
    }

    public function offsetExists($offset)
    {
        return isset($this->container[$offset]);
    }

    public function offsetGet($offset)
    {
        return isset($this->container[$offset]) ? $this->container[$offset] : null;
    }

    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->container[] = $value;
        } else {
            $this->container[$offset] = $value;
        }
    }

    public function offsetUnset($offset)
    {
        unset($this->container[$offset]);
    }

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

