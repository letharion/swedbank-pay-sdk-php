<?php
// phpcs:ignoreFile -- this is test

use SwedbankPay\Api\Service\Creditcard\Request\Purchase;
use SwedbankPay\Api\Service\Creditcard\Request\Verify;
use SwedbankPay\Api\Service\Creditcard\Resource\Request\PaymentPurchaseCreditcard;
use SwedbankPay\Api\Service\Creditcard\Resource\Request\PaymentPurchaseObject;
use SwedbankPay\Api\Service\Creditcard\Resource\Request\PaymentUrl;
use SwedbankPay\Api\Service\Creditcard\Resource\Request\PaymentVerifyCreditcard;
use SwedbankPay\Api\Service\Creditcard\Resource\Request\PaymentVerifyObject;
use SwedbankPay\Api\Service\Creditcard\Resource\Request\PaymentPurchase;
use SwedbankPay\Api\Service\Creditcard\Resource\Request\PaymentVerify;
use SwedbankPay\Api\Service\Payment\Resource\Collection\PricesCollection;
use SwedbankPay\Api\Service\Payment\Resource\Collection\Item\PriceItem;
use SwedbankPay\Api\Service\Creditcard\Resource\Request\PaymentPayeeInfo;

use SwedbankPay\Api\Service\Data\ResponseInterface as ResponseServiceInterface;
use SwedbankPay\Api\Service\Resource\Data\ResponseInterface as ResponseResourceInterface;
use SwedbankPay\Api\Service\Payment\Transaction\Resource\Request\TransactionObject;

use SwedbankPay\Api\Service\Creditcard\Transaction\Request\CreateCapture;
use SwedbankPay\Api\Service\Creditcard\Transaction\Request\CreateReversal;
use SwedbankPay\Api\Service\Creditcard\Transaction\Request\CreateCancellation;
use SwedbankPay\Api\Service\Creditcard\Transaction\Resource\Request\TransactionCapture;
use SwedbankPay\Api\Service\Creditcard\Transaction\Resource\Request\TransactionReversal;
use SwedbankPay\Api\Service\Creditcard\Transaction\Resource\Request\TransactionCancellation;

use SwedbankPay\Api\Service\Payment\Transaction\Resource\Response\AuthorizationObject;
use SwedbankPay\Api\Service\Payment\Transaction\Resource\Response\CaptureObject;
use SwedbankPay\Api\Service\Payment\Transaction\Resource\Response\ReversalObject;
use SwedbankPay\Api\Service\Payment\Transaction\Resource\Response\CancellationObject;
use SwedbankPay\Api\Service\Payment\Transaction\Resource\Response\TransactionObject as TransactionObjectResponse;

use SwedbankPay\Api\Service\Creditcard\Transaction\Request\GetAuthorizations;
use SwedbankPay\Api\Service\Creditcard\Transaction\Request\GetCaptures;
use SwedbankPay\Api\Service\Creditcard\Transaction\Request\GetReversals;
use SwedbankPay\Api\Service\Creditcard\Transaction\Request\GetCancellations;
use SwedbankPay\Api\Service\Creditcard\Transaction\Request\GetTransactions;

use SwedbankPay\Api\Service\Payment\Transaction\Resource\Response\AuthorizationsObject;
use SwedbankPay\Api\Service\Payment\Transaction\Resource\Response\CancellationsObject;
use SwedbankPay\Api\Service\Payment\Transaction\Resource\Response\CapturesObject;
use SwedbankPay\Api\Service\Payment\Transaction\Resource\Response\ReversalsObject;
use SwedbankPay\Api\Service\Payment\Transaction\Resource\Response\TransactionsObject;

use SwedbankPay\Api\Service\Creditcard\Transaction\Request\GetAuthorization;
use SwedbankPay\Api\Service\Creditcard\Transaction\Request\GetCancellation;
use SwedbankPay\Api\Service\Creditcard\Transaction\Request\GetCapture;
use SwedbankPay\Api\Service\Creditcard\Transaction\Request\GetReversal;
use SwedbankPay\Api\Service\Creditcard\Transaction\Request\GetTransaction;

class CardPaymentTest extends TestCase
{
    protected $paymentId = '/psp/creditcard/payments/c87ff72f-b336-44c0-04c3-08d850138a2d';

