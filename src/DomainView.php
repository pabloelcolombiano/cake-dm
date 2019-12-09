<?php


namespace CakeDomainManager;


use Cake\Event\EventManager;
use Cake\Http\Response;
use Cake\Http\ServerRequest;
use Cake\View\View;

class DomainView extends View
{
    public static function init(View $view)
    {
        return new static($view->getRequest(), $view->getResponse(), $view->getEventManager());
    }

    protected function _getElementFileName($name, $pluginCheck = true)
    {
        return parent::_getElementFileName(
            $this->extractElementName($name),
            $pluginCheck
        );
    }

    public function extractElementName(string $name) : string
    {
        $cast = explode('@', $name);
        if (count($cast) === 2) {
            $template = $cast[0];
            $layer = $cast[1];
            $name = ".." . DS . ".." . DS . ".." . DS . $layer . DS . 'Template' . DS . 'Element' . DS . $template;
        }

        return $name;
    }
}
