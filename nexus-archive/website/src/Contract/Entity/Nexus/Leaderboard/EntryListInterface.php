<?php

declare(strict_types=1);

namespace App\Contract\Entity\Nexus\Leaderboard;

use ArrayAccess;
use Countable;
use InvalidArgumentException;
use Iterator;

/**
 * Sorted collection of leaderboard entries.
 */
interface EntryListInterface extends Iterator, ArrayAccess, Countable
{
    /** Iterator interface: Return the current element */
    public function current(): ?EntryInterface;

    /** Iterator interface: Return the key of the current element */
    public function key(): ?int;

    /** Iterator interface: Move forward to next element */
    public function next(): void;

    /** Iterator interface: Rewind the Iterator to the first element */
    public function rewind(): void;

    /** Iterator interface: Checks if current position is valid */
    public function valid(): bool;

    /** ArrayAccess interface: Whether an offset exists */
    public function offsetExists(mixed $offset): bool;

    /** ArrayAccess interface: Offset to retrieve */
    public function offsetGet(mixed $offset): ?EntryInterface;

    /**
     * ArrayAccess interface: Assign a value to the specified offset
     *
     * @throws InvalidArgumentException when $offset ins not an integer
     * @throws InvalidArgumentException when $value is not an instance of EntryInterface
     */
    public function offsetSet(mixed $offset, mixed $value): void;

    /** ArrayAccess interface: Unset an offset */
    public function offsetUnset(mixed $offset): void;

    /** Countable interface: Count elements of an object */
    public function count(): int;
}
