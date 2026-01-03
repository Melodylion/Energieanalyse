<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Session;
use App\Models\Quiz;
use App\Models\Question;
use App\Models\Category;

class QuizController extends Controller
{
    // Display the Quiz (Dynamic)
    public function index($slug = 'energieanalyse')
    {
        $quiz = Quiz::with(['questions'])->where('slug', $slug)->firstOrFail();
        
        // We map questions to a simple structure for AlpineJS
        $jsQuestions = $quiz->questions->map(function($q) {
            return ['id' => $q->id, 'text' => $q->text];
        });

        // Pass Quiz and Questions to Views for potential static display if needed, though mostly handled in submit now
        return view('quiz', [
            'quiz' => $quiz,
            'jsQuestions' => $jsQuestions
        ]);
    }

    public function submit(Request $request, $slug = 'energieanalyse')
    {
        $quiz = Quiz::where('slug', $slug)->firstOrFail();

        // Validate Answers (Key is index 0..N, corresponding to order)
        $data = $request->validate([
            'answers' => 'required|array',
            'answers.*' => 'required|integer|min:1|max:10',
        ]);

        $userAnswers = $data['answers']; // Array indices match question mapping order in Frontend
        
        // 1. Fetch all Questions with their Weights
        $questions = $quiz->questions()->with('categories')->orderBy('order')->get();

        // 2. Initialize Category Scores
        $categories = $quiz->categories;
        $catScores = [];   // Stores raw points extracted
        $catMax = [];      // Stores maximum possible points

        foreach ($categories as $cat) {
            $catScores[$cat->id] = 0;
            $catMax[$cat->id] = 0;
        }

        // 3. Calculate Scores (The Matrix Logic)
        foreach ($questions as $index => $q) {
            $answerVal = isset($userAnswers[$index]) ? $userAnswers[$index] : 5; // Default safety

            foreach ($q->categories as $cat) {
                $weight = $cat->pivot->weight;
                $points = 0;
                $maxPoints = 10 * abs($weight);

                if ($weight > 0) {
                    // Positive: 10/10 answer is good
                    $points = $answerVal * $weight;
                } elseif ($weight < 0) {
                    // Negative: 10/10 answer is bad -> Flip it (11 - 10 = 1)
                    $points = (11 - $answerVal) * abs($weight);
                }
                
                // Add to totals
                if (isset($catScores[$cat->id])) {
                    $catScores[$cat->id] += $points;
                    $catMax[$cat->id] += $maxPoints;
                }
            }
        }

        // 4. Normalize to 0-10 Scale
        $finalScores = [];
        $labels = [];

        foreach ($categories as $cat) {
            if ($catMax[$cat->id] > 0) {
                $normalized = ($catScores[$cat->id] / $catMax[$cat->id]) * 10;
            } else {
                $normalized = 0;
            }
            // Key by 'key' string (e.g. 'gehoert_werden') for frontend compatibility
            $finalScores[$cat->key] = round($normalized, 1);
            $labels[$cat->key] = $cat->label;
        }

        // 5. Find lowest category
        // We use the same sorting logic as before
        $sortedScores = $finalScores;
        asort($sortedScores);
        $lowestKey = array_key_first($sortedScores);
        
        // Find the full category model for the lowest key to get label/impulse if needed
        $lowestCatModel = $categories->where('key', $lowestKey)->first();

        // Store in session
        Session::put('quiz_result', [
            'quiz_id' => $quiz->id,
            'scores' => $finalScores,
            'answers' => $userAnswers,
            'lowest_category' => [
                'key' => $lowestKey,
                'label' => $labels[$lowestKey],
                'value' => $sortedScores[$lowestKey]
            ],
            // Store Labels map for PDF generation later
            'labels_map' => $labels,
            'quiz_title' => $quiz->title // Store Title
        ]);

        return response()->json([
            'success' => true,
            'redirect' => route('quiz.email')
        ]);
    }

    public function showEmailForm()
    {
        if (!Session::has('quiz_result')) {
            return redirect('/');
        }
        
        // Reconstruct basic category list for the View
        $sessionData = Session::get('quiz_result');
        $categories = $sessionData['labels_map']; 
        
        // Fetch Quiz for Layout Title
        $quiz = \App\Models\Quiz::find($sessionData['quiz_id'] ?? null);

        return view('email_capture', [
            'result' => $sessionData,
            'categories' => $categories,
            'quiz' => $quiz
        ]);
    }

    public function generatePDF(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $sessionData = Session::get('quiz_result');

        if (!$sessionData) {
            return redirect('/');
        }

        // Prepare Data
        $data = [
            'scores' => $sessionData['scores'],
            'lowest_category' => $sessionData['lowest_category'],
            'categories' => $sessionData['labels_map'],
            'date' => date('d.m.Y'),
            'email' => $request->email,
            'logo' => $this->getLogoBase64(), // Helper
            'title' => $sessionData['quiz_title'] ?? 'Nervensystem Kompass' // Dynamic Title with Fallback
        ];

        // Generate PDF
        $pdf = Pdf::loadView('pdf.report', $data);
        $pdfContent = $pdf->output();

        // Save to Database (Assessments)
        $this->saveToDatabase($request, $sessionData);

        // Send Email
        try {
            \Illuminate\Support\Facades\Mail::to($request->email)
                ->send(new \App\Mail\QuizResultMail($pdfContent));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Mail Error: '.$e->getMessage());
        }

        return response()->streamDownload(fn () => print($pdfContent), 'analyse-report.pdf');
    }

    private function getLogoBase64() {
        $logoPath = public_path('Logo_Final_Solo.png');
        if (file_exists($logoPath)) {
            return 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
        }
        return null;
    }

    private function saveToDatabase($request, $sessionData) {
        $respondent = \App\Models\Respondent::firstOrCreate(
            ['email' => $request->email],
            [
                'marketing_opt_in' => $request->has('marketing'),
                'contact_request' => $request->has('contact_request'),
                'ip_address' => $request->ip()
            ]
        );

        $respondent->assessments()->create([
            'quiz_id' => $sessionData['quiz_id'] ?? null,
            'scores' => $sessionData['scores'],
            'answers' => $sessionData['answers'] ?? [],
            'lowest_category_key' => $sessionData['lowest_category']['key'],
            'lowest_category_score' => $sessionData['lowest_category']['value'],
        ]);
    }
}
