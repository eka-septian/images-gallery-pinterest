<?php

namespace App\Http\Controllers;

use App\Models\Stories;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StoriesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('stories.index', [
            'stories' => Stories::with('user')->latest()->get(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('stories.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $formFields = $request->validate([
            'title' => 'required|max:255',
            'image' => 'required|image'
        ]);

        $formFields['image'] = $request->file('image')->store('images', 'public');

        if ($request->has('description')) {
            $formFields['description'] = $request->description;
        }

        $story = $request->user()->stories()->create($formFields);

        return view('stories.partials.create-success', [
            'story' => $story
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Stories  $stories
     * @return \Illuminate\Http\Response
     */
    public function show(Stories $stories)
    {
        return view('stories.story', [
            'story' => $stories
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Stories  $stories
     * @return \Illuminate\Http\Response
     */
    public function edit(Stories $stories)
    {
        return view('stories.edit', [
            'story' => $stories
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Stories  $stories
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Stories $stories)
    {
        if ($stories->user_id != auth()->id()) {
            abort(403, 'Unauthorized');
        }

        $formFields = $request->except('image');

        if ($request->hasFile('image')) {
            Storage::disk('public')->delete($stories->image);
            $formFields['image'] = $request->file('image')->store('images', 'public');
        }

        $stories->update($formFields);
        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Stories  $stories
     * @return \Illuminate\Http\Response
     */
    public function destroy(Stories $stories)
    {
        if ($stories->user_id != auth()->id()) {
            abort(403, 'Unauthorized');
        }

        Storage::disk('public')->delete($stories->image);
        $stories->delete();

        return back();
    }
}
