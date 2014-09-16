<?php

namespace SS6\ShopBundle\Model\Pricing;

use SS6\ShopBundle\Model\Setting\Setting;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

class InputPriceFacade {

	/**
	 * @var \SS6\ShopBundle\Model\Pricing\InputPriceRepository
	 */
	private $inputPriceRepository;

	/**
	 * @var \SS6\ShopBundle\Model\Setting\Setting
	 */
	private $setting;

	/**
	 * @var boolean
	 */
	private $recalculateInputPricesWithoutVat;

	/**
	 * @var boolean
	 */
	private $recalculateInputPricesWithVat;

	/**
	 * @param \SS6\ShopBundle\Model\Pricing\InputPriceRepository $inputPriceRepository
	 */
	public function __construct(InputPriceRepository $inputPriceRepository, Setting $setting) {
		$this->inputPriceRepository = $inputPriceRepository;
		$this->setting = $setting;
	}

	public function scheduleSetInputPricesWithoutVat() {
		$this->recalculateInputPricesWithoutVat = true;
	}

	public function scheduleSetInputPricesWithVat() {
		$this->recalculateInputPricesWithVat = true;
	}

	/**
	 * @param \Symfony\Component\HttpKernel\Event\FilterResponseEvent $event
	 */
	public function onKernelResponse(FilterResponseEvent $event) {
		if ($this->recalculateInputPricesWithoutVat) {
			$this->setting->set(PricingSetting::INPUT_PRICE_TYPE, PricingSetting::INPUT_PRICE_TYPE_WITHOUT_VAT);
			$this->inputPriceRepository->recalculateToInputPricesWithoutVat();
		} elseif ($this->recalculateInputPricesWithVat) {
			$this->setting->set(PricingSetting::INPUT_PRICE_TYPE, PricingSetting::INPUT_PRICE_TYPE_WITH_VAT);
			$this->inputPriceRepository->recalculateToInputPricesWithVat();
		}
	}

}
