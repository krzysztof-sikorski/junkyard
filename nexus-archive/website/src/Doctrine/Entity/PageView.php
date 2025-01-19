<?php

declare(strict_types=1);

namespace App\Doctrine\Entity;

use App\Contract\Doctrine\Entity\DatedEntityInterface;
use App\Contract\Doctrine\Entity\UuidPrimaryKeyInterface;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[
    ORM\Entity(),
    ORM\Table(name: 'page_view'),
    ORM\Index(columns: ['created_at', 'request_started_at', 'id'], name: 'page_view_sorting_idx'),
    ORM\Index(columns: ['owner_id'], name: 'page_view_owner_idx'),
]
class PageView implements UuidPrimaryKeyInterface, DatedEntityInterface
{
    use UuidPrimaryKeyTrait;
    use DatedEntityTrait;

    #[
        ORM\ManyToOne(targetEntity: User::class),
        ORM\JoinColumn(name: 'owner_id', referencedColumnName: 'id', nullable: false),
    ]
    private ?User $owner = null;

    #[ORM\Column(name: 'request_started_at', type: Types::DATETIMETZ_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $requestStartedAt = null;

    #[ORM\Column(name: 'response_completed_at', type: Types::DATETIMETZ_IMMUTABLE, nullable: true)]
    private ?DateTimeImmutable $responseCompletedAt = null;

    #[ORM\Column(name: 'method', type: Types::TEXT, nullable: false)]
    private ?string $method = null;

    #[ORM\Column(name: 'url', type: Types::TEXT, nullable: false)]
    private ?string $url = null;

    #[ORM\Column(name: 'form_data', type: Types::JSON, nullable: true)]
    private mixed $formData = null;

    #[ORM\Column(name: 'response_body', type: Types::TEXT, nullable: false)]
    private ?string $responseBody = null;

    #[ORM\Column(name: 'parsed_at', type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $parsedAt = null;

    #[ORM\Column(name: 'parser_errors', type: Types::JSON, nullable: true)]
    private ?array $parserErrors = null;

    public function __construct()
    {
        $this->generateId();
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): void
    {
        $this->owner = $owner;
    }

    public function getRequestStartedAt(): ?DateTimeInterface
    {
        return $this->requestStartedAt;
    }

    public function setRequestStartedAt(?DateTimeInterface $requestStartedAt): void
    {
        $this->requestStartedAt = DateTimeImmutable::createFromInterface(object: $requestStartedAt);
    }

    public function getResponseCompletedAt(): ?DateTimeInterface
    {
        return $this->responseCompletedAt;
    }

    public function setResponseCompletedAt(?DateTimeInterface $responseCompletedAt): void
    {
        $this->responseCompletedAt = DateTimeImmutable::createFromInterface(object: $responseCompletedAt);
    }

    public function getMethod(): ?string
    {
        return $this->method;
    }

    public function setMethod(?string $method): void
    {
        $this->method = $method;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): void
    {
        $this->url = $url;
    }

    public function getFormData(): mixed
    {
        return $this->formData;
    }

    public function setFormData(mixed $formData): void
    {
        $this->formData = $formData;
    }

    /**
     * @return string|null
     */
    public function getResponseBody(): ?string
    {
        return $this->responseBody;
    }

    /**
     * @param string|null $responseBody
     */
    public function setResponseBody(?string $responseBody): void
    {
        $this->responseBody = $responseBody;
    }

    public function getParsedAt(): ?DateTimeInterface
    {
        return $this->parsedAt;
    }

    public function setParsedAt(?DateTimeInterface $parsedAt): void
    {
        $this->parsedAt = DateTimeImmutable::createFromInterface(object: $parsedAt);
    }

    public function getParserErrors(): ?array
    {
        return $this->parserErrors;
    }

    public function setParserErrors(?array $parserErrors): void
    {
        $this->parserErrors = $parserErrors;
    }
}
