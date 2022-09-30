<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ScheduleImport implements ToCollection, WithHeadingrow
{
    protected string $color = "abcdefghijklmnopqrstuvwxyz";
    protected const ROOM_CLASS = "KELAS";
    protected const ROOM_LAB = "LAB";
    protected const COLUMN_STUDENTS_SETS = "students_sets";
    protected const COLUMN_TEACHERS = "teachers";
    protected const COLUMN_ROOM = "room";

    protected int $counterClass = 0;
    protected int $counterLab = 0;

    /**
     * @param Collection $payload
     * @return Collection
     */

    public function initGraph(Collection $payload): Collection
    {
        $payload->sortBy('activity_id');
        $graph = [];

        # init graph with zero
        for ($i = 0; $i < $payload->count(); $i++) {
            for ($j = 0; $j < $payload->count(); $j++) {
                $graph[$i][$j] = 0;
            }
        }

        # create graph
        for ($i = 0; $i < $payload->count(); $i++) {
            for ($j = $i + 1; $j < $payload->count(); $j++) {
                if (
                    $payload[$i][self::COLUMN_STUDENTS_SETS] === $payload[$j][self::COLUMN_STUDENTS_SETS] ||
                    $payload[$i][self::COLUMN_TEACHERS] === $payload[$j][self::COLUMN_TEACHERS]
                ) {
                    $graph[$i][$j] = 1;
                    $graph[$j][$i] = 1;
                }
            }
        }

        return collect($graph);
    }

    public function welshPowell(Collection $payload, Collection $graph, Collection $sortedGraph): Collection
    {
        # This algorithm runs in O(N*N) time complexity
        $colorizedNode = collect();
        $listOfColorizedNode = collect();
        $colorCounterClass = 0;
        $colorCounterLab = 0;

        foreach ($sortedGraph as $node) {
            if ($listOfColorizedNode->contains($node) === false) {
                $listOfColorizedNode->push($node);
                $listOfAdjacentNode = collect();

                if ($payload[$node][self::COLUMN_ROOM] === self::ROOM_CLASS) {
                    $this->counterClass = 1; $this->counterLab = 0;
                    $colorizedNode[$node] = $this->color[$colorCounterClass];
                } else if ($payload[$node][self::COLUMN_ROOM] === self::ROOM_LAB) {
                    $this->counterClass = 0; $this->counterLab = 1;
                    $colorizedNode[$node] = $this->color[$colorCounterLab];
                }

                // process 2
                foreach ($sortedGraph as $adjacentNode) {
                    if (
                        $graph[$node][$adjacentNode] === 0 &&
                        $node !== $adjacentNode &&
                        $this->isNotAdjacent($adjacentNode, $listOfAdjacentNode, $graph) === true &&
                        $listOfColorizedNode->contains($adjacentNode) === false &&
                        $payload[$adjacentNode][self::COLUMN_ROOM] === self::ROOM_CLASS &&
                        $this->counterClass < 28
                    ) {
                        $colorizedNode[$adjacentNode] = $this->color[$colorCounterClass];
                        $listOfColorizedNode->push($adjacentNode);
                        $listOfAdjacentNode->push($adjacentNode);
                        $this->counterClass++;
                    } else if (
                        $graph[$node][$adjacentNode] === 0 &&
                        $node !== $adjacentNode &&
                        $this->isNotAdjacent($adjacentNode, $listOfAdjacentNode, $graph) === true &&
                        $listOfColorizedNode->contains($adjacentNode) === false &&
                        $payload[$adjacentNode][self::COLUMN_ROOM] === self::ROOM_LAB &&
                        $this->counterLab < 10
                    ) {
                        $colorizedNode[$adjacentNode] = $this->color[$colorCounterLab];
                        $listOfColorizedNode->push($adjacentNode);
                        $listOfAdjacentNode->push($adjacentNode);
                        $this->counterLab++;
                    }

                    if ($this->counterClass == 28 && $this->counterLab == 10) {
                        break;
                    }
                }

                $colorCounterClass++;
                $colorCounterLab++;
            }
        }

        foreach ($payload as $key => $value) {
            $payload[$key]['color'] = $colorizedNode[$key];
        }

        return collect($payload);
    }

    public function isNotAdjacent($x, $list, $graph): bool
    {
        foreach ($list as $key => $value) {
            if ($graph[$x][$value] === 1) {
                return false;
            }
        }
        return true;
    }

    public function collection(Collection $collection): Collection
    {
        $graph = $this->initGraph($collection);

        # sum of all vertex degree
        $counter = $graph->map(fn(array $data) => count(array_filter($data, static fn($value) => (bool) $value)));

        # sort the vertex with the highest degree
        $sortedGraph = $counter->sortDesc()->keys();

        return $this->welshPowellResult = $this->welshPowell($collection, $graph, $sortedGraph);
    }
}
