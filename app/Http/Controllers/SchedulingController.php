<?php

namespace App\Http\Controllers;

use App\Imports\ScheduleImport;
use App\Models\Scheduling;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class SchedulingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     */
    public function index(): \Inertia\Response
    {
        $pageTitle = 'Simulasi Penjawalan';

        return Inertia::render($this->component('Index'), [
            'pageTitle' => $pageTitle,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Inertia\Response
     */
    public function store(Request $request): \Inertia\Response
    {
        $actionImport = new ScheduleImport();
        Excel::import($actionImport, $request->file('file'));

        return Inertia::render($this->component('Index'), [
            'pageTitle' => 'Simulasi Penjawalan',
            'data' => $actionImport->welshPowellResult,
        ]);
    }

    private function component(string $name): string
    {
        return 'Scheduling/' . $name;
    }
}