    /**
     * @throws \SwedbankPay\Api\Client\Exception
     */
    public function testPurchaseRequest()
    {
        $url = new PaymentUrl();
        $url->setCompleteUrl('http://test-dummy.net/payment-completed')
            ->setCancelUrl('http://test-dummy.net/payment-canceled')
            ->setPaymentUrl('https://example.com/perform-payment')
            ->setCallbackUrl('http://test-dummy.net/payment-callback')
            ->setLogoUrl('https://example.com/logo.png')
            ->setTermsOfService('https://example.com/terms.pdf')
            ->setHostUrls(['https://example.com', 'https://example.net']);

        $payeeInfo = new PaymentPayeeInfo();
        $payeeInfo->setPayeeId(PAYEE_ID)
            ->setPayeeReference($this->generateRandomString(30))
            ->setPayeeName('Merchant1')
            ->setProductCategory('A123')
            ->setOrderReference('or-123456')
            ->setSubsite('MySubsite');

        $price = new PriceItem();
        $price->setType('Creditcard')
            ->setAmount(1500)
            ->setVatAmount(0);

        $prices = new PricesCollection();
        $prices->addItem($price);

        $creditCard = new PaymentPurchaseCreditcard();
        $creditCard->setNo3DSecure(false)
            ->setMailOrderTelephoneOrder(false)
            ->setRejectCardNot3DSecureEnrolled(false)
            ->setRejectCreditCards(false)
            ->setRejectDebitCards(false)
            ->setRejectConsumerCards(false)
            ->setRejectCorporateCards(false)
            ->setRejectAuthenticationStatusA(false)
            ->setRejectAuthenticationStatusU(false);

        $payment = new PaymentPurchase();
        $payment->setOperation('Purchase')
            ->setIntent('Authorization')
            ->setCurrency('SEK')
            ->setPaymentToken('')
            ->setGeneratePaymentToken(true)
            ->setDescription('Test Purchase')
            ->setUserAgent('Mozilla/5.0...')
            ->setLanguage('sv-SE')
            ->setPayerReference($this->generateRandomString(30))
            ->setUrls($url)
            ->setPayeeInfo($payeeInfo)
            ->setPrices($prices);

        $paymentObject = new PaymentPurchaseObject();
        $paymentObject->setPayment($payment);
        $paymentObject->setCreditCard($creditCard);


        $purchaseRequest = new Purchase($paymentObject);
        $purchaseRequest->setClient($this->client);

        /** @var ResponseServiceInterface $responseService */
        $responseService = $purchaseRequest->send();

        $this->assertInstanceOf(ResponseServiceInterface::class, $responseService);

        /** @var ResponseResourceInterface $response */
        $responseResource = $responseService->getResponseResource();

        $this->assertInstanceOf(ResponseResourceInterface::class, $responseResource);

        $result = $responseService->getResponseData();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('payment', $result);
        $this->assertArrayHasKey('operations', $result);
        $this->assertEquals('Purchase', $result['payment']['operation']);
    }

    /**
     * @throws \SwedbankPay\Api\Client\Exception
     */
    public function testVerifyRequest()
    {
        $url = new PaymentUrl();
        $url->setCompleteUrl('http://test-dummy.net/payment-completed')
            ->setCancelUrl('http://test-dummy.net/payment-canceled')
            ->setCallbackUrl('http://test-dummy.net/payment-callback')
            ->setLogoUrl('https://example.com/logo.png')
            ->setTermsOfService('https://example.com/terms.pdf')
            ->setHostUrls(['https://example.com', 'https://example.net']);

        $payeeInfo = new PaymentPayeeInfo();
        $payeeInfo->setPayeeId(PAYEE_ID)
            ->setPayeeReference($this->generateRandomString(30))
            ->setPayeeName('Merchant1')
            ->setProductCategory('A123')
            ->setOrderReference('or-123456')
            ->setSubsite('MySubsite');

        $creditCard = new PaymentVerifyCreditcard();
        $creditCard->setNo3DSecure(false)
            ->setRejectCardNot3DSecureEnrolled(false)
            ->setRejectCreditCards(false)
            ->setRejectDebitCards(false)
            ->setRejectConsumerCards(false)
            ->setRejectCorporateCards(false)
            ->setRejectAuthenticationStatusA(false)
            ->setNoCvc(false);

        $payment = new PaymentVerify();
        $payment->setOperation('Verify')
            ->setIntent('Authorization')
            ->setCurrency('SEK')
            ->setDescription('Test Purchase')
            ->setUserAgent('Mozilla/5.0...')
            ->setLanguage('sv-SE')
            ->setPayerReference($this->generateRandomString(30))
            ->setUrls($url)
            ->setPayeeInfo($payeeInfo);

        $paymentObject = new PaymentVerifyObject();
        $paymentObject->setPayment($payment);
        $paymentObject->setCreditCard($creditCard);

        $verifyRequest = new Verify($paymentObject);
        $verifyRequest->setClient($this->client);

        /** @var ResponseServiceInterface $responseService */
        $responseService = $verifyRequest->send();

        $this->assertInstanceOf(ResponseServiceInterface::class, $responseService);

        /** @var ResponseResourceInterface $response */
        $responseResource = $responseService->getResponseResource();

        $this->assertInstanceOf(ResponseResourceInterface::class, $responseResource);

        $result = $responseService->getResponseData();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('payment', $result);
        $this->assertArrayHasKey('operations', $result);
        $this->assertEquals('Verify', $result['payment']['operation']);
    }

