<?php

namespace Shopsys\ShopBundle\Model\Pricing\Vat;

use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade as BaseVatFacade;

class VatFacade extends BaseVatFacade
{
    /**
     * @param string $vatPercent
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat
     */
    public function getVatByPercent($vatPercent)
    {
        return $this->vatRepository->getVatByPercent($vatPercent);
    }
}
