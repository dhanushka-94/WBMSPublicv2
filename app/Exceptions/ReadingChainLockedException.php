<?php

namespace App\Exceptions;

use InvalidArgumentException;

/**
 * Thrown when saving a reading or bill would change the reading of a later bill that is already
 * paid or cancelled. The controller shows the message and rolls back the whole edit.
 */
class ReadingChainLockedException extends InvalidArgumentException
{
}
