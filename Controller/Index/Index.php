<?php
/**
 * CravenDunnill ProductTileCalculator Extension
 */
namespace CravenDunnill\ProductTileCalculator\Controller\Index;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\View\Result\PageFactory;

class Index implements HttpGetActionInterface
{
	/**
	 * @var PageFactory
	 */
	protected $resultPageFactory;

	/**
	 * Constructor
	 *
	 * @param PageFactory $resultPageFactory
	 */
	public function __construct(
		PageFactory $resultPageFactory
	) {
		$this->resultPageFactory = $resultPageFactory;
	}

	/**
	 * Execute action based on request and return result
	 *
	 * @return ResultInterface|ResponseInterface
	 * @throws NotFoundException
	 */
	public function execute()
	{
		return $this->resultPageFactory->create();
	}
}