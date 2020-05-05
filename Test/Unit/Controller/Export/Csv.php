<?php
namespace Born\ExportCart\Test\Unit\Controller\Export;

use Born\ExportCart\Controller\Export\Csv as CsvController;
use Magento\Framework\App\Action\Context;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\File\Csv as FileCsv;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Test customer account controller
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Csv extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CsvController
     */
    protected $controller;

    /**
     * @var Context | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $context;

    /**
     * @var Session | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $session;

    /**
     * @var FileFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $fileFactoryMock;

    /**
     * @var FileCsv|\PHPUnit_Framework_MockObject_MockObject
     */
    private $csvProcessorMock;

    /**
     * @var ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $configMock;

    /** @var DirectoryList|\PHPUnit_Framework_MockObject_MockObject  */
    protected $directoryListMock;

    /**
     * @var \Magento\Framework\App\ResponseInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $responseMock;


    protected function setUp()
    {
        $this->prepareContext();

        $this->context = $this->getMockBuilder(\Magento\Framework\App\Action\Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->session = $this->getMockBuilder(\Magento\Checkout\Model\Session::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->fileFactoryMock = $this->getMockBuilder(\Magento\Framework\App\Response\Http\FileFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->csvProcessorMock = $this->getMockBuilder(\Magento\Framework\File\Csv::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->directoryListMock = $this->getMockBuilder(\Magento\Framework\App\Filesystem\DirectoryList::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->configMock = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        
        $this->controller = new CsvController(
            $this->context,
            $this->session,
            $this->fileFactoryMock,
            $this->csvProcessorMock,
            $this->directoryListMock,
            $this->configMock
        );

        $this->responseMock = $this->getMockBuilder(\Magento\Framework\App\ResponseInterface::class)
            ->getMock();
    }

    /**
     * @expectedException \Magento\Framework\Exception\NotFoundException
     */
    public function testExecuteDisabled()
    {
        $this->configMock->expects($this->once())
            ->method('getValue')
            ->with('customer/exportcart/enabled')
            ->willReturn(0);
        $this->controller->execute();
    }

    public function testExecuteSuccess()
    {
        $this->configMock->expects($this->once())
            ->method('getValue')
            ->with('customer/exportcart/enabled')
            ->willReturn(1);

        $quote = $this->getMockBuilder(\Magento\Quote\Model\Quote::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->session->expects($this->once())
            ->method('getQuote')
            ->willReturn($quote);

        $quote->expects($this->once())
            ->method('getAllVisibleItems')
            ->willReturn([]);

        $this->csvProcessorMock->expects($this->once())
            ->method('setDelimiter')
            ->with(';')
            ->willReturnSelf();

        $this->csvProcessorMock->expects($this->once())
            ->method('setEnclosure')
            ->with('"')
            ->willReturnSelf();

        $this->fileFactoryMock->expects($this->once())
            ->method('create')->with(
                'cart.csv',
                [
                    'type' => "filename",
                    'value' => 'cart.csv',
                    'rm' => true,
                ],
                DirectoryList::VAR_DIR,
                'application/octet-stream'
            )
            ->willReturn($this->responseMock);

        $this->assertSame($this->responseMock, $this->controller->execute());
    }

    protected function prepareContext()
    {
        $this->context = $this->getMockBuilder(\Magento\Framework\App\Action\Context::class)
            ->disableOriginalConstructor()
            ->getMock();

    }
}
