<?php

namespace App\Imports;

use App\Models\Room;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class RoomImport implements ToCollection
{
    /**
     * @param Collection $collection
     */

    public function collection(Collection $collection): void
    {
        foreach ($collection as $index => $item) {
            if ($index === 0) continue;
            else {
                Room::query()->create([
                    'name' => $item[0],
                    'capacity' => $item[1],
                    'building' => $item[2],
                ]);
            }
        }
    }
}
