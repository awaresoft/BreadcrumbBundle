<?php

namespace Awaresoft\BreadcrumbBundle\Breadcrumb;

/**
 * Interface BreadcrumbInterface
 *
 * @author Bartosz Malec <b.malec@awaresoft.pl>
 */
interface BreadcrumbInterface
{
    /**
     * Create breadcrumb for selected bundle class
     *
     * @return BreadcrumbItem[]
     */
    public function create();
}