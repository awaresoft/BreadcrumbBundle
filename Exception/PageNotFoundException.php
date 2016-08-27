<?php

namespace Awaresoft\BreadcrumbBundle\Exception;

/**
 * Class PageNotFoundException
 *
 * @author Bartosz Malec <b.malec@awaresoft.pl>
 */
class PageNotFoundException extends \Exception
{
    const MESSAGE = 'Selected page %s does not support breadcrumbs. Please add dedicated breadcrumb type for this page.';

    /**
     * PageNotFoundException constructor.
     *
     * @param string $page
     * @param int $code
     * @param \Exception|null $previous
     */
    public function __construct($page, $code = 500, \Exception $previous = null)
    {
        $message = sprintf(self::MESSAGE, $page);

        parent::__construct($message, $code, $previous);
    }
}
