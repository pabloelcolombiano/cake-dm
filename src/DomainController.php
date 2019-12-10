<?php


namespace CakeDomainManager;


use Cake\Controller\Controller;
use Cake\Core\Configure;

/**
 * DomainController
 *
 * @property Controller $masterClass
 */
class DomainController
{
    use DomainManagerTrait;

    /**
     * DomainController constructor.
     * @param Controller $controller
     */
    public function __construct(Controller $controller)
    {
        $this->masterClass = $controller;
        $this->defineType('Controller');
        $this->defineControllerDomain();
    }

    /**
     * In order to communicate to the DomainView in which domain layer
     * the request was called, the actual domain layer is written in
     * Configure.
     */
    public function defineControllerDomain()
    {
        Configure::write('DomainManager.controller_domain', $this->getDomainLayer());
    }

    /**
     * @param Controller $controller
     * @return static
     */
    public static function init(Controller $controller)
    {
        return new static($controller);
    }

    /**
     * Check if the current controller is within a domain layer
     * If it is the case, the App and the domain layers are added
     * to the tmplate paths
     * If in a plugin, the corresponding paths are also added
     */
    public function setTemplatePaths()
    {
        $layer = $this->getDomainLayer();

        $templatePaths = Configure::read('App.paths.templates', []);

        $baseAppPath = 'Domain' . DS . Configure::read('App.namespace', 'App');

        array_unshift($templatePaths, APP . $baseAppPath . DS . 'Template' . DS);

        if (!empty($layer)) {
            array_unshift($templatePaths, APP . $layer . DS . 'Template' . DS);
        }

        if ($this->masterClass->getPlugin()) {
            array_unshift($templatePaths, $this->getPluginFolder() . 'Domain' . DS . 'Plugin' . DS . 'Template' . DS);
            if (!empty($layer)) {
                array_unshift($templatePaths, $this->getPluginFolder() . $layer . DS . 'Template' . DS);
            }
        }

        Configure::write('App.paths.templates', $templatePaths);
    }
}
