<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Quiz;
use App\Models\Question;
use App\Models\Category;

class QuizManagerController extends Controller
{
    public function index()
    {
        $quizzes = Quiz::withCount('questions', 'assessments')->get();
        return view('admin.quizzes.index', compact('quizzes'));
    }

    public function create()
    {
        return view('admin.quizzes.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:quizzes',
            'description' => 'nullable|string'
        ]);

        $quiz = Quiz::create($data);
        return redirect()->route('admin.quizzes.edit', $quiz);
    }

    public function edit(Quiz $quiz)
    {
        // Load relationships for the Matrix
        $quiz->load('questions.categories', 'categories');
        
        return view('admin.quizzes.edit', compact('quiz'));
    }

    public function update(Request $request, Quiz $quiz)
    {
        // 1. Basic Quiz Info
        $quiz->update($request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:quizzes,slug,'.$quiz->id,
            'description' => 'nullable|string',
            'active' => 'boolean'
        ]));

        // 2. Questions & Matrix
        // Expecting input: questions[existing_id][text], questions[existing_id][weights][cat_id]
        // And new_questions[]...
        
        $questionsInput = $request->input('questions', []);
        
        // Update existing questions
        foreach ($questionsInput as $qId => $qData) {
            $question = Question::find($qId);
            if ($question && $question->quiz_id == $quiz->id) {
                // Update Text
                if (isset($qData['text'])) {
                    $question->update(['text' => $qData['text']]);
                }
                
                // Update Weights
                if (isset($qData['weights'])) {
                    foreach ($qData['weights'] as $catId => $weight) {
                        // Sync without detaching others
                        $question->categories()->syncWithoutDetaching([
                             $catId => ['weight' => (int)$weight]
                        ]);
                    }
                }
                
                // Delete?
                if (isset($qData['delete']) && $qData['delete'] == '1') {
                    $question->delete();
                }
            }
        }

        // Create New Questions
        if ($request->has('new_questions')) {
            foreach ($request->input('new_questions') as $newQ) {
                if (!empty($newQ['text'])) {
                    $q = $quiz->questions()->create([
                        'text' => $newQ['text'],
                        'order' => 999 // Add logic for order later
                    ]);
                    
                    // Add weights for new question
                    if (isset($newQ['weights'])) {
                        foreach ($newQ['weights'] as $catId => $weight) {
                            $q->categories()->attach($catId, ['weight' => (int)$weight]);
                        }
                    }
                }
            }
        }
        
        // Re-number ordering just in case
        $i = 1;
        foreach ($quiz->questions()->orderBy('id')->get() as $q) {
            $q->update(['order' => $i++]);
        }

        return redirect()->route('admin.quizzes.edit', $quiz)->with('success', 'Gespeichert!');
    }

    public function destroy(Quiz $quiz)
    {
        // Delete related data (Categories + Questions + Assessments)
        // Note: DB constraints might handle this via OnDelete('Cascade'), but let's be safe/explicit if needed.
        // Given our migrations used cascadeOnDelete(), simple delete is enough.
        
        $quiz->delete();

        return redirect()->route('admin.quizzes.index')->with('success', 'Quiz erfolgreich gelöscht.');
    }
}
