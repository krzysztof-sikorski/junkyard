<?php

declare(strict_types=1);

namespace App\DTO;

use App\Contract\Config\AppSerializationGroups;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;

final class PageViewSubmissionResult
{
    #[
        Groups(groups: [AppSerializationGroups::DEFAULT]),
        SerializedName(serializedName: 'status'),
    ]
    private string $status;

    #[
        Groups(groups: [AppSerializationGroups::DEFAULT]),
        SerializedName(serializedName: 'errors'),
    ]
    private array $errors;

    public function __construct(
        string $status,
        array $errors = [],
    ) {
        $this->errors = $errors;
        $this->status = $status;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getErrors(): ?array
    {
        return $this->errors;
    }
}
