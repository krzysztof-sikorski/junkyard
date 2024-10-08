<?php

declare(strict_types=1);

namespace App\DTO;

final class PageViewSubmissionValidatorResult
{
    public function __construct(
        private bool $isValid,
        private ?array $errors,
    ) {
    }

    public function isValid(): bool
    {
        return $this->isValid;
    }

    public function getErrors(): ?array
    {
        return $this->errors;
    }
}
