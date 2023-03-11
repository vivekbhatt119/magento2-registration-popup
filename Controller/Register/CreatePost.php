<?php
declare(strict_types=1);

namespace Vivek\RegistrationPopup\Controller\Register;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface as CustomerRepository;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Api\Data\RegionInterfaceFactory;
use Magento\Customer\Helper\Address;
use Magento\Customer\Model\Account\Redirect as AccountRedirect;
use Magento\Customer\Model\CustomerExtractor;
use Magento\Customer\Model\Metadata\FormFactory;
use Magento\Customer\Model\Registration;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Url as CustomerUrl;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\UrlFactory;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Store\Model\StoreManagerInterface;

class CreatePost extends \Magento\Customer\Controller\Account\CreatePost
{
    /**
     * @var JsonFactory
     */
    private JsonFactory $jsonFactory;
    /**
     * @var Validator|null
     */
    private ?Validator $formKeyValidator;
    private Json $json;

    public function __construct(
        Context $context,
        Session $customerSession,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        AccountManagementInterface $accountManagement,
        Address $addressHelper,
        UrlFactory $urlFactory,
        FormFactory $formFactory,
        SubscriberFactory $subscriberFactory,
        RegionInterfaceFactory $regionDataFactory,
        AddressInterfaceFactory $addressDataFactory,
        CustomerInterfaceFactory $customerDataFactory,
        CustomerUrl $customerUrl,
        Registration $registration,
        Escaper $escaper,
        CustomerExtractor $customerExtractor,
        DataObjectHelper $dataObjectHelper,
        AccountRedirect $accountRedirect,
        CustomerRepository $customerRepository,
        JsonFactory $jsonFactory,
        Json $json,
        Validator $formKeyValidator = null
    ) {
        parent::__construct(
            $context,
            $customerSession,
            $scopeConfig,
            $storeManager,
            $accountManagement,
            $addressHelper,
            $urlFactory,
            $formFactory,
            $subscriberFactory,
            $regionDataFactory,
            $addressDataFactory,
            $customerDataFactory,
            $customerUrl,
            $registration,
            $escaper,
            $customerExtractor,
            $dataObjectHelper,
            $accountRedirect,
            $customerRepository,
            $formKeyValidator
        );
        $this->jsonFactory = $jsonFactory;
        $this->formKeyValidator = $formKeyValidator ?: ObjectManager::getInstance()->get(Validator::class);
        $this->json = $json;
    }

    public function validateForCsrf(\Magento\Framework\App\RequestInterface $request): ?bool
    {
        return true;
    }

    public function execute()
    {
        $result = [
            'errors' => false
        ];
        $data = $this->json->unserialize($this->getRequest()->getContent());
        $this->getRequest()->setParams($data);

        $response = $this->jsonFactory->create();
        if ($this->session->isLoggedIn() || !$this->registration->isAllowed()) {
            $response->setData($result);
            return $response;
        }

        if (!$this->getRequest()->isPost()) {
            $result['errors'] = true;
            $result['message'] = __("invalid form key");
            $response->setData($result);
            return $response;
        }
        $this->session->regenerateId();
        try {
            $address = $this->extractAddress();
            $addresses = $address === null ? [] : [$address];
            $customer = $this->customerExtractor->extract('customer_account_create', $this->_request);
            $customer->setAddresses($addresses);
            $password = $this->getRequest()->getParam('password');
            $confirmation = $this->getRequest()->getParam('password_confirmation');
            $redirectUrl = $this->session->getBeforeAuthUrl();
            $this->checkPasswordConfirmation($password, $confirmation);

            $extensionAttributes = $customer->getExtensionAttributes();
            $extensionAttributes->setIsSubscribed($this->getRequest()->getParam('is_subscribed', false));
            $customer->setExtensionAttributes($extensionAttributes);

            $customer = $this->accountManagement
                ->createAccount($customer, $password, $redirectUrl);

            $this->_eventManager->dispatch(
                'customer_register_success',
                ['account_controller' => $this, 'customer' => $customer]
            );
            $confirmationStatus = $this->accountManagement->getConfirmationStatus($customer->getId());
            if ($confirmationStatus === AccountManagementInterface::ACCOUNT_CONFIRMATION_REQUIRED) {
                $this->messageManager->addComplexSuccessMessage(
                    'confirmAccountSuccessMessage',
                    [
                        'url' => $this->customerUrl->getEmailConfirmationUrl($customer->getEmail()),
                    ]
                );
                $url = $this->urlModel->getUrl('*/*/index', ['_secure' => true]);
                $result['errors'] = true;
                $result['message'] = $this->messageManager->getMessages();
                $result['redirectUrl'] = $url;
                $response->setData($result);
                return $response;
            } else {
                $this->session->setCustomerDataAsLoggedIn($customer);
            }

            $response->setData($result);
            return $response;
        } catch (StateException $e) {
            $result['message'] = __(
                'There is already an account with this email address. If you are sure that it is your email address, <a href="%1">click here</a> to get your password and access your account.',
                $this->urlModel->getUrl('customer/account/forgotpassword')
            );
        } catch (InputException $e) {
            $result['message'] = __($e->getMessage());
        } catch (LocalizedException $e) {
            $result['message'] = __($e->getMessage());
        } catch (\Exception $e) {
            $result['message'] = __($e->getMessage());
        }

        $result['errors'] = true;
        $response->setData($result);
        return $response;
    }
}


