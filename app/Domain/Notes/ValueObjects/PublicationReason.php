<?php

declare(strict_types=1);

namespace App\Domain\Notes\ValueObjects;

use App\Domain\Notes\Enums\PublicationReasonType;
use App\Domain\Notes\Exceptions\InvalidPublicationReasonMessage;

final readonly class PublicationReason
{
    public const int MAX_MESSAGE_LENGTH = 80;

    private const string URL_PATTERN = '/(?:https?:\/\/|www\.)/i';

    private string $message;

    public function __construct(private PublicationReasonType $publicationReasonType, string $message)
    {
        $normalizedMessage = trim($message);

        if ($normalizedMessage === '') {
            throw InvalidPublicationReasonMessage::becauseItIsEmpty();
        }

        if (mb_strlen($normalizedMessage) > self::MAX_MESSAGE_LENGTH) {
            throw InvalidPublicationReasonMessage::becauseItExceedsAllowedLength(self::MAX_MESSAGE_LENGTH);
        }

        if (preg_match(self::URL_PATTERN, $normalizedMessage) === 1) {
            throw InvalidPublicationReasonMessage::becauseItContainsUrl();
        }

        $this->message = $normalizedMessage;
    }

    public static function fromNullable(?PublicationReasonType $publicationReasonType, ?string $message): ?self
    {
        if (!$publicationReasonType instanceof PublicationReasonType && $message === null) {
            return null;
        }

        if (!$publicationReasonType instanceof PublicationReasonType || $message === null) {
            return null;
        }

        return new self($publicationReasonType, $message);
    }

    public function type(): PublicationReasonType
    {
        return $this->publicationReasonType;
    }

    public function message(): string
    {
        return $this->message;
    }

    public function matchesTitle(string $title): bool
    {
        return mb_strtolower($this->message) === mb_strtolower(trim($title));
    }
}
