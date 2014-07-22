<?php

namespace SS6\ShopBundle\Model\Cart\Watcher;

use Doctrine\ORM\EntityManager;
use SS6\ShopBundle\Model\Cart\Cart;
use SS6\ShopBundle\Model\Cart\Watcher\CartWatcherService;
use SS6\ShopBundle\Model\FlashMessage\FlashMessage;

class CartWatcherFacade {
	
	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	private $em;
	
	/**
	 * @var \SS6\ShopBundle\Model\Cart\Watcher\CartWatcherService
	 */
	private $cartWatcherService;

	/**
	 * @var \SS6\ShopBundle\Model\FlashMessage\FlashMessage
	 */
	private $flashMessage;
	
	/**
	 * @param \SS6\ShopBundle\Model\FlashMessage\FlashMessage $flashMessage
	 * @param \Doctrine\ORM\EntityManager $em
	 * @param \SS6\ShopBundle\Model\Cart\CartService $cartWatcherService
	 * @param \SS6\ShopBundle\Model\Product\ProductRepository $productRepository
	 */
	public function __construct(FlashMessage $flashMessage, EntityManager $em, CartWatcherService $cartWatcherService) {
		$this->flashMessage = $flashMessage;
		$this->em = $em;
		$this->cartWatcherService = $cartWatcherService;
	}

	/**
	 * @param \SS6\ShopBundle\Model\Cart\Cart $cart
	 */
	public function checkCartModifications(Cart $cart) {
		$this->cartWatcherService->showErrorOnModifiedItems($cart);

		$notVisibleItems = $this->cartWatcherService->getNotVisibleItems($cart);
		foreach ($notVisibleItems as $cartItem) {
			$this->flashMessage->addError('Zboží ' . $cartItem->getName() .
				', které jste měl v košíku, již není v nabídce. Prosím, překontrolujte si objednávku.');
			$cart->removeItem($cartItem);
			$this->em->remove($cartItem);
		}

		$this->em->flush();
	}
}