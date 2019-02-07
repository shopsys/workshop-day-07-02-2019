<?php

namespace Shopsys\ShopBundle\Model\Pricing\Vat;

use Shopsys\FrameworkBundle\Model\Pricing\Vat\Exception\VatNotFoundException;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatRepository as BaseVatRepository;

class VatRepository extends BaseVatRepository
{
    /**
     * @param $vatPercent
     * @return \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat|null
     */
    public function getVatByPercent($vatPercent)
    {
        $vat = $this->getVatRepository()->findOneBy(['percent' => $vatPercent]);
        if ($vat === null) {
            throw new VatNotFoundException(sprintf('Vat not found by percent %s', $vatPercent));
        }

        return $vat;
    }
}
