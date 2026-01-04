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
            'description' => 'nullable|string',
            'intro_text' => 'nullable|string',
            'analysis_title' => 'nullable|string',
            'analysis_text' => 'nullable|string',
            'analysis_graph_text' => 'nullable|string',
            'analysis_report_text' => 'nullable|string',
            'report_title' => 'nullable|string',
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
            'intro_text' => 'nullable|string',
            'analysis_title' => 'nullable|string',
            'analysis_text' => 'nullable|string',
            'analysis_graph_text' => 'nullable|string',
            'analysis_report_text' => 'nullable|string',
            'report_title' => 'nullable|string',
            'pdf_page2_title' => 'nullable|string',
            'pdf_page2_text' => 'nullable|string',
            'email_body' => 'nullable|string',
            'email_subject' => 'nullable|string',
            'active' => 'boolean'
        ]));

        // 2. Questions & Matrix
        // Expecting input: questions[existing_id][text], questions[existing_id][weights][cat_id]
        
        $questionsInput = $request->input('questions', []);
        
        // Update existing questions
        foreach ($questionsInput as $qId => $qData) {
            $question = Question::find($qId);
            if ($question && $question->quiz_id == $quiz->id) {
                // Update Text
                if (isset($qData['text'])) $question->update(['text' => $qData['text']]);
                
                // Update Weights
                if (isset($qData['weights'])) {
                    foreach ($qData['weights'] as $catId => $weight) {
                        $question->categories()->syncWithoutDetaching([
                             $catId => ['weight' => (int)$weight]
                        ]);
                    }
                }
                
                // Delete?
                if (isset($qData['delete']) && $qData['delete'] == '1') $question->delete();
            }
        }

        // Create New Questions
        if ($request->has('new_questions')) {
            foreach ($request->input('new_questions') as $newQ) {
                if (!empty($newQ['text'])) {
                    $q = $quiz->questions()->create([
                        'text' => $newQ['text'],
                        'order' => 999 
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
        
        // 3. BULK CATEGORY UPDATES
        $categoriesInput = $request->input('categories', []);
        
        foreach ($categoriesInput as $catId => $catData) {
            $category = Category::find($catId);
            if ($category && $category->quiz_id == $quiz->id) {
                // Delete?
                if (isset($catData['delete']) && $catData['delete'] == '1') {
                    $category->delete();
                    continue; 
                }

                // Update Fields
                $category->update([
                    'label' => $catData['label'],
                    'key' => $catData['key'],
                    'description_positive' => $catData['description_positive'] ?? null,
                    'description_negative' => $catData['description_negative'] ?? null,
                    'description' => $catData['description'] ?? null,
                ]);
            }
        }

        // Create New Categories
        if ($request->has('new_categories')) {
            foreach ($request->input('new_categories') as $newCat) {
                if (!empty($newCat['label'])) {
                    $quiz->categories()->create([
                        'label' => $newCat['label'],
                        'key' => $newCat['key'] ?? \Illuminate\Support\Str::slug($newCat['label']),
                        'description_positive' => $newCat['description_positive'] ?? null,
                        'description_negative' => $newCat['description_negative'] ?? null,
                    ]);
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
