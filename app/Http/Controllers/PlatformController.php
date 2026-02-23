<?php

namespace App\Http\Controllers;

use App\Http\Requests\PlatformRequest;
use App\Models\Platform;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PlatformController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $platforms = Platform::query()->latest()->get();

        return view('platforms.index', compact('platforms'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('platforms.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PlatformRequest $request): RedirectResponse
    {
        Platform::create($request->validated());

        return redirect()->route('platforms.index')->with('success', 'Platform created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Platform $platform): View
    {
        return view('platforms.show', compact('platform'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Platform $platform): View
    {
        return view('platforms.edit', compact('platform'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PlatformRequest $request, Platform $platform): RedirectResponse
    {
        $platform->update($request->validated());

        return redirect()->route('platforms.index')->with('success', 'Platform updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Platform $platform): RedirectResponse
    {
        $platform->delete();

        return redirect()->route('platforms.index')->with('success', 'Platform deleted successfully.');
    }
}