    public function testCapture()
    {
        $transactionData = new TransactionCapture();
        $transactionData->setAmount(100)
            ->setVatAmount(0)
            ->setDescription('Test Capture')
            ->setPayeeReference($this->generateRandomString(12));

        $transaction = new TransactionObject();
        $transaction->setTransaction($transactionData);

        $requestService = new CreateCapture($transaction);
        $requestService->setClient($this->client);
        $requestService->setPaymentId($this->paymentId);

        /** @var ResponseServiceInterface $responseService */
        $responseService = $requestService->send();
        $this->assertInstanceOf(ResponseServiceInterface::class, $responseService);

        /** @var CaptureObject $responseResource */
        $responseResource = $responseService->getResponseResource();
        $this->assertInstanceOf(CaptureObject::class, $responseResource);

        $result = $responseService->getResponseData();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('payment', $result);
        $this->assertArrayHasKey('capture', $result);
        $this->assertArrayHasKey('transaction', $result['capture']);
        $this->assertEquals('Capture', $result['capture']['transaction']['type']);

        return $requestService->getPaymentId();
    }

    /**
     * @depends CardPaymentTest::testCapture
     * @param $paymentId
     */
    public function testReversal($paymentId)
    {
        $transactionData = new TransactionReversal();
        $transactionData->setAmount(100)
            ->setVatAmount(0)
            ->setDescription('Test refund')
            ->setPayeeReference($this->generateRandomString(12));

        $transaction = new TransactionObject();
        $transaction->setTransaction($transactionData);

        $requestService = new CreateReversal($transaction);
        $requestService->setClient($this->client);
        $requestService->setPaymentId($paymentId);

        /** @var ResponseServiceInterface $responseService */
        $responseService = $requestService->send();
        $this->assertInstanceOf(ResponseServiceInterface::class, $responseService);

        /** @var ReversalObject $responseResource */
        $responseResource = $responseService->getResponseResource();
        $this->assertInstanceOf(ReversalObject::class, $responseResource);

        $result = $responseService->getResponseData();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('payment', $result);
        $this->assertArrayHasKey('reversal', $result);
        $this->assertArrayHasKey('transaction', $result['reversal']);
        $this->assertEquals('Reversal', $result['reversal']['transaction']['type']);
    }

    public function testCancellation()
    {
        $this->markTestSkipped('Capture/Reversal tests will be broken if this test will be executed.');

        $transactionData = new TransactionCancellation();
        $transactionData
            ->setDescription('Test Cancellation')
            ->setPayeeReference($this->generateRandomString(12));

        $transaction = new TransactionObject();
        $transaction->setTransaction($transactionData);

        $requestService = new CreateCancellation($transaction);
        $requestService->setClient($this->client);
        $requestService->setPaymentId($this->paymentId);

        /** @var ResponseServiceInterface $responseService */
        $responseService = $requestService->send();
        $this->assertInstanceOf(ResponseServiceInterface::class, $responseService);

        /** @var CancellationObject $responseResource */
        $responseResource = $responseService->getResponseResource();
        $this->assertInstanceOf(CancellationObject::class, $responseResource);

        $result = $responseService->getResponseData();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('payment', $result);
        $this->assertArrayHasKey('cancellation', $result);
        $this->assertArrayHasKey('transaction', $result['cancellation']);
        $this->assertEquals('Cancellation', $result['cancellation']['transaction']['type']);
    }

    public function testGetAuthorizations()
    {
        $requestService = new GetAuthorizations();
        $requestService->setClient($this->client)
            ->setPaymentId($this->paymentId);

        /** @var ResponseServiceInterface $responseService */
        $responseService = $requestService->send();
        $this->assertInstanceOf(ResponseServiceInterface::class, $responseService);

        /** @var AuthorizationsObject $responseResource */
        $responseResource = $responseService->getResponseResource();
        $this->assertInstanceOf(AuthorizationsObject::class, $responseResource);

        $result = $responseService->getResponseData();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('payment', $result);
        $this->assertArrayHasKey('authorizations', $result);
        $this->assertIsArray($result['authorizations']);

        return $result['authorizations'];
    }

