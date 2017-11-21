<?php

namespace Awaresoft\BreadcrumbBundle\Exception;

/**
 * Class WrongPositionException
 *
 * @author Bartosz Malec <b.malec@awaresoft.pl>
 */
class WrongPositionException extends \Exception
{
    const MESSAGE = 'Position %s is not acceptable for this block';

    /**
     * WrongPositionException constructor.
     *
     * @param string $position
     * @param int $code
     * @param \Exception|null $previous
     */
    public function __construct($position, $code = 500, \Exception $previous = null)
    {
        $message = sprintf(self::MESSAGE, $position);

        parent::__construct($message, $code, $previous);
    }
}
