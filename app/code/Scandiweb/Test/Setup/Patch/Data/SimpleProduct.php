<?php

namespace Scandiweb\Test\Setup\Patch\Data;

use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Eav\Setup\EavSetup;
use Magento\Framework\App\State;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class SimpleProduct
 * @package Scandiweb\Test\Setup\Patch\Data
 */
class SimpleProduct implements DataPatchInterface
{
    /**
     * @var ProductInterfaceFactory
     */
    protected ProductInterfaceFactory $productFactory;

    /**
     * @var ProductRepositoryInterface
     */
    protected ProductRepositoryInterface $productRepository;

    /**
     * @var CategoryLinkManagementInterface
     */
    protected CategoryLinkManagementInterface $categoryLinkManagement;

    /**
     * @var StoreManagerInterface
     */
    protected StoreManagerInterface $storeManager;

    /**
     * @var State
     */
    protected State $appState;

    /**
     * @var EavSetup
     */
    protected EavSetup $eavSetup;

    /**
     * SimpleProduct constructor.
     *
     * @param ProductInterfaceFactory $productFactory
     * @param ProductRepositoryInterface $productRepository
     * @param CategoryLinkManagementInterface $categoryLinkManagement
     * @param StoreManagerInterface $storeManager
     * @param State $appState
     * @param EavSetup $eavSetup
     */
    public function __construct(
        ProductInterfaceFactory $productFactory,
        ProductRepositoryInterface $productRepository,
        CategoryLinkManagementInterface $categoryLinkManagement,
        StoreManagerInterface $storeManager,
        State $appState,
        EavSetup $eavSetup
    ) {
        $this->productFactory = $productFactory;
        $this->productRepository = $productRepository;
        $this->categoryLinkManagement = $categoryLinkManagement;
        $this->storeManager = $storeManager;
        $this->appState = $appState;
        $this->eavSetup = $eavSetup;
    }

    /**
     * Apply data patch to create a simple product.
     *
     * @return void
     */
    public function apply(): void
    {
        $this->appState->emulateAreaCode('adminhtml', [$this, 'execute']);
    }

    /**
     * Execute the product creation logic.
     *
     * @return void
     */
    public function execute(): void
    {
        string $sku = 'simple-product-sku';

        string $name = 'simple-product-name';

        // Check if the product already exists
        if ($product->getIdBySku($sku)) {
            return;
        }

        $product = $this->productFactory->create();

        // Get the attribute set id from EavSetup object
        $attributeSetId = $this->eavSetup->getAttributeSetId(Product::ENTITY, 'Default');

        // Set attributes
        $product->setTypeId(Type::TYPE_SIMPLE)
            ->setAttributeSetId($attributeSetId)
            ->setName($name)
            ->setSku($sku)
            ->setUrlKey('Simple Product')
            ->setPrice(9.99)
            ->setVisibility(Visibility::VISIBILITY_BOTH)
            ->setStatus(Status::STATUS_ENABLED)
            ->setWebsiteIds([$this->storeManager->getStore()->getWebsiteId()])
            ->setStockData([
                'use_config_manage_stock' => 1,
                'qty' => 100,
                'is_qty_decimal' => 0,
                'is_in_stock' => 1,
            ]);
        
        $this->productRepository->save($product);

        $sourceItem = $this->sourceItemFactory->create();

        $sourceItem->setSourceCode('default');

        $sourceItem->setQuantity(100);

        $sourceItem->setSku($product->getSku());

        $sourceItem->setStatus(SourceItemInterface::STATUS_IN_STOCK);

        $this->sourceItems[] = $sourceItem;

        $this->sourceItemsSaveInterface->execute($this->sourceItems);
        

        $categoryIds = [2]; 

        $this->categoryLinkManagement->assignProductToCategories($product->getSku(), $categoryIds);
    }

    /**
     * Get dependencies for the data patch.
     *
     * @return array
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * Get aliases for the data patch.
     *
     * @return array
     */
    public function getAliases(): array
    {
        return [];
    }
}