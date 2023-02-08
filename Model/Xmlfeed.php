<?php

namespace ModestoSolar\GoogleShopping\Model;

use ModestoSolar\GoogleShopping\Helper\Data;
use ModestoSolar\GoogleShopping\Helper\Products;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Bundle\Model\Product\Type;

class Xmlfeed
{
    const GROUPED_PRODUCT_ID = "grouped";
    protected Data $helper;
    protected Products $productFeedHelper;
    protected StoreManagerInterface $storeManager;
    private ProductRepositoryInterface $productRepository;

    public function __construct(
        Data $helper,
        Products $productFeedHelper,
        StoreManagerInterface $storeManager,
        ProductRepositoryInterface $productRepository
    ) {
        $this->helper = $helper;
        $this->productFeedHelper = $productFeedHelper;
        $this->storeManager = $storeManager;
        $this->productRepository = $productRepository;
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function getFeed(): string
    {
        $xml = $this->getXmlHeader();
        $xml .= $this->getProductsXml();
        $xml .= $this->getXmlFooter();

        return $xml;
    }

    public function getXmlHeader(): string
    {
        $xml = '<?xml version="1.0" encoding="utf-8" ?>';
        $xml .= '<rss version="2.0" xmlns:g="http://base.google.com/ns/1.0">';
        $xml .= '<channel>';
        $xml .= '<title>' . $this->helper->getConfig('google_default_title') . '</title>';
        $xml .= '<link>' . $this->helper->getConfig('google_default_url') . '</link>';
        $xml .= '<description>' . $this->helper->getConfig('google_default_description') . '</description>';

        return $xml;
    }

    public function getXmlFooter(): string
    {
        return '</channel></rss>';
    }

    public function getProductsXml(): string
    {
        $productCollection = $this->productFeedHelper->getAllIdsToFeed();
        $xml = "";

        foreach ($productCollection as $productId) {
            $xml .= "<item>" . $this->buildProductXml($productId) . "</item>";
        }

        return $xml;
    }

    public function buildProductXml($productId): string
    {
        $product = $this->productRepository->getById($productId);
        $description = $this->productFeedHelper->fixDescription($product->getDescription());
        $allImages = $product->getMediaGalleryEntries();
        array_shift($allImages);
        $mediaPath = $this->storeManager->getStore()
            ->getBaseUrl(UrlInterface::URL_TYPE_MEDIA, true) . 'catalog/product';

        $xml = $this->createNode("g:id", $product->getSku(), true);
        $xml .= $this->createNode("g:title", $product->getName(), true);
        $xml .= $this->createNode("g:description", $description, true);
        $xml .= $this->createNode("link", $product->getProductUrl(), true);

        if ($product->getImage()) {
            $xml .= $this->createNode("g:image_link", $mediaPath . $product->getImage(), true);
        }

        foreach ($allImages as $additionalImage) {
            $xml .= $this->createNode("g:additional_image_link", $mediaPath . $additionalImage->getFile(), true);
        }

        $xml .= $this->createNode("g:condition", 'new');
        $xml .= $this->createNode("g:availability", 'in stock');

        if ($product->getTypeId() === self::GROUPED_PRODUCT_ID) {
            $_children = $product->getTypeInstance()->getChildrenIds($product->getId());
            $allGroupedPrices = [];
            foreach ($_children as $children) {
                foreach ($children as $child) {
                    if ($child != $product->getId()) {
                        $allGroupedPrices[]  = $this->productRepository->getById($child)->getPrice();
                    }
                }
            }
            $minimalPrice = min($allGroupedPrices);
            $xml .= $this->createNode(
                'g:price',
                number_format(
                    $minimalPrice,
                    2,
                    '.',
                    ''
                ) . ' ' . $this->productFeedHelper->getCurrentCurrencySymbol()
            );
        } else {
            $xml .= $this->createNode('g:price', number_format($product
                ->getFinalPrice(), 2, '.', '')
                . ' ' . $this->productFeedHelper->getCurrentCurrencySymbol());
        }

        if (!(int)$product->getSpecialPrice() == 0) {
            if ((int)$product->getSpecialPrice() != $product->getFinalPrice()) {
                $xml .= $this->createNode('g:sale_price', number_format($product
                    ->getSpecialPrice(), 2, '.', '')
                    . ' ' . $this->productFeedHelper
                    ->getCurrentCurrencySymbol());
            }
        }

        $xml .= $this->createNode(
            'g:google_product_category',
            $this->productFeedHelper->getProductValue($product, 'google_product_category'),
            true
        );
        $xml .= $this->createNode("g:product_type", $this->productFeedHelper->getAttributeSet($product), true);
        $xml .= $this->createNode("g:brand", $this->getAttributeText($product, 'manufacturer'));
        $xml .= $this->createNode("g:gtin", $this->getAttributeText($product, 'gr_ean'));
        $xml .= $this->createNode("g:mpn", $product->getSku());

        return $xml;
    }

    protected function getAttributeText($product, $attributeCode)
    {
        if ($attribute = $product->getResource()->getAttribute($attributeCode)) {
            return $attribute->getSource()->getOptionText($product->getData($attributeCode));
        } else {
            return false;
        }
    }

    public function createNode(string $nodeName, $value, bool $cData = false)
    {
        if (empty($value) || empty($nodeName)) {
            return false;
        }

        $cDataStart = "";
        $cDataEnd = "";

        if ($cData === true) {
            $cDataStart = "<![CDATA[";
            $cDataEnd = "]]>";
        }

        return "<" . $nodeName . ">" . $cDataStart . $value . $cDataEnd . "</" . $nodeName . ">";
    }
}
