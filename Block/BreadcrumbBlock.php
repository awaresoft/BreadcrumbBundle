<?php

namespace Awaresoft\BreadcrumbBundle\Block;

use Awaresoft\BreadcrumbBundle\Breadcrumb\BreadcrumbItem;
use Awaresoft\Sonata\PageBundle\Entity\PageRepository;
use Doctrine\ORM\EntityManager;
use Knp\Menu\MenuItem;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Block\Service\MenuBlockService;
use Knp\Menu\ItemInterface;
use Knp\Menu\Provider\MenuProviderInterface;
use Knp\Menu\FactoryInterface;
use Awaresoft\BreadcrumbBundle\Exception\ContextNotAvailableException;
use Awaresoft\BreadcrumbBundle\Exception\ContextNotFoundException;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class BreadcrumbBlockService
 *
 * @author Bartosz Malec <b.malec@awaresoft.pl>
 */
class BreadcrumbBlock extends MenuBlockService
{

    /**
     * @var array referrer to breadcrumbs menu
     */
    const MENUS = ['AwaresoftBreadcrumbBundle:BlockBuilder:breadcrumb' => 'dynamic'];

    /**
     * @var string
     */
    const DEFAULT_CLASS = 'breadcrumb';

    /**
     * @var string
     */
    private $context;

    /**
     * @var FactoryInterface
     */
    private $factory;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var MenuItem
     */
    protected $menu;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @param string $name
     * @param EngineInterface $templating
     * @param MenuProviderInterface $menuProvider
     * @param FactoryInterface $factory
     * @param ContainerInterface $container
     */
    public function __construct($name, EngineInterface $templating, MenuProviderInterface $menuProvider, FactoryInterface $factory, ContainerInterface $container)
    {
        $this->container = $container;
        $this->em = $this->container->get('doctrine.orm.entity_manager');
        $this->factory = $factory;
        $this->context = null;

        parent::__construct($name, $templating, $menuProvider, self::MENUS);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return sprintf("Breadcrumb %s", $this->context);
    }

    /**
     * {@inheritdoc}
     */
    public function configureSettings(OptionsResolver $resolver)
    {
        parent::configureSettings($resolver);

        $resolver->setDefaults([
            'template' => 'AwaresoftBreadcrumbBundle:Block:block_breadcrumb.html.twig',
            'include_homepage_link' => true,
            'request' => [],
        ]);
    }

    /**
     * @return null|string
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Return true if current BlockService handles the given context.
     *
     * @param string $context
     *
     * @return boolean
     */
    public function handleContext($context)
    {
        return $this->context === $context;
    }

    /**
     * Check context and choose from available
     *
     * @return string
     *
     * @throws ContextNotAvailableException
     * @throws ContextNotFoundException
     */
    protected function checkContext()
    {
        $request = $this->container->get('request');
        $page = $request->attributes->get('page');
        $availableRoutes = $this->container->getParameter('awaresoft.breadcrumb.options')['routes'];
        $hiddenOnRoutes = $this->container->getParameter('awaresoft.breadcrumb.options')['hidden_on_routes'];

        if (array_key_exists($request->get('_route'), $hiddenOnRoutes)) {
            throw new ContextNotAvailableException('page_slug');
        }

        if (!$availableRoutes['page_slug']) {
            throw new ContextNotFoundException('page_slug');
        }

        if ($page) {
            return $availableRoutes['page_slug'];
        }

        $route = $request->get('_route');

        if (array_key_exists($route, $availableRoutes)) {
            return $availableRoutes[$route];
        }

        return $availableRoutes['page_slug'];
    }

    /**
     * Initialize breadcrumb menu.
     *
     * @param BlockContextInterface $blockContext
     *
     * @return ItemInterface
     */
    protected function getRootMenu(BlockContextInterface $blockContext)
    {
        $this->setExtendedRequest($blockContext);
        $settings = $blockContext->getSettings();
        $menu = $this->factory->createItem('breadcrumb');
        $baseUrl = $this->getRequest()->getBaseUrl() . $this->container->get('sonata.page.site.selector')
                ->getRequestContext()
                ->getBaseUrl();

        if (!$baseUrl) {
            $baseUrl = '/';
        }

        $childrenClass = $blockContext->getSetting('menu_class');
        if (!$childrenClass) {
            $childrenClass = self::DEFAULT_CLASS;
        }

        $menu->setChildrenAttribute('class', $childrenClass);

        if (method_exists($menu, 'setCurrentUri')) {
            $menu->setCurrentUri($settings['current_uri']);
        }

        if (method_exists($menu, 'setCurrent')) {
            $menu->setCurrent($settings['current_uri']);
        }

        if ($settings['include_homepage_link']) {
            $menu->addChild($this->container->get('translator')->trans('breadcrumb.homepage'),
                ['uri' => $baseUrl]);
        }

        return $menu;
    }

    /**
     * Set extended request params to global request
     *
     * @param BlockContextInterface $blockContext
     */
    protected function setExtendedRequest(BlockContextInterface $blockContext)
    {
        $request = $this->container->get('request');
        $extendedRequestParams = $blockContext->getBlock()->getSetting('request');

        if (count($extendedRequestParams) > 0) {
            foreach ($extendedRequestParams as $key => $param) {
                $request->attributes->set($key, $param);
            }
        }
    }

    /**
     * Gets the menu to render from prepared Breadcrumb class
     *
     * @param BlockContextInterface $blockContext
     *
     * @return ItemInterface|string
     */
    protected function getMenu(BlockContextInterface $blockContext)
    {
        $this->context = $this->checkContext();
        $this->menu = $this->getRootMenu($blockContext);
        $contextClass = new $this->context($this->container);
        $breadcrumbItems = $contextClass->create();

        if (!is_array($breadcrumbItems)) {
            return $this->menu;
        }

        $contextClass->cleanLastItem($breadcrumbItems);

        foreach ($breadcrumbItems as $breadcrumb) {
            $this->addChild($breadcrumb);
        }

        return $this->menu;
    }

    /**
     * Add child to menu object
     *
     * @param BreadcrumbItem $item
     *
     * @return MenuItem
     */
    protected function addChild($item)
    {
        return $this->menu->addChild($item->getName(), [
            'uri' => $item->getUrl(),
            'extras' => [
                'object' => $item,
            ],
        ]);
    }

    /**
     * @return Request
     */
    protected function getRequest()
    {
        return $this->container->get('request');
    }

    /**
     * @return PageRepository
     */
    protected function getPageRepository()
    {
        return $this->em->getRepository('AwaresoftSonataPageBundle:Page');
    }
}
