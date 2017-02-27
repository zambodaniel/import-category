<?php

/**
 * TechDivision\Import\Category\Observers\UrlRewriteObserver
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
 * Observer that creates/updates the category's URL rewrites.
 *
 * @author    Tim Wagner <t.wagner@techdivision.com>
 * @copyright 2016 TechDivision GmbH <info@techdivision.com>
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link      https://github.com/techdivision/import-category
 * @link      http://www.techdivision.com
 */
class UrlRewriteObserver extends AbstractCategoryImportObserver
{

    /**
     * The entity type to load the URL rewrites for.
     *
     * @var string
     */
    const ENTITY_TYPE = 'category';

    /**
     * Process the observer's business logic.
     *
     * @return void
     */
    protected function process()
    {

        // query whether or not, we've found a new path => means we've found a new category
        if ($this->hasBeenProcessed($this->getValue(ColumnKeys::PATH))) {
            return;
        }

        // try to load the URL key, return immediately if not possible
        if (!$this->hasValue(ColumnKeys::URL_PATH)) {
            return;
        }

        // initialize the store view code
        $this->prepareStoreViewCode();

        // initialize and persist the URL rewrite
        if ($urlRewrite = $this->initializeUrlRewrite($this->prepareAttributes())) {
            $this->persistUrlRewrite($urlRewrite);
        }
    }

    /**
     * Initialize the category product with the passed attributes and returns an instance.
     *
     * @param array $attr The category product attributes
     *
     * @return array The initialized category product
     */
    protected function initializeUrlRewrite(array $attr)
    {
        return $attr;
    }

    /**
     * Prepare the attributes of the entity that has to be persisted.
     *
     * @return array The prepared attributes
     */
    protected function prepareAttributes()
    {

        // load the store ID to use
        $storeId = $this->getRowStoreId();

        // initialize the values
        $entityId = $this->getLastEntityId();
        $requestPath = $this->prepareRequestPath();
        $targetPath = $this->prepareTargetPath();

        // return the prepared URL rewrite
        return $this->initializeEntity(
            array(
                MemberNames::ENTITY_TYPE      => UrlRewriteObserver::ENTITY_TYPE,
                MemberNames::ENTITY_ID        => $entityId,
                MemberNames::REQUEST_PATH     => $requestPath,
                MemberNames::TARGET_PATH      => $targetPath,
                MemberNames::REDIRECT_TYPE    => 0,
                MemberNames::STORE_ID         => $storeId,
                MemberNames::DESCRIPTION      => null,
                MemberNames::IS_AUTOGENERATED => 1,
                MemberNames::METADATA         => null
            )
        );
    }

    /**
     * Prepare's the target path for a URL rewrite.
     *
     * @return string The target path
     */
    protected function prepareTargetPath()
    {
        return sprintf('catalog/category/view/id/%d', $this->getPrimaryKey());
    }

    /**
     * Prepare's the request path for a URL rewrite or the target path for a 301 redirect.
     *
     * @return string The request path
     */
    protected function prepareRequestPath()
    {
        return sprintf('%s.html', $this->getValue(ColumnKeys::URL_PATH));
    }

    /**
     * Return's the store ID of the actual row, or of the default store
     * if no store view code is set in the CSV file.
     *
     * @param string|null $default The default store view code to use, if no store view code is set in the CSV file
     *
     * @return integer The ID of the actual store
     * @throws \Exception Is thrown, if the store with the actual code is not available
     */
    protected function getRowStoreId($default = null)
    {
        return $this->getSubject()->getRowStoreId($default);
    }

    /**
     * Persist's the URL rewrite with the passed data.
     *
     * @param array $row The URL rewrite to persist
     *
     * @return string The ID of the persisted entity
     */
    protected function persistUrlRewrite($row)
    {
        return $this->getSubject()->persistUrlRewrite($row);
    }

    /**
     * Return's the PK to create the product => attribute relation.
     *
     * @return integer The PK to create the relation with
     */
    protected function getPrimaryKey()
    {
        return $this->getLastEntityId();
    }
}
