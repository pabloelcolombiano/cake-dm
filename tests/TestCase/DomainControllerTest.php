<?php
namespace CakeDomainManager\Test;


use Cake\Controller\Controller;
use Cake\Core\Configure;
use CakeDomainManager\DomainController;
use PHPUnit\Framework\TestCase as BaseTestCase;

class DomainControllerTest extends BaseTestCase
{
    /**
     * @var DomainController
     */
    public $domainController;

    public function setUp()
    {
        $controller = $this->createMock(Controller::class);
        $controller->method('getName')->willReturn('Test');

        $this->domainController = DomainController::init($controller);
    }

    public function testExtractLayerFromFileNameCorrect()
    {
        $expectedLayer = 'Domain' . DS . 'TestDomain';
        $fileName = APP . $expectedLayer . DS . 'Controller' . DS . 'TestController.php';
        $layer = $this->domainController->extractDomainLayerFromFileName($fileName);

        $this->assertEquals($expectedLayer, $layer);
    }

    public function testExtractLayerFromFileNameCorrect2()
    {
        $expectedLayer = 'Domain' . DS . 'TestDomain' . DS . 'TestSubDomain';
        $fileName = APP . $expectedLayer . DS . 'Controller' . DS . 'TestController.php';
        $layer = $this->domainController->extractDomainLayerFromFileName($fileName);

        $this->assertEquals($expectedLayer, $layer);
    }

    public function testExtractLayerFromFileNameIncorrect()
    {
        $expectedLayer = 'Domain' . DS . 'TestDomain';
        $fileName = APP . $expectedLayer . DS . 'Controlle' . DS . 'TestController.php';
        $layer = $this->domainController->extractDomainLayerFromFileName($fileName);

        $this->assertEquals(null, $layer);
    }
}