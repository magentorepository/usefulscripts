<?php

/**
 * Generate meta data csv
 *
 * Want to update meta data after product created?
 * Is meta data in a common format?
 *
 * Create csv file using this script and import it using magento default import.
 *
 * @param $_newMetaTitle [to update meta title]
 * @param $_newMetaDescription [to update meta description]
 *
 * @author karan.popat@krishtechnolabs.com
 */

/* display errors */
error_reporting(E_ALL | E_STRICT);
ini_set('display_errors', 'On');

/* import bootstrap class */
use Magento\Framework\App\Bootstrap;

require __DIR__ . '/app/bootstrap.php';
$bootstrap = Bootstrap::create(BP, $_SERVER);

/* load objectmanager */
$objectManager = $bootstrap->getObjectManager();

/* set area frontend / adminhtml */
$state = $objectManager->get('Magento\Framework\App\State');
$state->setAreaCode('frontend');

/* get storemanager object */
$storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');

/* get current website id*/
$websiteId = $storeManager->getWebsite()->getWebsiteId();
$storeId   = $storeManager->getStore()->getId();

/**
 * get all products from store without any filers
 * select required attributes
 *
 * add filters like enable/disable, stock availibility, etc if required
 */
$productCollection = $objectManager->create('Magento\Catalog\Model\ResourceModel\Product\Collection');
$productCollection->addAttributeToSelect('name');
$productCollection->addAttributeToSelect('description');
$productCollection->addAttributeToSelect('meta_title');
$productCollection->load();

/* get categoryFactory object to load category */
$_categoryFactory = $objectManager->create('Magento\Catalog\Model\CategoryFactory');

/* set header fields for csv*/
$_fields['0'] = array('sku', 'meta_title', 'meta_description');
$i            = 1;

foreach ($productCollection as $product) {
    $_metaTitle   = $product->getMetaTitle();
    $_description = $product->getDescription();
    $_productName = $product->getName();

    $_categories = $product->getCategoryIds();
    if (count($_categories)) {
        $_lastCategoryId  = end($_categories);
        $_category        = $_categoryFactory->create()->load($_lastCategoryId);
        $_productCategory = $_category->getName();
    } else {
        $_productCategory = null;
    }

    $count              = 255;
    $_newMetaTitle      = "New meta title for product with product name " . $_productName;
    $newMetaDescription = "Mew meta description along with product name" . $_productName . "  and last traversed category name" . $_productCategory . " with actual product description." . strip_tags($_description);

    /* meta description length must not exeed 255 characters */
    $_newMetaDescription = (strlen($newMetaDescription) > $count) ? substr($newMetaDescription, 0, ($count - 3)) . '...' : $newMetaDescription;

    /* write data fields for csv*/
    $_fields[$i] = array($product->getSku(), $_newMetaTitle, $_newMetaDescription);
    $i++;
}

/* check data */
echo "<pre>";print_r($_fields);die;

/* write csv */
$fp = fopen('upload_metadata.csv', 'w');

foreach ($_fields as $_field) {
    fputcsv($fp, $_field);
}

fclose($fp);

die('done');
