<?php

declare(strict_types = 1);

namespace Shopsys\ShopBundle\Model\Product\LastVisitedProduct;

use Shopsys\ShopBundle\Model\Product\ProductOnCurrentDomainFacade;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

class LastVisitedProductFacade
{
    /**
     * @var \Symfony\Component\HttpFoundation\RequestStack
     */
    private $requestStack;

    /**
     * @var \Shopsys\ShopBundle\Model\Product\ProductOnCurrentDomainFacade
     */
    private $productOnCurrentDomainFacade;

    /**
     * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
     * @param \Shopsys\ShopBundle\Model\Product\ProductOnCurrentDomainFacade $productOnCurrentDomainFacade
     */
    public function __construct(
        RequestStack $requestStack,
        ProductOnCurrentDomainFacade $productOnCurrentDomainFacade
    ) {
        $this->requestStack = $requestStack;
        $this->productOnCurrentDomainFacade = $productOnCurrentDomainFacade;
    }

    /**
     * @param int $productId
     * @param \Symfony\Component\HttpFoundation\Response $response
     */
    public function updateLastVisitedProducts(int $productId, Response $response): void
    {
        $lastVisitedProductsIds = $this->getLastVisitedProductsIdsFromCookie();

        $indexOfProductIdIfAlreadyVisited = array_search($productId, $lastVisitedProductsIds, true);
        if ($indexOfProductIdIfAlreadyVisited !== false) {
            unset($lastVisitedProductsIds[$indexOfProductIdIfAlreadyVisited]);
        }

        array_unshift($lastVisitedProductsIds, $productId);

        $cookie = new Cookie(
            'lastVisitedProducts',
            implode(',', $lastVisitedProductsIds)
        );

        $response->headers->setCookie($cookie);
    }

    /**
     * @return array|\Shopsys\ShopBundle\Model\Product\Product[]
     */
    public function getLastVisitedProducts()
    {
        $lastVisitedProductsIds = $this->getLastVisitedProductsIdsFromCookie();
        return $this->productOnCurrentDomainFacade->getVisibleProductsByIds($lastVisitedProductsIds);
    }

    /**
     * @return array
     */
    private function getLastVisitedProductsIdsFromCookie(): array
    {
        $lastVisitedProductsIdsString = $this->requestStack->getMasterRequest()->cookies->get(
            'lastVisitedProducts',
            ''
        );

        if ($lastVisitedProductsIdsString !== '') {
            $lastVisitedProductsIds = explode(',', $lastVisitedProductsIdsString);
            $lastVisitedProductsIds = array_map('intval', $lastVisitedProductsIds);
        } else {
            $lastVisitedProductsIds = [];
        }

        return $lastVisitedProductsIds;
    }
}