    /**
     * @depends CardPaymentTest::testGetAuthorizations
     * @param array $authorizations
     */
    public function testGetAuthorization($authorizations)
    {
        foreach ($authorizations['authorization_list'] as $authorization) {
            $requestService = new GetAuthorization();
            $requestService->setClient($this->client)
                ->setRequestEndpoint($authorization['id']);

            /** @var ResponseServiceInterface $responseService */
            $responseService = $requestService->send();
            $this->assertInstanceOf(ResponseServiceInterface::class, $responseService);

            /** @var AuthorizationObject $responseResource */
            $responseResource = $responseService->getResponseResource();
            $this->assertInstanceOf(AuthorizationObject::class, $responseResource);

            $result = $responseService->getResponseData();

            $this->assertIsArray($result);
            $this->assertArrayHasKey('payment', $result);
            $this->assertArrayHasKey('authorization', $result);
            $this->assertIsArray($result['authorization']);
            $this->assertArrayHasKey('id', $result['authorization']);

            // Test the first item only
            break;
        }
    }

    public function testGetCancellations()
    {
        $this->markTestSkipped('Impossible to test if no any Cancellations.');

        $requestService = new GetCancellations();
        $requestService->setClient($this->client)
            ->setPaymentId($this->paymentId);

        /** @var ResponseServiceInterface $responseService */
        $responseService = $requestService->send();
        $this->assertInstanceOf(ResponseServiceInterface::class, $responseService);

        /** @var CancellationsObject $responseResource */
        $responseResource = $responseService->getResponseResource();
        $this->assertInstanceOf(CancellationsObject::class, $responseResource);

        $result = $responseService->getResponseData();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('payment', $result);
        $this->assertArrayHasKey('cancellations', $result);
        $this->assertIsArray($result['cancellations']);

        return $result['cancellations'];
    }

    /**
     * @depends CardPaymentTest::testGetCancellations
     * @param array $cancellations
     */
    public function testGetCancellation($cancellations)
    {
        if (!$cancellations) {
            $this->assertEquals(null, $cancellations);
            return;
        }

        foreach ($cancellations['cancellation_list'] as $cancellation) {
            $requestService = new GetCancellation();
            $requestService->setClient($this->client)
                ->setRequestEndpoint($cancellation['id']);

            /** @var ResponseServiceInterface $responseService */
            $responseService = $requestService->send();
            $this->assertInstanceOf(ResponseServiceInterface::class, $responseService);

            /** @var CancellationObject $responseResource */
            $responseResource = $responseService->getResponseResource();
            $this->assertInstanceOf(CancellationObject::class, $responseResource);

            $result = $responseService->getResponseData();

            $this->assertIsArray($result);
            $this->assertArrayHasKey('payment', $result);
            $this->assertArrayHasKey('cancellation', $result);
            $this->assertIsArray($result['cancellation']);
            $this->assertArrayHasKey('id', $result['cancellation']);

            // Test the first item only
            break;
        }
    }

    public function testGetCaptures()
    {
        $requestService = new GetCaptures();
        $requestService->setClient($this->client)
            ->setPaymentId($this->paymentId);

        /** @var ResponseServiceInterface $responseService */
        $responseService = $requestService->send();
        $this->assertInstanceOf(ResponseServiceInterface::class, $responseService);

        /** @var CapturesObject $responseResource */
        $responseResource = $responseService->getResponseResource();
        $this->assertInstanceOf(CapturesObject::class, $responseResource);

        $result = $responseService->getResponseData();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('payment', $result);
        $this->assertArrayHasKey('captures', $result);
        $this->assertIsArray($result['captures']);

        return $result['captures'];
    }

    /**
     * @depends CardPaymentTest::testGetCaptures
     * @param array $captures
     */
    public function testGetCapture($captures)
    {
        foreach ($captures['capture_list'] as $capture) {
            $requestService = new GetCapture();
            $requestService->setClient($this->client)
                ->setRequestEndpoint($capture['id']);

            /** @var ResponseServiceInterface $responseService */
            $responseService = $requestService->send();
            $this->assertInstanceOf(ResponseServiceInterface::class, $responseService);

            /** @var CaptureObject $responseResource */
            $responseResource = $responseService->getResponseResource();
            $this->assertInstanceOf(CaptureObject::class, $responseResource);

            $result = $responseService->getResponseData();

            $this->assertIsArray($result);
            $this->assertArrayHasKey('payment', $result);
            $this->assertArrayHasKey('capture', $result);
            $this->assertIsArray($result['capture']);
            $this->assertArrayHasKey('id', $result['capture']);

            // Test the first item only
            break;
        }
    }

