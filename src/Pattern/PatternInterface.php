<?php

namespace Awesomite\Chariot\Pattern;

use Awesomite\Chariot\Exceptions\PatternException;

interface PatternInterface extends \Serializable
{
    /**
     * Returns regex without delimiters
     *
     * @return string
     */
    public function getRegex(): string;

    /**
     * @param mixed $data
     *
     * @return string
     *
     * @throws PatternException
     */
    public function toUrl($data): string;

    /**
     * Passed argument must be validated by regex earlier
     *
     * @param string $param
     *
     * @return mixed
     *
     * @throws PatternException
     *
     * @see PatternInterface::getRegex()
     */
    public function fromUrl(string $param);
}
