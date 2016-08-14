<?php

namespace Awaresoft\BreadcrumbBundle\Exception;

/**
 * Class ContextNotAvailableException
 *
 * @author Bartosz Malec <b.malec@awaresoft.pl>
 */
class ContextNotAvailableException extends \Exception
{
    const MESSAGE = 'Context %s is not available.';

    /**
     * @var string
     */
    protected $context;

    public function __construct($context, $code = 500, \Exception $previous = null)
    {
        $this->context = $context;
        $message = sprintf(self::MESSAGE, $context);

        parent::__construct($message, $code, $previous);
    }

    public function getContext()
    {
        return $this->context;
    }
}
