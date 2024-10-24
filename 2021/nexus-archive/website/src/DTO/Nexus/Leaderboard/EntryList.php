<?php

declare(strict_types=1);

namespace App\DTO\Nexus\Leaderboard;

use App\Contract\Entity\Nexus\Leaderboard\EntryInterface;
use App\Contract\Entity\Nexus\Leaderboard\EntryListInterface;
use InvalidArgumentException;

use function array_key_exists;
use function array_keys;
use function count;
use function is_int;

final class EntryList implements EntryListInterface
{
    /** @var int current position of iterator cursor */
    private int $iteratorCursor = 0;

    /** @var array array of filled positions */
    private array $positionList = [];

    /** @var array<int, EntryInterface> position=>entry mapping */
    private array $entryDict = [];

    public function current(): ?EntryInterface
    {
        $position = $this->key();
        return $this->offsetGet(offset: $position);
    }

    public function key(): ?int
    {
        if ($this->valid()) {
            return $position = $this->positionList[$this->iteratorCursor];
        }
        return null;
    }

    public function next(): void
    {
        ++$this->iteratorCursor;
    }

    public function rewind(): void
    {
        $this->positionList = array_keys(array: $this->entryDict);
        $this->iteratorCursor = 0;
    }

    public function valid(): bool
    {
        if (array_key_exists(key: $this->iteratorCursor, array: $this->positionList)) {
            $position = $this->positionList[$this->iteratorCursor];
            return $this->offsetExists(offset: $position);
        }
        return false;
    }

    public function count(): int
    {
        return count($this->entryDict);
    }

    public function offsetExists(mixed $offset): bool
    {
        if (false === is_int($offset)) {
            return false; // unsupported offset type
        }
        return array_key_exists(key: $offset, array: $this->entryDict);
    }

    public function offsetGet(mixed $offset): ?EntryInterface
    {
        if ($this->offsetExists(offset: $offset)) {
            return $this->entryDict[$offset];
        }
        return null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (false === is_int(value: $offset)) {
            throw new InvalidArgumentException(message: 'Offset is not an integer');
        }
        if (false === $value instanceof EntryInterface) {
            throw new InvalidArgumentException(message: 'Value is not an instance of EntryInterface');
        }
        $this->entryDict[$offset] = $value;
    }

    public function offsetUnset(mixed $offset): void
    {
        if ($this->offsetExists(offset: $offset)) {
            unset($this->entryDict[$offset]);
        }
    }
}
