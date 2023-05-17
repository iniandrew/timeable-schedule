<?php

namespace App\Imports;

use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * @property Collection $welshPowellResult
 */

class ScheduleImport implements ToCollection, WithHeadingrow
{
    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->maxClass = $request->input('max_class') ?? 28; // default is 28
        $this->maxLab = $request->input('max_lab') ?? 10; // default is 10
    }

    protected const ROOM_CLASS = "KELAS";
    protected const ROOM_LAB = "LAB";
    protected const COLUMN_STUDENTS_SETS = "students_sets";
    protected const COLUMN_TEACHERS = "teachers";
    protected const COLUMN_ROOM = "room";

    protected int $counterClass = 0;
    protected int $counterLab = 0;
    protected array $scheduleList = [
        'Senin, 07:00 - 09.50',
        'Senin, 10.00 - 12.50',
        'Senin, 13.00 - 15.50',
        'Selasa, 07:00 - 09.50',
        'Selasa, 10.00 - 12.50',
        'Selasa, 13.00 - 15.50',
        'Rabu, 07:00 - 09.50',
        'Rabu, 10.00 - 12.50',
        'Rabu, 13.00 - 15.50',
        'Kamis, 07:00 - 09.50',
        'Kamis, 10.00 - 12.50',
        'Jum\'at, 07.00 - 09.50',
        'Jum\'at, 10.00 - 12.50',
        'Jum\'at, 13.00 - 15.50',
        'Unscheduled',
    ];


    /**
     * @param Collection $payload
     * @return Collection
     */

    public function initGraph(Collection $payload): Collection
    {
        $payload->sortBy('activity_id');
        $graph = [];

        # Initialize the graph with zeros
        for ($i = 0; $i < $payload->count(); $i++) {
            for ($j = 0; $j < $payload->count(); $j++) {
                $graph[$i][$j] = 0;
            }
        }

        # Assign value to the adjacency matrix by the connectivity of the nodes
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

    // Untuk kelas yang jadwanya sama maka kelasnya berbeda, based on schedules id
    public function determineRoom(Collection $payload): string
    {
        $selectedRoom = '';
        $roomType = match ($payload['room']) {
            self::ROOM_CLASS => 'KTT',
            self::ROOM_LAB => 'LAB',
        };

        $filteredRoom = Room::query()->where('name', 'like', $roomType . '%')->get()->toArray();

        foreach ($this->scheduleList as $key => $schedule) {
            if ($payload['schedules'] === $key) {
                $selectedRoom = $filteredRoom[random_int(0, count($this->scheduleList) - 1)]['name'];
            }
        }

        return $selectedRoom;
    }

    # Main Process
    /**
        *Constraint:
        1. Two or more activities can't share the same teacher or the same students sets in a single schedule
        2. Maximum number of classroom and laboratorium used in a single schedule is 28 and 10, respectively
     */
    public function welshPowell(Collection $payload, Collection $graph, Collection $sortedGraph): Collection
    {
        # This algorithm runs in O(N*N) time complexity
        $colorizedNode = collect();
        $listOfColorizedNode = collect();
        $colorClass = 0; # Color for Class
        $colorLab = 0; # Color For Laboratory

        foreach ($sortedGraph as $node) {
            if ($listOfColorizedNode->contains($node) === false) {
                $listOfColorizedNode->push($node);
                $listOfAdjacentNode = collect();

                if ($payload[$node][self::COLUMN_ROOM] === self::ROOM_CLASS) {
                    $this->counterClass = 1; $this->counterLab = 0;
                    $colorizedNode[$node] = $colorClass;
                } else if ($payload[$node][self::COLUMN_ROOM] === self::ROOM_LAB) {
                    $this->counterClass = 0; $this->counterLab = 1;
                    $colorizedNode[$node] = $colorLab;
                }

                // process 2
                foreach ($sortedGraph as $adjacentNode) {
                    if (
                        $graph[$node][$adjacentNode] === 0 &&
                        $node !== $adjacentNode &&
                        $this->isNotAdjacent($adjacentNode, $listOfAdjacentNode, $graph) === true &&
                        $listOfColorizedNode->contains($adjacentNode) === false &&
                        $payload[$adjacentNode][self::COLUMN_ROOM] === self::ROOM_CLASS &&
                        $this->counterClass < $this->maxClass
                    ) {
                        $colorizedNode[$adjacentNode] = $colorClass;
                        $listOfColorizedNode->push($adjacentNode);
                        $listOfAdjacentNode->push($adjacentNode);
                        $this->counterClass++;
                    } else if (
                        $graph[$node][$adjacentNode] === 0 &&
                        $node !== $adjacentNode &&
                        $this->isNotAdjacent($adjacentNode, $listOfAdjacentNode, $graph) === true &&
                        $listOfColorizedNode->contains($adjacentNode) === false &&
                        $payload[$adjacentNode][self::COLUMN_ROOM] === self::ROOM_LAB &&
                        $this->counterLab < $this->maxLab
                    ) {
                        $colorizedNode[$adjacentNode] = $colorLab;
                        $listOfColorizedNode->push($adjacentNode);
                        $listOfAdjacentNode->push($adjacentNode);
                        $this->counterLab++;
                    }

                    # If the counter has reached the maximum input, then break the loop
                    if ($this->counterClass === $this->maxClass && $this->counterLab === $this->maxLab) {
                        break;
                    }
                }

                $colorClass++;
                $colorLab++;
            }
        }

        # Activity is the colorized node, schedule is the color of a node
        foreach ($payload as $key => $value) {
            $payload[$key]['schedules'] = $colorizedNode[$key];
            $payload[$key]['schedule_time'] = $this->scheduleList[$colorizedNode[$key]];
            $payload[$key]['room'] = $this->determineRoom($value);
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

    # Main function
    public function collection(Collection $collection): Collection
    {
        $graph = $this->initGraph($collection);

        # Calculate the degree of each node, here's node as the key and its degree as the value of dictionary
        $nodeDegree = $graph->map(fn(array $data) => count(array_filter($data, static fn($value) => (bool) $value)));

        # Sort the nodes by its degree in descending order
        $sortedNode = $nodeDegree->sortDesc()->keys();

        return $this->welshPowellResult = $this->welshPowell($collection, $graph, $sortedNode);
    }
}
