<?php

/**
 * TechDivision\Import\Category\Observers\CategoryObserver
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

use TechDivision\Import\Category\Utils\ColumnKeys;
use TechDivision\Import\Category\Utils\MemberNames;

/**
 * Observer that create's the category itself.
 *
 * @author    Tim Wagner <t.wagner@techdivision.com>
 * @copyright 2016 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/techdivision/import-category
 * @link      http://www.techdivision.com
 */
class CategoryObserver extends AbstractCategoryImportObserver
{

    /**
     * The artefact type.
     *
     * @var string
     */
    const ARTEFACT_TYPE = 'category-path';

    /**
     * The array with the parent category IDs.
     *
     * @var array
     */
    protected $categoryIds = array();

    /**
     * Process the observer's business logic.
     *
     * @return void
     */
    protected function process()
    {

        // query whether or not, we've found a new path => means we've found a new category
        if ($this->hasBeenProcessed($path = $this->getValue(ColumnKeys::PATH))) {
            return;
        }

        // explode the path into the category names
        if ($categories = $this->explode($path, '/')) {
            // initialize the artefacts and reset the category IDs
            $artefacts = array();
            $this->categoryIds = array();

            // iterate over the category names and try to load the categegory therefore
            for ($i = sizeof($categories); $i > 0; $i--) {
                try {
                    // prepare the expected category name
                    $categoryPath = implode('/', array_slice($categories, 0, $i));
                    // load the existing category and prepend the ID the array with the category IDs
                    $existingCategory = $this->getCategoryByPath($categoryPath);
                    array_unshift($this->categoryIds, $existingCategory[MemberNames::ENTITY_ID]);

                } catch (\Exception $e) {
                    $this->getSystemLogger()->debug(sprintf('Can\'t load category %s, create a new one', $categoryPath));
                }
            }

            // prepare the static entity values, insert the entity and set the entity ID
            $category = $this->initializeCategory($this->prepareAttributes());
            $this->setLastEntityId($entityId = $this->persistCategory($category));

            //update the persisted category with the entity ID
            $category[MemberNames::ENTITY_ID] = $entityId;
            $category[MemberNames::URL_KEY] = $this->getValue(ColumnKeys::URL_KEY);

            // append the category to the list
            $this->addCategory($path, $category);

            // append the ID of the new category to array with the IDs
            array_push($this->categoryIds, $entityId);

            // prepare the artefact
            $artefact = array(
                MemberNames::ENTITY_ID => $entityId,
                MemberNames::PATH      => implode('/', $this->categoryIds)
            );

            // put the artefact on the stack
            $artefacts[] = $artefact;

            // add the artefacts
            $this->addArtefacts($artefacts);
        }
    }

    /**
     * Prepare the attributes of the entity that has to be persisted.
     *
     * @return array The prepared attributes
     */
    protected function prepareAttributes()
    {

        // prepare the date format for the created at/updated at dates
        $createdAt = $this->getValue(ColumnKeys::CREATED_AT, date('Y-m-d H:i:s'), array($this, 'formatDate'));
        $updatedAt = $this->getValue(ColumnKeys::UPDATED_AT, date('Y-m-d H:i:s'), array($this, 'formatDate'));

        // load the product's attribute set ID
        $attributeSet = $this->getAttributeSetByAttributeSetName($this->getValue(ColumnKeys::ATTRIBUTE_SET_CODE));
        $attributeSetId = $attributeSet[MemberNames::ATTRIBUTE_SET_ID];
        $this->setAttributeSet($attributeSet);

        // prepend the ID of the Root Catalog to the category IDs
        array_unshift($this->categoryIds, 1);

        // initialize parent ID, category level + path
        $parentId = end($this->categoryIds);
        $level = sizeof($this->categoryIds);

        // load the position, if available
        $position = $this->getValue(ColumnKeys::POSITION, 0);

        // return the prepared product
        return $this->initializeEntity(
            array(
                MemberNames::CREATED_AT       => $createdAt,
                MemberNames::UPDATED_AT       => $updatedAt,
                MemberNames::ATTRIBUTE_SET_ID => $attributeSetId,
                MemberNames::PATH             => '',
                MemberNames::PARENT_ID        => $parentId,
                MemberNames::POSITION         => $position,
                MemberNames::LEVEL            => $level,
                MemberNames::CHILDREN_COUNT   => 0
            )
        );
    }

    /**
     * Initialize the category with the passed attributes and returns an instance.
     *
     * @param array $attr The category attributes
     *
     * @return array The initialized category
     */
    protected function initializeCategory(array $attr)
    {
        return $attr;
    }

    /**
     * Add's the passed category to the internal list.
     *
     * @param string $path     The path of the category to add
     * @param array  $category The category to add
     *
     * @return void
     */
    protected function addCategory($path, $category)
    {
        $this->getSubject()->addCategory($path, $category);
    }

    /**
     * Return's the category with the passed path.
     *
     * @param string $path The path of the category to return
     *
     * @return array The category
     */
    protected function getCategoryByPath($path)
    {
        return $this->getSubject()->getCategoryByPath($path);
    }

    /**
     * Persist's the passed category data and return's the ID.
     *
     * @param array $category The category data to persist
     *
     * @return string The ID of the persisted entity
     */
    protected function persistCategory($category)
    {
        return $this->getSubject()->persistCategory($category);
    }

    /**
     * Set's the attribute set of the product that has to be created.
     *
     * @param array $attributeSet The attribute set
     *
     * @return void
     */
    protected function setAttributeSet(array $attributeSet)
    {
        $this->getSubject()->setAttributeSet($attributeSet);
    }

    /**
     * Return's the attribute set of the product that has to be created.
     *
     * @return array The attribute set
     */
    protected function getAttributeSet()
    {
        $this->getSubject()->getAttributeSet();
    }

    /**
     * Return's the attribute set with the passed attribute set name.
     *
     * @param string $attributeSetName The name of the requested attribute set
     *
     * @return array The attribute set data
     */
    protected function getAttributeSetByAttributeSetName($attributeSetName)
    {
        return $this->getSubject()->getAttributeSetByAttributeSetName($attributeSetName);
    }

    /**
     * Set's the ID of the product that has been created recently.
     *
     * @param string $lastEntityId The entity ID
     *
     * @return void
     */
    protected function setLastEntityId($lastEntityId)
    {
        $this->getSubject()->setLastEntityId($lastEntityId);
    }

    /**
     * Add the passed category artefacts to the category with the
     * last entity ID.
     *
     * @param array $artefacts The category artefacts
     *
     * @return void
     * @uses \TechDivision\Import\Category\BunchSubject::getLastEntityId()
     */
    protected function addArtefacts(array $artefacts)
    {
        $this->getSubject()->addArtefacts(CategoryObserver::ARTEFACT_TYPE, $artefacts);
    }
}
