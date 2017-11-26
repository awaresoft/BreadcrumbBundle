<?php

namespace Awaresoft\BreadcrumbBundle\Breadcrumb;

use Awaresoft\Sonata\PageBundle\Entity\Page;
use Awaresoft\Sonata\PageBundle\Entity\PageRepository;
use Doctrine\ORM\EntityManager;
use Sonata\PageBundle\Model\PageInterface;
use Sonata\PageBundle\Model\SiteInterface;
use Symfony\Cmf\Component\Routing\ChainRouter;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\DataCollectorTranslator;

/**
 * Abstract class AbstractBreadcrumb
 * Supports breadcrumbs in the website
 * Please configure support for bundle classes in yml config file
 *
 * @author Bartosz Malec <b.malec@awaresoft.pl>
 */
abstract class AbstractBreadcrumb implements BreadcrumbInterface
{
    /**
     * @var Request
     */
    protected $request;

    /**
     * @var ChainRouter
     */
    protected $router;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var PageInterface
     */
    protected $cmsPage;

    /**
     * @var object|\Sonata\PageBundle\CmsManager\CmsManagerInterface|\Sonata\PageBundle\CmsManager\CmsPageManager|\Sonata\PageBundle\CmsManager\CmsSnapshotManager
     */
    protected $cmsManager;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var DataCollectorTranslator
     */
    protected $translator;

    /**
     * @var SiteInterface
     */
    protected $site;

    /**
     * AbstractBreadcrumb constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->em = $container->get('doctrine.orm.entity_manager');
        $this->request = $container->get('request_stack')->getCurrentRequest();
        $this->router = $container->get('router');
        $this->translator = $container->get('translator');
        $this->container = $container;
        $this->site = $container->get('sonata.page.site.selector')->retrieve();
        $this->cmsManager = $this->container->get('sonata.page.cms_manager_selector')->retrieve();
        $this->cmsPage = $this->cmsManager->getCurrentPage();
    }

    /**
     * @param BreadcrumbItem[] $items
     *
     * @return BreadcrumbItem[]
     */
    public function cleanLastItem(array $items)
    {
        $count = count($items);

        if ($count <= 0) {
            return $items;
        }

        $items[$count - 1]->setActive(true);
        $items[$count - 1]->setUrl(null);

        return $items;
    }

    /**
     * @return PageRepository
     */
    protected function getPageRepository()
    {
        return $this->em->getRepository('AwaresoftSonataPageBundle:Page');
    }

    /**
     * Find index parent by child action
     *
     * @param null $indexRoute
     *
     * @return Page|null
     */
    protected function getIndexParent($indexRoute = null)
    {
        if (!$this->cmsPage) {
            return null;
        }

        if (!$indexRoute) {
            $route = $this->cmsPage->getRouteName();
            $routeComponents = explode('_', $route);
            $lastRouteComponent = end($routeComponents);
            $indexRoute = str_replace('_' . $lastRouteComponent, '_index', $route);
        }

        return $this->container->get('awaresoft.page.manager.page')->getMultisitePageByRoute($indexRoute);
    }

    /**
     * Prepare parents breadcrumb for page
     *
     * @param PageInterface $page
     *
     * @return array
     */
    protected function prepareParentBreadcrumbs(PageInterface $page = null)
    {
        if (!$page) {
            $page = $this->cmsPage;
        }

        $baseUrl = $this->request->getBaseUrl();
        $parents = $this->prepareParents($page);
        $parentsCount = count($parents);
        $breadcrumbs = [];

        for ($i = $parentsCount - 1; $i >= 0; $i--) {
            $item = new BreadcrumbItem();
            $item->setName($parents[$i]->getName());
            $item->setUrl($baseUrl . $parents[$i]->getUrl());
            $item->setActive(false);

            $breadcrumbs[] = $item;
        }

        return $breadcrumbs;
    }

    /**
     * Prepare breadcrumb for page
     *
     * @param PageInterface $page
     * @param bool $isLastActive
     *
     * @return array
     */
    protected function preparePageBreadcrumbs(PageInterface $page = null, $isLastActive = false)
    {
        $baseUrl = $this->request->getBaseUrl();
        $breadcrumbs = $this->prepareParentBreadcrumbs($page);

        $item = new BreadcrumbItem();
        $item->setName($this->cmsPage->getName());
        $item->setUrl($baseUrl . $this->cmsPage->getUrl());
        $item->setActive($isLastActive);

        $breadcrumbs[] = $item;

        return $breadcrumbs;
    }

    /**
     * @param Page $page
     * @param Page[] $parents
     *
     * @return Page[]
     */
    protected function prepareParents($page, $parents = [])
    {
        if (!$page || !$page->getParent()) {
            return $parents;
        }

        if (!$page->getParent()->getParent() || $page->getParent() === $page) {
            return $parents;
        }

        $parents[] = $page->getParent();

        return $this->prepareParents($page->getParent(), $parents);
    }
}
