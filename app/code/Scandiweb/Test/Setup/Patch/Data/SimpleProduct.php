<?php

namespace Scandiweb\Test\Setup\Patch\Data;

use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\State;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Store\Model\StoreManagerInterface;

class SimpleProduct implements DataPatchInterface
{
    private $productFactory;
    private $productRepository;
    private $categoryLinkManagement;
    private $storeManager;
    private $appState;

    public function __construct(
        ProductInterfaceFactory $productFactory,
        ProductRepositoryInterface $productRepository,
        CategoryLinkManagementInterface $categoryLinkManagement,
        StoreManagerInterface $storeManager,
        State $appState
    ) {
        $this->productFactory = $productFactory;
        $this->productRepository = $productRepository;
        $this->categoryLinkManagement = $categoryLinkManagement;
        $this->storeManager = $storeManager;
        $this->appState = $appState;
    }

    public function apply()
    {
        $this->appState->setAreaCode('adminhtml');

        $product = $this->productFactory->create();
        $product->setSku('simple-product-sku');
        $product->setName('Simple Product');
        $product->setAttributeSetId(4); 
        $product->setStatus(1);
        $product->setVisibility(4); 
        $product->setTypeId('simple');
        $product->setPrice(10.00);
        $product->setWebsiteIds([$this->storeManager->getStore()->getWebsiteId()]);
        $product->setStockData([
            'use_config_manage_stock' => 1,
            'qty' => 100,
            'is_qty_decimal' => 0,
            'is_in_stock' => 1,
        ]);

        $this->productRepository->save($product);

        $categoryIds = [2]; 
        $this->categoryLinkManagement->assignProductToCategories($product->getSku(), $categoryIds);
    }

    public static function getDependencies()
    {
        return [];
    }

    public function getAliases()
    {
        return [];
    }
}