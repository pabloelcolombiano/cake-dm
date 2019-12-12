<?php
namespace CakeDomainManager\Test;


use Cake\Core\Configure;
use Cake\View\View;
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
        Configure::write('DomainManager.controller_domain', 'Layer');
    }

    public function testExtractElementNameWithOneLevelLayer()
    {
        $elementName = 'element@TestLayer';
        $expectedElementName = '../../../TestLayer/Template/Element/element';

        $parsedElementName = $this->domainView->extractElementName($elementName);

        $this->assertEquals($expectedElementName, $parsedElementName);
    }

    public function testExtractElementNameWithTwoLevelsLayerFromOneLayer()
    {
        $elementName = 'element@TestLayer/TestSublayer';
        $expectedElementName = '../../../TestLayer/TestSublayer/Template/Element/element';

        $parsedElementName = $this->domainView->extractElementName($elementName);

        $this->assertEquals($expectedElementName, $parsedElementName);
    }

    public function testExtractElementNameWithTwoLevelsLayerFromTwoLayers()
    {
        Configure::write('DomainManager.controller_domain', 'Layer/SubLayer');

        $elementName = 'element@TestLayer/TestSublayer';
        $expectedElementName = '../../../../TestLayer/TestSublayer/Template/Element/element';

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

    public function testExtractElementNameFromPlugin()
    {
        $elementName = 'TestPlugin.element@TestPluginLayer';
        $expectedElementName = '../../../../plugins/TestPlugin/src/TestPluginLayer/Template/Element/element';

        $parsedElementName = $this->domainView->extractElementName($elementName);

        $this->assertEquals($expectedElementName, $parsedElementName);
    }

    public function testExtractElementNameFromPluginWithinSublayer()
    {
        Configure::write('DomainManager.controller_domain', 'Domain/Layer/Sublayer');

        $elementName = 'TestPlugin.element@TestPluginLayer/TestPluginSublayer';
        $expectedElementName = '../../../../../../plugins/TestPlugin/src/TestPluginLayer/TestPluginSublayer/Template/Element/element';

        $parsedElementName = $this->domainView->extractElementName($elementName);

        $this->assertEquals($expectedElementName, $parsedElementName);
    }
}