    public function testGetReversals()
    {
        $requestService = new GetReversals();
        $requestService->setClient($this->client)
            ->setPaymentId($this->paymentId);

        /** @var ResponseServiceInterface $responseService */
        $responseService = $requestService->send();
        $this->assertInstanceOf(ResponseServiceInterface::class, $responseService);

        /** @var ReversalsObject $responseResource */
        $responseResource = $responseService->getResponseResource();
        $this->assertInstanceOf(ReversalsObject::class, $responseResource);

        $result = $responseService->getResponseData();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('payment', $result);
        $this->assertArrayHasKey('reversals', $result);
        $this->assertIsArray($result['reversals']);

        return $result['reversals'];
    }

    /**
     * @depends CardPaymentTest::testGetReversals
     * @param array $reversals
     */
    public function testGetReversal($reversals)
    {
        foreach ($reversals['reversal_list'] as $reversal) {
            $requestService = new GetReversal();
            $requestService->setClient($this->client)
                ->setRequestEndpoint($reversal['id']);

            /** @var ResponseServiceInterface $responseService */
            $responseService = $requestService->send();
            $this->assertInstanceOf(ResponseServiceInterface::class, $responseService);

            /** @var ReversalObject $responseResource */
            $responseResource = $responseService->getResponseResource();
            $this->assertInstanceOf(ReversalObject::class, $responseResource);

            $result = $responseService->getResponseData();

            $this->assertIsArray($result);
            $this->assertArrayHasKey('payment', $result);
            $this->assertArrayHasKey('reversal', $result);
            $this->assertIsArray($result['reversal']);
            $this->assertArrayHasKey('id', $result['reversal']);

            // Test the first item only
            break;
        }
    }

    public function testGetTransactions()
    {
        $requestService = new GetTransactions();
        $requestService->setClient($this->client)
            ->setPaymentId($this->paymentId);

        /** @var ResponseServiceInterface $responseService */
        $responseService = $requestService->send();
        $this->assertInstanceOf(ResponseServiceInterface::class, $responseService);

        /** @var TransactionsObject $responseResource */
        $responseResource = $responseService->getResponseResource();
        $this->assertInstanceOf(TransactionsObject::class, $responseResource);

        $result = $responseService->getResponseData();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('payment', $result);
        $this->assertArrayHasKey('transactions', $result);
        $this->assertIsArray($result['transactions']);

        return $result['transactions'];
    }

    /**
     * @depends CardPaymentTest::testGetTransactions
     * @param array $transactions
     */
    public function testGetTransaction($transactions)
    {
        foreach ($transactions['transaction_list'] as $transaction) {
            $requestService = new GetTransaction();
            $requestService->setClient($this->client)
                ->setRequestEndpoint($transaction['id']);

            /** @var ResponseServiceInterface $responseService */
            $responseService = $requestService->send();
            $this->assertInstanceOf(ResponseServiceInterface::class, $responseService);

            /** @var TransactionObjectResponse $responseResource */
            $responseResource = $responseService->getResponseResource();
            $this->assertInstanceOf(TransactionObjectResponse::class, $responseResource);

            $result = $responseService->getResponseData();

            $this->assertIsArray($result);
            $this->assertArrayHasKey('payment', $result);
            $this->assertArrayHasKey('transaction', $result);
            $this->assertIsArray($result['transaction']);
            $this->assertArrayHasKey('id', $result['transaction']);
            $this->assertArrayHasKey('created', $result['transaction']);
            $this->assertArrayHasKey('updated', $result['transaction']);
            $this->assertArrayHasKey('type', $result['transaction']);
            $this->assertArrayHasKey('state', $result['transaction']);
            $this->assertArrayHasKey('number', $result['transaction']);
            $this->assertArrayHasKey('amount', $result['transaction']);
            $this->assertArrayHasKey('vat_amount', $result['transaction']);
            $this->assertArrayHasKey('description', $result['transaction']);
            $this->assertArrayHasKey('payee_reference', $result['transaction']);
            $this->assertArrayHasKey('is_operational', $result['transaction']);
            $this->assertArrayHasKey('operations', $result['transaction']);

            // Test the first item only
            break;
        }
    }




}
