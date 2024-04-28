<?php

namespace DigitalTunnel\Invoice\Exceptions;

use Exception;

class InvalidInvoiceTypeException extends Exception
{
    /**
     * Report the exception.
     */
    public function report(): void
    {
        // report the exception
    }
}
