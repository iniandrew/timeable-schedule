<?php

namespace App\Http\Controllers;

use App\Imports\ScheduleImport;
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
        // TODO: Add validation

        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
            'max_class' => 'required|numeric',
            'max_lab' => 'required|numeric',
        ]);

        $actionImport = new ScheduleImport($request);
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
