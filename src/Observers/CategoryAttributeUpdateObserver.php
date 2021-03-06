<?php

/**
 * TechDivision\Import\Category\Observers\CategoryAttributeUpdateObserver
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * PHP version 5
 *
 * @author    Tim Wagner <t.wagner@techdivision.com>
 * @copyright 2016 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/techdivision/import-category
 * @link      http://www.techdivision.com
 */

namespace TechDivision\Import\Category\Observers;

use TechDivision\Import\Category\Utils\MemberNames;

/**
 * Observer that creates/updates the category's attributes.
 *
 * @author    Tim Wagner <t.wagner@techdivision.com>
 * @copyright 2016 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/techdivision/import-category
 * @link      http://www.techdivision.com
 */
class CategoryAttributeUpdateObserver extends CategoryAttributeObserver
{

    /**
     * Initialize the category product with the passed attributes and returns an instance.
     *
     * @param array $attr The category product attributes
     *
     * @return array The initialized category product
     */
    protected function initializeAttribute(array $attr)
    {

        // load the supported backend types
        $backendTypes = $this->getBackendTypes();

        // initialize the persist method for the found backend type
        list (, $loadMethod) = $backendTypes[$this->backendType];

        // load store/entity/attribute ID
        $storeId = $attr[MemberNames::STORE_ID];
        $entityId = $attr[MemberNames::ENTITY_ID];
        $attributeId = $attr[MemberNames::ATTRIBUTE_ID];

        // try to load the attribute with the passed entity/attribute/store ID
        // and merge it with the attributes
        if ($entity = $this->$loadMethod($entityId, $attributeId, $storeId)) {
            return $this->mergeEntity($entity, $attr);
        }

        // otherwise simply return the attributes
        return $attr;
    }

    /**
     * Load's and return's the datetime attribute with the passed entity/attribute/store ID.
     *
     * @param integer $entityId    The entity ID of the attribute
     * @param integer $attributeId The attribute ID of the attribute
     * @param integer $storeId     The store ID of the attribute
     *
     * @return array|null The datetime attribute
     */
    protected function loadDatetimeAttribute($entityId, $attributeId, $storeId)
    {
        return $this->getSubject()->loadCategoryDatetimeAttribute($entityId, $attributeId, $storeId);
    }

    /**
     * Load's and return's the decimal attribute with the passed entity/attribute/store ID.
     *
     * @param integer $entityId    The entity ID of the attribute
     * @param integer $attributeId The attribute ID of the attribute
     * @param integer $storeId     The store ID of the attribute
     *
     * @return array|null The decimal attribute
     */
    protected function loadDecimalAttribute($entityId, $attributeId, $storeId)
    {
        return $this->getSubject()->loadCategoryDecimalAttribute($entityId, $attributeId, $storeId);
    }

    /**
     * Load's and return's the integer attribute with the passed entity/attribute/store ID.
     *
     * @param integer $entityId    The entity ID of the attribute
     * @param integer $attributeId The attribute ID of the attribute
     * @param integer $storeId     The store ID of the attribute
     *
     * @return array|null The integer attribute
     */
    protected function loadIntAttribute($entityId, $attributeId, $storeId)
    {
        return $this->getSubject()->loadCategoryIntAttribute($entityId, $attributeId, $storeId);
    }

    /**
     * Load's and return's the text attribute with the passed entity/attribute/store ID.
     *
     * @param integer $entityId    The entity ID of the attribute
     * @param integer $attributeId The attribute ID of the attribute
     * @param integer $storeId     The store ID of the attribute
     *
     * @return array|null The text attribute
     */
    protected function loadTextAttribute($entityId, $attributeId, $storeId)
    {
        return $this->getSubject()->loadCategoryTextAttribute($entityId, $attributeId, $storeId);
    }

    /**
     * Load's and return's the varchar attribute with the passed entity/attribute/store ID.
     *
     * @param integer $entityId    The entity ID of the attribute
     * @param integer $attributeId The attribute ID of the attribute
     * @param integer $storeId     The store ID of the attribute
     *
     * @return array|null The varchar attribute
     */
    protected function loadVarcharAttribute($entityId, $attributeId, $storeId)
    {
        return $this->getSubject()->loadCategoryVarcharAttribute($entityId, $attributeId, $storeId);
    }
}
