<?php

namespace Awaresoft\BreadcrumbBundle\Exception;

/**
 * Class ContextNotFoundException
 *
 * @author Bartosz Malec <b.malec@awaresoft.pl>
 */
class ContextNotFoundException extends \Exception
{
    const MESSAGE = 'Context %s not found. Please check breadcrumbs configuration.';

    /**
     * @var string
     */
    protected $context;

    public function __construct($context, $code = 500, \Exception $previous = null)
    {
        $this->context = $context;
        $message = sprintf(self::MESSAGE, $this->context);

        parent::__construct($message, $code, $previous);
    }

    public function getContext()
    {
        return $this->context;
    }
}
