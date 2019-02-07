<?php

namespace Shopsys\ShopBundle\Model\Product;

use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Exception\VatNotFoundException;
use Shopsys\FrameworkBundle\Model\Product\Brand\BrandFacade;
use Shopsys\Plugin\Cron\SimpleCronModuleInterface;
use Shopsys\ShopBundle\Model\Pricing\Vat\VatFacade;
use Symfony\Bridge\Monolog\Logger;

class ImportProductsCronModule implements SimpleCronModuleInterface
{
    const DATA_URL = 'https://bit.ly/2DoOIAc';
    const LOCALE = 'en';
    const DOMAIN_ID = 1;

    /**
     * @var \Symfony\Bridge\Monolog\Logger
     */
    protected $logger;

    /**
     * @var \Shopsys\ShopBundle\Model\Product\ProductFacade
     */
    private $productFacade;

    /**
     * @var \Shopsys\ShopBundle\Model\Product\ProductDataFactory
     */
    private $productDataFactory;

    /**
     * @var \Shopsys\ShopBundle\Model\Pricing\Vat\VatFacade
     */
    private $vatFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade
     */
    private $pricingGroupSettingFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Product\Brand\BrandFacade
     */
    private $brandFacade;

    /**
     * @param \Shopsys\ShopBundle\Model\Product\ProductFacade $productFacade
     * @param \Shopsys\ShopBundle\Model\Product\ProductDataFactory $productDataFactory
     * @param \Shopsys\ShopBundle\Model\Pricing\Vat\VatFacade $vatFacade
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade $pricingGroupSettingFacade
     */
    public function __construct(
        ProductFacade $productFacade,
        ProductDataFactory $productDataFactory,
        VatFacade $vatFacade,
        PricingGroupSettingFacade $pricingGroupSettingFacade,
        BrandFacade $brandFacade
    ) {
        $this->productFacade = $productFacade;
        $this->productDataFactory = $productDataFactory;
        $this->vatFacade = $vatFacade;
        $this->pricingGroupSettingFacade = $pricingGroupSettingFacade;
        $this->brandFacade = $brandFacade;
    }

    /**
     * @param \Symfony\Bridge\Monolog\Logger $logger
     */
    public function setLogger(Logger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * This method is called to run the CRON module.
     */
    public function run()
    {
        $this->logger->info('Downloading data...');
        $jsonData = file_get_contents(self::DATA_URL);
        $this->logger->info('Decoding data...');
        $decodedData = json_decode($jsonData, true);
        foreach ($decodedData as $externalProductData) {
            try {
                $extId = $externalProductData['id'];
                $product = $this->productFacade->findByExternalId($extId);
                if ($product === null) {
                    $productData = $this->productDataFactory->create();
                    $this->mapExternalDataToProductData($externalProductData, $productData);
                    $this->productFacade->create($productData);
                    $this->logger->info(sprintf('Product with ext ID %s created', $extId));
                } else {
                    $productData = $this->productDataFactory->createFromProduct($product);
                    $this->mapExternalDataToProductData($externalProductData, $productData);
                    $this->productFacade->edit($product->getId(), $productData);
                    $this->logger->info(sprintf('Product with ext ID %s edited', $extId));
                }
            } catch (VatNotFoundException $ex) {
                $this->logger->warning(sprintf('Skipping product with ext ID %s: %s', $extId, $ex->getMessage()));
            }
        }
    }

    /**
     * @param array $externalProductData
     * @param \Shopsys\ShopBundle\Model\Product\ProductData $productData
     */
    private function mapExternalDataToProductData(array $externalProductData, ProductData $productData)
    {
        $productData->extId = $externalProductData['id'];
        $productData->vat = $this->vatFacade->getVatByPercent($externalProductData['vat_percent']);
        $productData->name[self::LOCALE] = $externalProductData['name'];
        $productData->ean = $externalProductData['ean'];

        $defaultPricingGroup = $this->pricingGroupSettingFacade->getDefaultPricingGroupByDomainId(self::DOMAIN_ID);
        $productData->manualInputPricesByPricingGroupId[$defaultPricingGroup->getId()] = $externalProductData['price_without_vat'];

        $productData->descriptions[self::DOMAIN_ID] = $externalProductData['description'];

        $productData->stockQuantity = $externalProductData['stock_quantity'];
        $productData->usingStock = true;

        $productData->brand = $this->brandFacade->getById($externalProductData['brand_id']);
    }
}
