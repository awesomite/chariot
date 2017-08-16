<?php

namespace Awesomite\Chariot\Pattern\StdPatterns;

use Awesomite\Chariot\Exceptions\PatternException;

class PatternDate extends AbstractPattern
{
    const DATE_FORMAT = 'Y-m-d';

    public function getRegex(): string
    {
        return '[0-9]{4}-[0-9]{2}-[0-9]{2}';
    }

    /**
     * @param int|string|\DateTimeInterface $data
     *
     * @return string
     *
     * @throws PatternException
     */
    public function toUrl($data): string
    {
        if (is_int($data)) {
            return (new \DateTime())->setTimestamp($data)->format(static::DATE_FORMAT);
        }

        if (is_object($data) && $data instanceof \DateTimeInterface) {
            return $data->format(static::DATE_FORMAT);
        }

        if (
            (is_string($data) || (is_object($data) && method_exists($data, '__toString')))
            && preg_match('#^' . $this->getRegex() . '$#', $data)
        ) {
            $sData = (string) $data;
            if ($this->checkDate($sData)) {
                return $sData;
            }
        }

        throw $this->newInvalidToUrl($data);
    }

    /**
     * @param string $param
     *
     * @return \DateTimeImmutable
     *
     * @throws PatternException
     */
    public function fromUrl(string $param)
    {
        if ($this->checkDate($param)) {
            return new \DateTimeImmutable($param);
        }

        throw $this->newInvalidFromUrl($param);
    }

    private function checkDate(string &$input): bool
    {
        list($year, $month, $day) = explode('-', $input);

        return checkdate((int) $month, (int) $day, (int) $year);
    }
}
