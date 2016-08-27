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

    /**
     * ContextNotAvailableException constructor.
     *
     * @param string $context
     * @param int $code
     * @param \Exception|null $previous
     */
    public function __construct($context, $code = 500, \Exception $previous = null)
    {
        $message = sprintf(self::MESSAGE, $context);

        parent::__construct($message, $code, $previous);
    }
}
