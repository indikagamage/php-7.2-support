<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Model;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\Invoice\NotifierInterface;
use Magento\Sales\Model\Order\InvoiceDocumentFactory;
use Magento\Sales\Model\Order\InvoiceRepository;
use Magento\Sales\Model\Order\InvoiceValidatorInterface;
use Magento\Sales\Model\Order\OrderStateResolverInterface;
use Magento\Sales\Model\Order\PaymentAdapterInterface;
use Magento\Sales\Model\Order\Config as OrderConfig;
use Magento\Sales\Model\OrderInvoice;
use Magento\Sales\Api\Data\InvoiceCommentCreationInterface;
use Magento\Sales\Api\Data\InvoiceCreationArgumentsInterface;
use Magento\Sales\Api\Data\InvoiceInterface;

/**
 * Class OrderInvoiceTest
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class OrderInvoiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ResourceConnection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $resourceConnectionMock;

    /**
     * @var OrderRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderRepositoryMock;

    /**
     * @var InvoiceDocumentFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $invoiceDocumentFactoryMock;

    /**
     * @var InvoiceValidatorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $invoiceValidatorMock;

    /**
     * @var PaymentAdapterInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $paymentAdapterMock;

    /**
     * @var OrderStateResolverInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderStateResolverMock;

    /**
     * @var OrderConfig|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    /**
     * @var InvoiceRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    private $invoiceRepositoryMock;

    /**
     * @var NotifierInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $notifierInterfaceMock;

    /**
     * @var OrderInvoice|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderInvoice;

    /**
     * @var InvoiceCreationArgumentsInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $invoiceCommentCreationMock;

    /**
     * @var InvoiceCommentCreationInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $invoiceCreationArgumentsMock;

    /**
     * @var OrderInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $orderMock;

    /**
     * @var InvoiceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $invoiceMock;

    /**
     * @var AdapterInterface
     */
    private $adapterInterface;

    /**
     * @var LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $loggerMock;

    protected function setUp()
    {
        $this->resourceConnectionMock = $this->getMockBuilder(ResourceConnection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->orderRepositoryMock = $this->getMockBuilder(OrderRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->invoiceDocumentFactoryMock = $this->getMockBuilder(InvoiceDocumentFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->invoiceValidatorMock = $this->getMockBuilder(InvoiceValidatorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->paymentAdapterMock = $this->getMockBuilder(PaymentAdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->orderStateResolverMock = $this->getMockBuilder(OrderStateResolverInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->configMock = $this->getMockBuilder(OrderConfig::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->invoiceRepositoryMock = $this->getMockBuilder(InvoiceRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->notifierInterfaceMock = $this->getMockBuilder(NotifierInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->loggerMock = $this->getMockBuilder(LoggerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->invoiceCommentCreationMock = $this->getMockBuilder(InvoiceCommentCreationInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->invoiceCreationArgumentsMock = $this->getMockBuilder(InvoiceCreationArgumentsInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->orderMock = $this->getMockBuilder(OrderInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->invoiceMock = $this->getMockBuilder(InvoiceInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->adapterInterface = $this->getMockBuilder(AdapterInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->orderInvoice = new OrderInvoice(
            $this->resourceConnectionMock,
            $this->orderRepositoryMock,
            $this->invoiceDocumentFactoryMock,
            $this->invoiceValidatorMock,
            $this->paymentAdapterMock,
            $this->orderStateResolverMock,
            $this->configMock,
            $this->invoiceRepositoryMock,
            $this->notifierInterfaceMock,
            $this->loggerMock
        );
    }

    /**
     * @dataProvider dataProvider
     */
    public function testOrderInvoice($orderId, $capture, $items, $notify, $appendComment)
    {
        $this->resourceConnectionMock->expects($this->once())
            ->method('getConnection')
            ->with('sales')
            ->willReturn($this->adapterInterface);

        $this->orderRepositoryMock->expects($this->once())
            ->method('get')
            ->willReturn($this->orderMock);

        $this->invoiceDocumentFactoryMock->expects($this->once())
            ->method('create')
            ->with(
                $this->orderMock,
                $items,
                $this->invoiceCommentCreationMock,
                ($appendComment && $notify),
                $this->invoiceCreationArgumentsMock
            )->willReturn($this->invoiceMock);

        $this->invoiceValidatorMock->expects($this->once())
            ->method('validate')
            ->with($this->invoiceMock, $this->orderMock)
            ->willReturn([]);

        $this->paymentAdapterMock->expects($this->once())
            ->method('pay')
            ->with($this->orderMock, $this->invoiceMock, $capture)
            ->willReturn($this->orderMock);

        $this->orderStateResolverMock->expects($this->once())
            ->method('getStateForOrder')
            ->with($this->orderMock, [OrderStateResolverInterface::IN_PROGRESS])
            ->willReturn(Order::STATE_PROCESSING);

        $this->orderMock->expects($this->once())
            ->method('setState')
            ->with(Order::STATE_PROCESSING)
            ->willReturnSelf();

        $this->orderMock->expects($this->once())
            ->method('getState')
            ->willReturn(Order::STATE_PROCESSING);

        $this->configMock->expects($this->once())
            ->method('getStateDefaultStatus')
            ->with(Order::STATE_PROCESSING)
            ->willReturn('Processing');

        $this->orderMock->expects($this->once())
            ->method('setStatus')
            ->with('Processing')
            ->willReturnSelf();

        $this->invoiceMock->expects($this->once())
            ->method('setState')
            ->with(\Magento\Sales\Model\Order\Invoice::STATE_PAID)
            ->willReturnSelf();

        $this->invoiceRepositoryMock->expects($this->once())
            ->method('save')
            ->with($this->invoiceMock)
            ->willReturn($this->invoiceMock);

        $this->orderRepositoryMock->expects($this->once())
            ->method('save')
            ->with($this->orderMock)
            ->willReturn($this->orderMock);

        if ($notify) {
            $this->notifierInterfaceMock->expects($this->once())
                ->method('notify')
                ->with($this->orderMock, $this->invoiceMock, $this->invoiceCommentCreationMock);
        }

        $this->invoiceMock->expects($this->once())
            ->method('getEntityId')
            ->willReturn(2);

        $this->assertEquals(
            2,
            $this->orderInvoice->execute(
                $orderId,
                $capture,
                $items,
                $notify,
                $appendComment,
                $this->invoiceCommentCreationMock,
                $this->invoiceCreationArgumentsMock
            )
        );
    }

    /**
     * @expectedException \Magento\Sales\Api\Exception\DocumentValidationExceptionInterface
     */
    public function testDocumentValidationException()
    {
        $orderId = 1;
        $capture = true;
        $items = [1 => 2];
        $notify = true;
        $appendComment = true;
        $errorMessages = ['error1', 'error2'];

        $this->orderRepositoryMock->expects($this->once())
            ->method('get')
            ->willReturn($this->orderMock);

        $this->invoiceDocumentFactoryMock->expects($this->once())
            ->method('create')
            ->with(
                $this->orderMock,
                $items,
                $this->invoiceCommentCreationMock,
                ($appendComment && $notify),
                $this->invoiceCreationArgumentsMock
            )->willReturn($this->invoiceMock);

        $this->invoiceValidatorMock->expects($this->once())
            ->method('validate')
            ->with($this->invoiceMock, $this->orderMock)
            ->willReturn($errorMessages);

        $this->orderInvoice->execute(
            $orderId,
            $capture,
            $items,
            $notify,
            $appendComment,
            $this->invoiceCommentCreationMock,
            $this->invoiceCreationArgumentsMock
        );
    }

    /**
     * @expectedException \Magento\Sales\Api\Exception\CouldNotInvoiceExceptionInterface
     */
    public function testCouldNotInvoiceException()
    {
        $orderId = 1;
        $items = [1 => 2];
        $capture = true;
        $notify = true;
        $appendComment = true;
        $this->resourceConnectionMock->expects($this->once())
            ->method('getConnection')
            ->with('sales')
            ->willReturn($this->adapterInterface);

        $this->orderRepositoryMock->expects($this->once())
            ->method('get')
            ->willReturn($this->orderMock);

        $this->invoiceDocumentFactoryMock->expects($this->once())
            ->method('create')
            ->with(
                $this->orderMock,
                $items,
                $this->invoiceCommentCreationMock,
                ($appendComment && $notify),
                $this->invoiceCreationArgumentsMock
            )->willReturn($this->invoiceMock);

        $this->invoiceValidatorMock->expects($this->once())
            ->method('validate')
            ->with($this->invoiceMock, $this->orderMock)
            ->willReturn([]);
        $e = new \Exception;

        $this->paymentAdapterMock->expects($this->once())
            ->method('pay')
            ->with($this->orderMock, $this->invoiceMock, $capture)
            ->willThrowException($e);

        $this->loggerMock->expects($this->once())
            ->method('critical')
            ->with($e);

        $this->adapterInterface->expects($this->once())
            ->method('rollBack');

        $this->orderInvoice->execute(
            $orderId,
            $capture,
            $items,
            $notify,
            $appendComment,
            $this->invoiceCommentCreationMock,
            $this->invoiceCreationArgumentsMock
        );
    }

    public function dataProvider()
    {
        return [
            'TestWithNotifyTrue' => [1, true, [1 => 2], true, true],
            'TestWithNotifyFalse' => [1, true, [1 => 2], false, true]
        ];
    }
}
