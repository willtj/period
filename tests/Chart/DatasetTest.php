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

namespace LeagueTest\Period\Chart;

use ArrayObject;
use DateTimeImmutable;
use League\Period\Chart\Dataset;
use League\Period\Chart\DecimalNumber;
use League\Period\Chart\LatinLetter;
use League\Period\Period;
use League\Period\Sequence;
use PHPUnit\Framework\TestCase;
use TypeError;
use function iterator_to_array;
use function json_encode;

/**
 * @coversDefaultClass \League\Period\Chart\Dataset;
 */
final class DatasetTest extends TestCase
{
    public function testFromSequenceConstructor(): void
    {
        $periodA = Period::fromDay(2018, 3, 15);
        $periodB = Period::fromDay(2019, 3, 15);
        $labelGenerator = new LatinLetter('A');
        $sequence = new Sequence($periodA, $periodB);
        $dataset = Dataset::fromItems($sequence, $labelGenerator);
        $arr = iterator_to_array($dataset);

        self::assertCount(2, $dataset);
        self::assertSame('B', $arr[1][0]);
        self::assertTrue($periodB->equals($arr[1][1][0]));

        $emptyDataset = Dataset::fromItems(new Sequence(), $labelGenerator);
        self::assertCount(0, $emptyDataset);
        self::assertTrue($emptyDataset->isEmpty());
    }

    /**
     * @dataProvider provideIterableStructure
     */
    public function testFromIterableConstructor(iterable $input, int $expectedCount, bool $isEmpty, bool $boundaryIsNull): void
    {
        $dataset = Dataset::fromIterable($input);
        self::assertCount($expectedCount, $dataset);
        self::assertSame($isEmpty, $dataset->isEmpty());
        self::assertSame($boundaryIsNull, null === $dataset->boundaries());
    }

    public function provideIterableStructure(): iterable
    {
        return [
            'empty structure' => [
                'input' => [],
                'expectedCount' => 0,
                'isEmpty' => true,
                'boundaryIsNull' => true,
            ],
            'single array' => [
                'input' => [Period::fromDay(2019, 3, 15)],
                'expectedCount' => 1,
                'isEmpty' => false,
                'boundaryIsNull' => false,
            ],
            'using an iterator' => [
                'input' => new ArrayObject([Period::fromDay(2019, 3, 15)]),
                'expectedCount' => 1,
                'isEmpty' => false,
                'boundaryIsNull' => false,
            ],
            'using a direct sequence' => [
                'input' => new Sequence(
                    Period::fromDay(2018, 9, 10),
                    Period::fromDay(2019, 10, 11)
                ),
                'expectedCount' => 2,
                'isEmpty' => false,
                'boundaryIsNull' => false,
            ],
            'using a wrapped sequence' => [
                'input' => [new Sequence(
                    Period::fromDay(2018, 9, 10),
                    Period::fromDay(2019, 10, 11)
                )],
                'expectedCount' => 1,
                'isEmpty' => false,
                'boundaryIsNull' => false,
            ],
        ];
    }

    public function testAppendDataset(): void
    {
        $dataset = new Dataset([
            ['A', new Sequence(new Period('2018-01-01', '2018-01-15'))],
            ['B', new Period('2018-01-15', '2018-02-01')],
        ]);

        self::assertCount(2, $dataset);
    }

    public function testAppendDatasetThrowWithInvalidLabel(): void
    {
        self::expectException(TypeError::class);

        new Dataset([[new DateTimeImmutable(), Period::around('2018-01-15', '1 DAY')]]);
    }

    public function testAppendDatasetThrowWithInvalidItem(): void
    {
        self::expectException(TypeError::class);

        new Dataset([['foo', 'bar']]);
    }

    public function testLabelizeDataset(): void
    {
        $dataset = new Dataset([
            ['A', new Sequence(new Period('2018-01-01', '2018-01-15'))],
            ['B', new Sequence(new Period('2018-01-15', '2018-02-01'))],
        ]);
        self::assertSame(['A', 'B'], $dataset->labels());
        self::assertSame(1, $dataset->labelMaxLength());

        $newDataset = $dataset->withLabels(new DecimalNumber(42));
        self::assertSame(['42', '43'], $newDataset->labels());
        self::assertSame($dataset->items(), $newDataset->items());
        self::assertSame(2, $newDataset->labelMaxLength());
    }

    public function testLabelizeDatasetReturnsSameInstance(): void
    {
        $dataset = new Dataset([
            ['A', new Sequence(new Period('2018-01-01', '2018-01-15'))],
            ['B', new Sequence(new Period('2018-01-15', '2018-02-01'))],
        ]);

        self::assertEquals($dataset, $dataset->withLabels(new LatinLetter()));

        $emptyDataset = new Dataset();
        self::assertEquals($emptyDataset, $emptyDataset->withLabels(new DecimalNumber(42)));
    }

    public function testEmptyInstance(): void
    {
        $dataset = new Dataset();
        self::assertSame(0, $dataset->labelMaxLength());
        self::assertSame([], $dataset->items());
        self::assertSame([], $dataset->labels());
    }

    public function testJsonEncoding(): void
    {
        self::assertSame('[]', json_encode(new Dataset()));
        $dataset = new Dataset([
            ['A', new Sequence(new Period('2018-01-01', '2018-01-15'))],
            ['B', new Sequence(new Period('2018-01-15', '2018-02-01'))],
        ]);

        self::assertStringContainsString('label', (string) json_encode($dataset));
    }
}
