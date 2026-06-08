<?php

namespace Core\Doctrine\DBAL\Types;

use DateTime;
use DateTimeZone;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\DateTimeType;

class TimestampType extends DateTimeType
{
    private const TYPE_NAME = 'timestamp';

    /**
     * @param mixed|\DateTime|null $value
     * @param AbstractPlatform     $platform
     *
     * @return string|null
     *
     * @throws ConversionException
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if (null === $value) {
            return null;
        }

        if ($value instanceof DateTime) {
            $value = clone $value;

            return $value
                ->setTimezone($this->getUTCDateTimeZone())
                ->format($platform->getDateTimeFormatString());
        }

        throw ConversionException::conversionFailedInvalidType($value, self::TYPE_NAME, ['null', 'DateTime']);
    }

    /**
     * @param string|\DateTime|null $value
     * @param AbstractPlatform      $platform
     *
     * @return \DateTime|null
     *
     * @throws ConversionException
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): ?DateTime
    {
        if (null === $value) {
            return null;
        }

        if ($value instanceof DateTime) {
            $value = clone $value;

            return $value->setTimezone($this->getUTCDateTimeZone());
        }

        $convertedValue = DateTime::createFromFormat(
            $platform->getDateTimeFormatString(),
            $value,
            $this->getUTCDateTimeZone()
        );

        if (false === $convertedValue) {
            throw ConversionException::conversionFailedFormat(
                $value,
                self::TYPE_NAME,
                $platform->getDateTimeFormatString()
            );
        }

        return $convertedValue;
    }

    public function getName(): string
    {
        return self::TYPE_NAME;
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }

    private function getUTCDateTimeZone(): DateTimeZone
    {
        return new DateTimeZone('UTC');
    }
}
