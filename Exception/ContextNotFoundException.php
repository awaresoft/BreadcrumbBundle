<?php

namespace Awaresoft\BreadcrumbBundle\Exception;

/**
 * Class ContextNotFoundException
 *
 * @author Bartosz Malec <b.malec@awaresoft.pl>
 */
class ContextNotFoundException extends BaseBreadcrumbException
{
    const MESSAGE = 'Context %s not found. Please check breadcrumbs configuration.';

    /**
     * @var string
     */
    protected $context;

    /**
     * ContextNotFoundException constructor.
     *
     * @param string $context
     * @param int $code
     * @param \Exception|null $previous
     */
    public function __construct($context, $code = 500, \Exception $previous = null)
    {
        $message = sprintf(self::MESSAGE, $this->context);

        parent::__construct($message, $code, $previous);
    }
}
