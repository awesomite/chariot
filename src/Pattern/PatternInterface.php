<?php

namespace Awesomite\Chariot\Pattern;

use Awesomite\Chariot\Exceptions\PatternException;

interface PatternInterface extends \Serializable
{
    /**
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
     * @param string $param
     *
     * @return mixed
     *
     * @throws PatternException
     */
    public function fromUrl(string $param);
}
