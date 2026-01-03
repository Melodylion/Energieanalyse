<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Quiz;

class CategoryController extends Controller
{
    public function store(Request $request, Quiz $quiz)
    {
        $data = $request->validate([
            'label' => 'required|string|max:255',
            'key' => 'required|string|max:255|alpha_dash', // e.g. 'loslassen'
            'description' => 'nullable|string',
            'impulse_text' => 'nullable|string',
        ]);

        $quiz->categories()->create($data);

        return back()->with('success', 'Kategorie erstellt!');
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'label' => 'required|string|max:255',
            'key' => 'required|string|max:255|alpha_dash',
            'description' => 'nullable|string',
            'impulse_text' => 'nullable|string',
        ]);

        $category->update($data);

        return back()->with('success', 'Kategorie aktualisiert.');
    }

    public function destroy(Category $category)
    {
        $category->delete();
        return back()->with('success', 'Kategorie gelöscht.');
    }
}
