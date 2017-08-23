<?php

namespace Awesomite\Chariot\Exceptions;

use Awesomite\Chariot\Pattern\PatternInterface;

/**
 * May be thrown when:
 * - invalid parameter is passed to PatternInterface::fromUrl()
 * - invalid parameter is passed to PatternInterface::toUrl()
 *
 * @see PatternInterface::fromUrl()
 * @see PatternInterface::toUrl()
 */
class PatternException extends InvalidArgumentException
{
    const CODE_TO_URL   = 1;
    const CODE_FROM_URL = 2;
}
