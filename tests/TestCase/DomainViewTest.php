<?php
namespace CakeDomainManager\Test;


use Cake\Controller\Controller;
use Cake\Core\Configure;
use Cake\View\View;
use CakeDomainManager\DomainController;
use CakeDomainManager\DomainView;
use PHPUnit\Framework\TestCase as BaseTestCase;

class DomainViewTest extends BaseTestCase
{
    /**
     * @var DomainView
     */
    public $domainView;

    public function setUp()
    {
        $view = $this->createMock(View::class);
        $this->domainView = DomainView::init($view);
    }

    public function testExtractElementNameWithOneLevelLayer()
    {
        $elementName = 'element@TestLayer';
        $expectedElementName = '../../../TestLayer/Template/Element/element';

        $parsedElementName = $this->domainView->extractElementName($elementName);

        $this->assertEquals($expectedElementName, $parsedElementName);
    }

    public function testExtractElementNameWithTwoLevelsLayer()
    {
        $elementName = 'element@TestLayer/TestSublayer';
        $expectedElementName = '../../../TestLayer/TestSublayer/Template/Element/element';

        $parsedElementName = $this->domainView->extractElementName($elementName);

        $this->assertEquals($expectedElementName, $parsedElementName);
    }

    public function testExtractElementNameWithNoLayer()
    {
        $elementName = 'element';
        $expectedElementName = 'element';

        $parsedElementName = $this->domainView->extractElementName($elementName);

        $this->assertEquals($expectedElementName, $parsedElementName);
    }
}