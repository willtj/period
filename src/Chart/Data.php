<?php

/**
 * League.Period (https://period.thephpleague.com)
 *
 * (c) Ignace Nyamagana Butera <nyamsprod@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace League\Period\Chart;

use League\Period\Period;
use League\Period\Sequence;

interface Data extends \Countable, \IteratorAggregate, \JsonSerializable
{
    /**
     * Returns the number of pairs.
     */
    public function count(): int;

    /**
     * Returns the pairs.
     *
     * @return \Iterator<int, array{0: string, 1: Sequence}>
     */
    public function getIterator(): \Iterator;

    /**
     * @var array<int, array{label:string, item:Sequence}>.
     */
    public function jsonSerialize(): array;

    /**
     * Tells whether the instance is empty.
     */
    public function isEmpty(): bool;

    /**
     * @return string[]
     */
    public function labels(): iterable;

    /**
     * @return Sequence[]
     */
    public function items(): iterable;

    /**
     * Returns the dataset boundaries.
     */
    public function boundaries(): ?Period;

    /**
     * Returns the label maximum length.
     */
    public function labelMaxLength(): int;

    /**
     * Add a new pair to the collection.
     *
     * @param mixed           $label a scalar or a stringable object (implementing __toString method).
     * @param Period|Sequence $item
     *
     * @throws \TypeError If the label or the item type are not supported.
     */
    public function append($label, $item): void;

    /**
     * Add a collection of pairs to the collection.
     */
    public function appendAll(iterable $pairs): void;

    /**
     * Update the labels used for the dataset.
     *
     * This method MUST retain the state of the current instance, and return
     * an instance that contains the newly specified labels.
     */
    public function withLabels(LabelGenerator $labelGenerator): self;
}
