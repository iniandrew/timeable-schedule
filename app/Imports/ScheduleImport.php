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

    /**
     * @param Collection $rows
     * @return Collection
     */

    public function collection(Collection $rows)
    {
        $payload = $rows->sortBy('activity_id');
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
                    $payload[$i]['students_sets'] === $payload[$j]['students_sets'] ||
                    $payload[$i]['teachers'] === $payload[$j]['teachers']
                ) {
                    $graph[$i][$j] = 1;
                    $graph[$j][$i] = 1;
                }
            }
        }

        $payloadGraph = collect($graph);

        # sum of all vertex degree
        $counter = $payloadGraph->map(fn(array $data) => count(array_filter($data, static fn($value) => (bool) $value)));

        # sort the vertex with the highest degree
        $sortedGraph = $counter->sortDesc()->keys();

        # This algorithm runs in O(N*N) time complexity

        $colorizedNode = collect();
        $listOfColorizedNode = collect();
        $colorCounterClass = 0;
        $colorCounterLab = 0;

        foreach ($sortedGraph as $node) {
            if ($listOfColorizedNode->contains($node) === false) {
                $listOfColorizedNode->push($node);
                $listOfAdjacentNode = collect();

                if ($payload[$node]['room'] === self::ROOM_CLASS) {
                    $counterClass = 1; $counterLab = 0;
                    $colorizedNode[$node] = $this->color[$colorCounterClass];
                } else if ($payload[$node]['room'] === self::ROOM_LAB) {
                    $counterClass = 0; $counterLab = 1;
                    $colorizedNode[$node] = $this->color[$colorCounterLab];
                }

                // process 2
                foreach ($sortedGraph as $adjacentNode) {
                    if (
                        $payloadGraph[$node][$adjacentNode] === 0 &&
                        $node !== $adjacentNode &&
                        $this->isNotAdjacent($adjacentNode, $listOfAdjacentNode, $payloadGraph) === true &&
                        $listOfColorizedNode->contains($adjacentNode) === false &&
                        $payload[$adjacentNode]['room'] === self::ROOM_CLASS &&
                        $counterClass < 28
                    ) {
                        $colorizedNode[$adjacentNode] = $this->color[$colorCounterClass];
                        $listOfColorizedNode->push($adjacentNode);
                        $listOfAdjacentNode->push($adjacentNode);
                        $counterClass++;
                    } else if (
                        $payloadGraph[$node][$adjacentNode] === 0 &&
                        $node !== $adjacentNode &&
                        $this->isNotAdjacent($adjacentNode, $listOfAdjacentNode, $payloadGraph) === true &&
                        $listOfColorizedNode->contains($adjacentNode) === false &&
                        $payload[$adjacentNode]['room'] === self::ROOM_LAB &&
                        $counterLab < 10
                    ) {
                        $colorizedNode[$adjacentNode] = $this->color[$colorCounterLab];
                        $listOfColorizedNode->push($adjacentNode);
                        $listOfAdjacentNode->push($adjacentNode);
                        $counterLab++;
                    }

                    if ($counterClass === 28 && $counterLab === 10) {
                        break;
                    }
                }

                #listOfAdjacentNode *= 0
                $colorCounterClass++;
                $colorCounterLab++;
            }
        }

        foreach ($payload as $key => $value) {
            $payload[$key]['color'] = $colorizedNode[$key];
        }

        $this->payload = $payload;
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
}
