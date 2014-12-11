<?php

namespace SS6\ShopBundle\Model\Pricing\Currency;

use SS6\ShopBundle\Model\Domain\Domain;
use SS6\ShopBundle\Model\Pricing\Currency\Currency;
use SS6\ShopBundle\Model\Pricing\Currency\CurrencyData;
use SS6\ShopBundle\Model\Pricing\PricingSetting;

class CurrencyService {

	/**
	 * @var \SS6\ShopBundle\Model\Pricing\PricingSetting
	 */
	private $pricingSetting;

	/**
	 * @var \SS6\ShopBundle\Model\Domain\Domain
	 */
	private $domain;

	public function __construct(PricingSetting $pricingSetting, Domain $domain) {
		$this->pricingSetting = $pricingSetting;
		$this->domain = $domain;
	}

	/**
	 * @param \SS6\ShopBundle\Model\Pricing\Currency\CurrencyData $currencyData
	 * @return \SS6\ShopBundle\Model\Pricing\Currency\Currency
	 */
	public function create(CurrencyData $currencyData) {
		return new Currency($currencyData);
	}

	/**
	 * @param \SS6\ShopBundle\Model\Pricing\Currency\Currency $currency
	 * @param \SS6\ShopBundle\Model\Pricing\Currency\CurrencyData $currencyData
	 * @param bool $isDefaultCurrency
	 * @return \SS6\ShopBundle\Model\Pricing\Currency\Currency
	 */
	public function edit(Currency $currency, CurrencyData $currencyData, $isDefaultCurrency) {
		if ($isDefaultCurrency) {
			$currencyData->setExchangeRate(Currency::DEFAULT_EXCHANGE_RATE);
		}
		$currency->edit($currencyData);

		return $currency;
	}

	/**
	 * @param \SS6\ShopBundle\Model\Pricing\Currency\Currency $currency
	 */
	public function setDefaultCurrency(Currency $currency) {
		$this->pricingSetting->setDefaultCurrency($currency);
		$currencyData = new CurrencyData();
		$currencyData->setFromEntity($currency);
		$this->edit($currency, $currencyData, true);
	}

	/**
	 * @return array
	 */
	public function getNotAllowedToDeleteCurrencyIds() {
		$notAllowedToDeleteCurrencyIds = array();
		$notAllowedToDeleteCurrencyIds[] = $this->pricingSetting->getDefaultCurrencyId();
		foreach ($this->domain->getAll() as $domainConfig) {
			$notAllowedToDeleteCurrencyIds[] = $this->pricingSetting->getDomainDefaultCurrencyIdByDomainId($domainConfig->getId());
		}

		return $notAllowedToDeleteCurrencyIds;
	}

	/**
	 * @param \SS6\ShopBundle\Model\Pricing\Currency\Currency $currency
	 * @return bool
	 */
	public function isCurrencyNotAllowedToDelete(Currency $currency) {
		return in_array($currency->getId(), $this->getNotAllowedToDeleteCurrencyIds());
	}

}
