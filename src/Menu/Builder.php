<?php

namespace Awaresoft\BreadcrumbBundle\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Awaresoft\BreadcrumbBundle\Exception\ContextNotAvailableException;
use Awaresoft\BreadcrumbBundle\Exception\ContextNotFoundException;
use Awaresoft\BreadcrumbBundle\Breadcrumb\BreadcrumbItem;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * Class Builder
 * Builder for breadcrumb menu
 *
 * @author Bartosz Malec <b.malec@awaresoft.pl>
 */
class Builder
{
    use ContainerAwareTrait;

    /**
     * @var ItemInterface
     */
    protected $menu;

    /**
     * @var string
     */
    protected $context;

    /**
     * @var FactoryInterface
     */
    protected $factory;

    /**
     * Generate breadcrumb menu
     *
     * @param FactoryInterface $factory
     * @param array $options
     *
     * @return ItemInterface
     *
     * @throws ContextNotFoundException
     */
    public function breadcrumb(FactoryInterface $factory, array $options)
    {
        if (!$options['context']) {
            return;
        }

        $this->context = $options['context'];
        $this->factory = $factory;

        try {
            $this->menu = $this->build();
        } catch(ContextNotAvailableException $ex) {

        }

        return $this->menu;
    }

    /**
     * Build breadcrumb Menu
     *
     * @return ItemInterface
     *
     * @throws ContextNotAvailableException
     */
    protected function build()
    {
        $this->menu = $this->factory->createItem('root');
        $classes = $this->container->getParameter('awaresoft.breadcrumb.options')['classes'];

        if (!array_key_exists($this->context, $classes)) {
            throw new ContextNotAvailableException($this->context);
        }

        $contextClass = new $classes[$this->context]($this->container);
        $breadcrumbItems = $contextClass->create();
        $baseUrl = $this->getRequest()->getBaseUrl();

        foreach ($breadcrumbItems as $breadcrumb) {
            $this->addChild($breadcrumb, $baseUrl);
        }

        $this->setCurrentItem($this->menu);

        return $this->menu;
    }

    /**
     * Add child to menu object
     *
     * @param BreadcrumbItem $item
     * @param $baseUrl
     *
     * @return ItemInterface
     */
    protected function addChild($item, $baseUrl)
    {
        return $this->menu->addChild($item->getName(), array(
            'uri' => $baseUrl.$item->getUrl(),
            'extras' => array(
                'object' => $item
            )
        ));
    }

    /**
     * @param ItemInterface $menu
     */
    protected function setCurrentItem(ItemInterface $menu)
    {
        $menu->setCurrent($this->container->get('request')->getPathInfo());
    }

    /**
     * Return request from container
     *
     * @return \Symfony\Component\HttpFoundation\Request
     */
    protected function getRequest()
    {
        return $this->container->get('request');
    }
}
