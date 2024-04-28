<?php

namespace DigitalTunnel\Invoice\Enums;

enum InvoiceSchemeType: string
{
    case Year = 'year';
    case Prefix = 'prefix';
}
