<?php

namespace Awesomite\Chariot\Pattern\StdPatterns;

use Awesomite\Chariot\Exceptions\PatternException;
use Awesomite\Chariot\Pattern\Patterns;

class DatePattern extends AbstractPattern
{
    const DATE_FORMAT = 'Y-m-d';

    /**
     * @var \DateTimeZone|null
     */
    private $timezone;

    public function __construct(\DateTimeZone $timezone = null)
    {
        $this->timezone = $timezone;
    }

    public function serialize()
    {
        return serialize($this->timezone);
    }

    public function unserialize($serialized)
    {
        $this->timezone = unserialize($serialized);
    }

    public function getRegex(): string
    {
        return Patterns::REGEX_DATE;
    }

    /**
     * Convert passed argument to date in format YYYY-mm-dd
     *
     * @param int|string|\DateTimeInterface $rawData
     *
     * @return string
     *
     * @throws PatternException
     */
    public function toUrl($rawData): string
    {
        $data = $this->processRawData($rawData);

        if (is_object($data) && $data instanceof \DateTimeInterface) {
            return $data->format(static::DATE_FORMAT);
        }

        if (is_int($data)) {
            return (new \DateTime())->setTimestamp($data)->format(static::DATE_FORMAT);
        }

        if (is_string($data) && $this->match($data)) {
            $sData = (string) $data;
            if ($this->checkDate($sData)) {
                return $sData;
            }
        }

        throw $this->newInvalidToUrl($data);
    }

    private function processRawData($data)
    {
        if (is_object($data)) {
            if ($data instanceof \DateTimeInterface) {
                return $data;
            }

            if (method_exists($data, '__toString')) {
                $data = (string) $data;
            }
        }

        $d = Patterns::DELIMITER;
        if (is_string($data) && preg_match($d . '^(' . Patterns::REGEX_INT . ')$' . $d, $data)) {
            return (int) $data;
        }

        return $data;
    }

    /**
     * Convert date in format YYYY-mm-dd to \DateTimeImmutable object
     *
     * @param string $param
     *
     * @return \DateTimeImmutable
     *
     * @throws PatternException
     */
    public function fromUrl(string $param)
    {
        if ($this->checkDate($param)) {
            return new \DateTimeImmutable($param, $this->timezone);
        }

        throw $this->newInvalidFromUrl($param);
    }

    private function checkDate(string &$input): bool
    {
        list($year, $month, $day) = explode('-', $input);

        return checkdate((int) $month, (int) $day, (int) $year);
    }
}
