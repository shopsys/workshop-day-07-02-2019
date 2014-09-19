<?php

namespace SS6\ShopBundle\Model\PKGrid;

use SS6\ShopBundle\Model\PKGrid\DataSourceInterface;
use SS6\ShopBundle\Model\PKGrid\PKGrid;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Router;
use Twig_Environment;

class PKGridFactory {

	/**
	 * @var \Symfony\Component\HttpFoundation\RequestStack
	 */
	private $requestStack;

	/**
	 * @var \Symfony\Component\Routing\Router
	 */
	private $router;

	/**
	 * @var \Twig_Environment
	 */
	private $twig;

	/**
	 * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
	 * @param \Symfony\Component\Routing\Router $router
	 * @param \Twig_Environment $twig
	 */
	public function __construct(RequestStack $requestStack, Router $router, Twig_Environment $twig) {
		$this->requestStack = $requestStack;
		$this->router = $router;
		$this->twig = $twig;
	}

	/**
	 * @param string $gridId
	 * @param \SS6\ShopBundle\Model\PKGrid\DataSourceInterface $dataSource
	 * @return \SS6\ShopBundle\Model\PKGrid\PKGrid
	 */
	public function create($gridId, DataSourceInterface $dataSource) {
		return new PKGrid($gridId, $dataSource, $this->requestStack, $this->router, $this->twig);
	}
}