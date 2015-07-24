<?php
/* Icinga Web 2 | (c) 2013-2015 Icinga Development Team | GPLv2+ */

namespace Icinga\Exception;

use Exception;

class IcingaException extends Exception
{
    /**
     * @param string $message   format string for vsprintf()
     * Any futher args:         args for vsprintf()
     * @see vsprintf
     *
     * If there is at least one exception, the last one will be also used for the exception chaining.
     */
    public function __construct($message = '')
    {
        $args = array_slice(func_get_args(), 1);
        $exc = null;
        foreach ($args as &$arg) {
            if ($arg instanceof Exception) {
                $exc = $arg;
            }
        }
        parent::__construct(vsprintf($message, $args), 0, $exc);
    }

    /**
     * Return the given exception formatted as one-liner
     *
     * The format used is: %class% in %path%:%line% with message: %message%
     *
     * @param   Exception   $exception
     *
     * @return  string
     */
    public static function describe(Exception $exception)
    {
        return sprintf(
            '%s in %s:%d with message: %s',
            get_class($exception),
            $exception->getFile(),
            $exception->getLine(),
            $exception->getMessage()
        );
    }
}
