<?php

namespace SS6\ShopBundle\Tests\Unit\Model\Product\Availability;

use Doctrine\ORM\EntityManager;
use PHPUnit_Framework_TestCase;
use SS6\ShopBundle\Model\Product\Availability\Availability;
use SS6\ShopBundle\Model\Product\Availability\AvailabilityData;
use SS6\ShopBundle\Model\Product\Availability\ProductAvailabilityCalculation;
use SS6\ShopBundle\Model\Product\Availability\ProductAvailabilityRecalculationScheduler;
use SS6\ShopBundle\Model\Product\Availability\ProductAvailabilityRecalculator;
use SS6\ShopBundle\Model\Product\Product;

class ProductAvailabilityRecalculatorTest extends PHPUnit_Framework_TestCase {

	public function testRunImmediatelyRecalculations() {
		$productMock = $this->getMock(Product::class, null, [], '', false);

		$emMock = $this->getMock(EntityManager::class, ['clear', 'flush'], [], '', false);
		$productAvailabilityCalculationMock = $this->getMock(
			ProductAvailabilityCalculation::class,
			['getCalculatedAvailability'],
			[],
			'',
			false
		);
		$productAvailabilityCalculationMock
			->expects($this->once())
			->method('getCalculatedAvailability')
			->willReturn(new Availability(new AvailabilityData([])));
		$productAvailabilityRecalculationSchedulerMock = $this->getMock(
			ProductAvailabilityRecalculationScheduler::class,
			null,
			[],
			'',
			false
		);
		$productAvailabilityRecalculationSchedulerMock->scheduleRecalculateAvailabilityForProduct($productMock);

		$productAvailabilityRecalculator = new ProductAvailabilityRecalculator(
			$emMock,
			$productAvailabilityRecalculationSchedulerMock,
			$productAvailabilityCalculationMock
		);

		$productAvailabilityRecalculator->runImmediateRecalculations();
	}

	public function testRunScheduledRecalculationsWhile() {
		$calculationLimit = 3;
		$productMock = $this->getMock(Product::class, null, [], '', false);
		$productIterator = [
			[$productMock],
			[$productMock],
			[$productMock],
			[$productMock],
		];

		$emMock = $this->getMock(EntityManager::class, ['clear', 'flush'], [], '', false);
		$productAvailabilityCalculationMock = $this->getMock(
			ProductAvailabilityCalculation::class,
			['getCalculatedAvailability'],
			[],
			'',
			false
		);
		$productAvailabilityCalculationMock
			->expects($this->exactly($calculationLimit))
			->method('getCalculatedAvailability')
			->willReturn(new Availability(new AvailabilityData([])));
		$productAvailabilityRecalculationSchedulerMock = $this->getMock(
			ProductAvailabilityRecalculationScheduler::class,
			['getProductsIteratorForRecalculation'],
			[],
			'',
			false
		);
		$productAvailabilityRecalculationSchedulerMock
			->expects($this->once())
			->method('getProductsIteratorForRecalculation')
			->willReturn($productIterator);

		$productAvailabilityRecalculator = new ProductAvailabilityRecalculator(
			$emMock,
			$productAvailabilityRecalculationSchedulerMock,
			$productAvailabilityCalculationMock
		);

		$calculationCallbackLimit = $calculationLimit;
		$recalculatedCount = $productAvailabilityRecalculator->runScheduledRecalculationsWhile(
			function () use (&$calculationCallbackLimit) {
				return $calculationCallbackLimit-- > 0;
			}
		);

		$this->assertSame($calculationLimit, $recalculatedCount);
	}

	public function testRecalculateAvailabilityForVariant() {
		$variantMock = $this->getMock(Product::class, ['isVariant', 'getMainVariant', 'setCalculatedAvailability'], [], '', false);
		$mainVariantMock = $this->getMock(Product::class, ['setCalculatedAvailability'], [], '', false);
		$variantMock
			->expects($this->once())
			->method('isVariant')
			->willReturn(true);
		$variantMock
			->expects($this->once())
			->method('getMainVariant')
			->willReturn($mainVariantMock);
		$mainVariantMock
			->expects($this->once())
			->method('setCalculatedAvailability');

		$emMock = $this->getMock(EntityManager::class, ['flush'], [], '', false);
		$productAvailabilityRecalculationSchedulerMock = $this->getMock(
			ProductAvailabilityRecalculationScheduler::class,
			['getProductsForImmediatelyRecalculation'],
			[],
			'',
			false
		);
		$productAvailabilityRecalculationSchedulerMock
			->expects($this->once())
			->method('getProductsForImmediatelyRecalculation')
			->willReturn([$variantMock]);
		$productAvailabilityCalculationMock = $this->getMock(
			ProductAvailabilityCalculation::class,
			['getCalculatedAvailability'],
			[],
			'',
			false
		);
		$productAvailabilityCalculationMock
			->expects($this->exactly(2))
			->method('getCalculatedAvailability')
			->willReturn(new Availability(new AvailabilityData([])));

		$productAvailabilityRecalculator = new ProductAvailabilityRecalculator(
			$emMock,
			$productAvailabilityRecalculationSchedulerMock,
			$productAvailabilityCalculationMock
		);

		$productAvailabilityRecalculator->runImmediateRecalculations();
	}
}
