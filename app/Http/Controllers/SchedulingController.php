<?php

namespace App\Http\Controllers;

use App\Actions\App\SchedulingAction;
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
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Inertia\Response
     */
    public function store(Request $request, SchedulingAction $action): \Inertia\Response
    {
        $import = new ScheduleImport();
        Excel::import($import, $request->file('file'));

        return Inertia::render($this->component('Index'), [
            'pageTitle' => 'Simulasi Penjawalan',
            'data' => $import->payload,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Scheduling  $scheduling
     * @return \Illuminate\Http\Response
     */
    public function show(Scheduling $scheduling)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Scheduling  $scheduling
     * @return \Illuminate\Http\Response
     */
    public function edit(Scheduling $scheduling)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Scheduling  $scheduling
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Scheduling $scheduling)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Scheduling  $scheduling
     * @return \Illuminate\Http\Response
     */
    public function destroy(Scheduling $scheduling)
    {
        //
    }

    private function component(string $name): string
    {
        return 'Scheduling/' . $name;
    }
}
