<?php
declare(strict_types=1);

namespace Vivek\RegistrationPopup\Controller\Register;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Framework\App\Action\Action
{
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\RawFactory $resultFactory
    ) {
        parent::__construct($context);
        $this->resultFactory = $resultFactory;
    }

    /**
     *
     * @return ResultInterface
     */
    public function execute()
    {
        $this->_view->loadLayout();

        /** @var \Magento\Customer\Block\Form\Register $registerForm */
        $registerForm = $this->_view->getLayout()->getBlock('customer_form_register');
        $registerForm->setTemplate("Vivek_RegistrationPopup::register.phtml");
        $layout = $this->_view->getLayout();

        $result = $this->resultFactory->create();
//        $result->setContents($layout->renderElement("content"));
        $result->setContents($registerForm->toHtml());
        return $result;
    }
}

