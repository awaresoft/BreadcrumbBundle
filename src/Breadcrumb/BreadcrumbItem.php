<?php

namespace Awaresoft\BreadcrumbBundle\Breadcrumb;

/**
 * Class BreadcrumbItem
 * Helps to create breadcrumb item object
 *
 * @author Bartosz Malec <b.malec@awaresoft.pl>
 */
class BreadcrumbItem
{
    /**
     * Name of breadcrumb item.
     *
     * @var string
     */
    protected $name;

    /**
     * Url of breadcrumb item.
     *
     * @var string
     */
    protected $url;

    /**
     * Active status of breadcrumb item.
     *
     * @var bool
     */
    protected $active;

    public function __construct()
    {
        $this->active = false;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @return bool
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * @param bool $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }
}
