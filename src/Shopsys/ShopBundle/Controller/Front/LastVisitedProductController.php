<?php

namespace Shopsys\ShopBundle\Controller\Front;

use Shopsys\ShopBundle\Model\Product\LastVisitedProduct\LastVisitedProductFacade;

class LastVisitedProductController extends FrontBaseController
{
    /**
     * @var \Shopsys\ShopBundle\Model\Product\LastVisitedProduct\LastVisitedProductFacade
     */
    private $lastVisitedProductFacade;

    /**
     * @param \Shopsys\ShopBundle\Model\Product\LastVisitedProduct\LastVisitedProductFacade $lastVisitedProductFacade
     */
    public function __construct(LastVisitedProductFacade $lastVisitedProductFacade)
    {
        $this->lastVisitedProductFacade = $lastVisitedProductFacade;
    }

    public function boxAction()
    {
        $lastVisitedProducts = $this->lastVisitedProductFacade->getLastVisitedProducts();

        return $this->render('@ShopsysShop/Front/Content/LastVisitedProduct/box.html.twig', [
            'lastVisitedProducts' => $lastVisitedProducts,
        ]);
    }
}
