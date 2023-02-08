<?php

namespace ModestoSolar\GoogleShopping\Helper;

use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Eav\Model\AttributeSetRepository;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\StoreManagerInterface;

class Products extends AbstractHelper
{
    public Data $helper;
    public StoreManagerInterface $storeManager;
    private CategoryFactory $categoryFactory;
    private AttributeSetRepository $attributeSetRepository;
    private CollectionFactory $productCollectionFactory;

    public function __construct(
        Context                $context,
        CollectionFactory      $productCollectionFactory,
        AttributeSetRepository $attributeSetRepo,
        Data                   $helper,
        StoreManagerInterface  $storeManager,
        CategoryFactory        $categoryFactory
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->attributeSetRepository = $attributeSetRepo;
        $this->helper = $helper;
        $this->storeManager = $storeManager;
        $this->categoryFactory = $categoryFactory;
        parent::__construct($context);
    }

    public function getAttributeSet($product)
    {
        $attributeSetId = $product->getAttributeSetId();
        $attributeSet = $this->attributeSetRepository->get($attributeSetId);

        return $attributeSet->getAttributeSetName();
    }

    public function getProductValue($product, $attributeCode)
    {
        $attributeCodeFromConfig = $this->helper->getConfig($attributeCode . '_attribute');
        $defaultValue = $this->helper->getConfig('default_' . $attributeCode);

        if (!empty($attributeCodeFromConfig)) {
            return $product->getAttributeText($attributeCodeFromConfig);
        }

        if (!empty($defaultValue)) {
            return $defaultValue;
        }

        return false;
    }

    public function getCurrentCurrencySymbol(): string
    {
        return $this->storeManager->getStore()->getCurrentCurrencyCode();
    }

    public function fixDescription($data)
    {
        $description = $this->helper->removeHtml($data);
        $encode = mb_detect_encoding($data);
        return mb_convert_encoding($description, 'UTF-8', $encode);
    }

    public function getAllIdsToFeed(): array
    {
        $items = $this->getFilteredProducts();
        $allIds = [];

        foreach ($items as $item) {
            $allIds[] = $item->getId();
        }
        return $allIds;
    }

    private function getFilteredProducts(): Collection
    {
        $categoryId = $this->helper->getConfig('category_list');
        if ($categoryId) {
            return $this->getAllProductsInSelectedCategory($categoryId);
        } else {
            return $this->getAllProductsVisibleAndEnabled();
        }
    }

    private function getAllProductsVisibleAndEnabled(): Collection
    {
        $collection = $this->productCollectionFactory->create();
        $collection->addAttributeToFilter('visibility', Visibility::VISIBILITY_BOTH);
        $collection->addAttributeToFilter('status', Status::STATUS_ENABLED);
        return $collection;
    }

    private function getAllProductsInSelectedCategory($categoryId): Collection
    {
            $category = $this->categoryFactory->create()->load($categoryId);
            return $category->getProductCollection()->addAttributeToSelect('*');
    }
}
