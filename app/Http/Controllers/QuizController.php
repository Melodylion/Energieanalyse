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
        $result = session('quiz_result'); // Get FULL result array
        $quizId = session('quiz_result.quiz_id');
        
        if (!$result || !$quizId) {
            return redirect()->route('quiz.index');
        }

        // Fetch Quiz safely by ID
        $quiz = \App\Models\Quiz::find($quizId);

        if (!$quiz) {
            // Should not happen if session is valid, but safety first
            return redirect()->route('quiz.index'); 
        }
        
        // Define Default/Initial Texts (from DB or strings)
        $texts = [
            'analysis_title' => $quiz->analysis_title ?? 'Deine Analyse ist bereit',
            'analysis_text' => $quiz->analysis_text ?? 'Hier ist der erste Blick auf dein Nervensystem-Profil.',
            'graph_header' => $quiz->analysis_graph_text ?? 'Hier ist ein Blick auf dein Nervensystem-Profil',
            'report_title' => $quiz->report_title ?? 'Dein persönlicher Report',
            'report_teaser' => $quiz->analysis_report_text ?? "Um deine detaillierte Auswertung inklusive individueller Übungen für deinen Bereich zu erhalten, trage bitte deine E-Mail-Adresse ein.",
        ];

        if ($quiz) {
            // 1. Calculate Scores to find Best/Worst Category
            $scores = $result['scores']; 

            // Sort scores to find highest/lowest
            asort($scores); // Low to High
            
            $lowestKey = array_key_first($scores);
            $highestKey = array_key_last($scores);

            // Fetch Category Models for descriptions
            $categoryModels = \App\Models\Category::whereIn('key', [$lowestKey, $highestKey])->where('quiz_id', $quiz->id)->get();
            $bestCat = $categoryModels->where('key', $highestKey)->first();
            $worstCat = $categoryModels->where('key', $lowestKey)->first();

            // Prepare Placeholders
            $replacements = [
                '{{best-category}}' => $bestCat ? $bestCat->label : '',
                '{{best-category-description}}' => $bestCat ? ($bestCat->description_positive ?? $bestCat->description) : '',
                '{{worst-category}}' => $worstCat ? $worstCat->label : '',
                '{{worst-category-description}}' => $worstCat ? ($worstCat->description_negative ?? $worstCat->description) : '',
            ];

            // 2. Process Placeholders for ALL fields
            foreach ($texts as $key => $text) {
                 if (!empty($text)) {
                     $texts[$key] = strtr($text, $replacements);
                 }
            }
            
            // Final fallback logic for teaser if empty in DB
            if (empty($quiz->analysis_report_text) && $worstCat) {
                $texts['report_teaser'] = "Um deine detaillierte Auswertung inklusive individueller Übungen für deinen Bereich \"{$worstCat->label}\" zu erhalten, trage bitte deine E-Mail-Adresse ein.";
            }
        }

        return view('email_capture', [
            'result' => $result, 
            'categories' => $result['labels_map'] ?? [],
            'quizTitle' => $quizTitle ?? 'Energieanalyse',
            'quiz' => $quiz,
            'texts' => $texts // Pass array of texts
        ]);
    }

    public function generatePDF(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'chart_image' => 'nullable|string' // Validating base64 string
        ]);
        
        $sessionData = Session::get('quiz_result');

        if (!$sessionData) {
            return redirect('/');
        }
        
        $quizId = $sessionData['quiz_id'];
        $quiz = \App\Models\Quiz::find($quizId);

        if (!$quiz) {
             return redirect('/');
        }
        
        // 1. Calculate Scores & Models (Same as EmailForm)
        $scores = $sessionData['scores'];
        asort($scores);
        $lowestKey = array_key_first($scores);
        $highestKey = array_key_last($scores);

        $categoryModels = \App\Models\Category::whereIn('key', [$lowestKey, $highestKey])->where('quiz_id', $quiz->id)->get();
        $bestCat = $categoryModels->where('key', $highestKey)->first();
        $worstCat = $categoryModels->where('key', $lowestKey)->first();

        // 2. Prepare HTML Snippets for Assignments (The Colored Boxes)
        // REMOVED Newlines to prevent extra spacing in 'pre-wrap' containers
        $fokusHtml = '<div style="margin-top: 10px; margin-bottom: 15px;"><p style="font-weight: bold; color: #2D2D2D; margin-bottom: 5px; font-size: 14px;">Dein Fokus-Bereich (Stärkste Ausprägung):</p><div style="background: #F9F7F2; padding: 15px; border-radius: 4px; border-left: 3px solid #C5A065;"><strong style="color: #C5A065; font-size: 16px;">' . ($bestCat->label ?? 'N/A') . '</strong><p style="font-size: 12px; margin-top: 5px; color: #444; line-height: 1.4;">' . ($bestCat->description_positive ?? $bestCat->description ?? '') . '</p></div></div>';

        $developmentHtml = '<div style="margin-top: 10px; margin-bottom: 15px;"><p style="font-weight: bold; color: #2D2D2D; margin-bottom: 5px; font-size: 14px;">Dein Entwicklungs-Bereich (Schwächste Ausprägung):</p><div style="background: #fff; border: 1px solid #eee; padding: 15px; border-radius: 4px; border-left: 3px solid #999;"><strong style="color: #666; font-size: 16px;">' . ($worstCat->label ?? 'N/A') . '</strong><p style="font-size: 12px; margin-top: 5px; color: #444; line-height: 1.4;">' . ($worstCat->description_negative ?? $worstCat->description ?? '') . '</p></div></div>';

        // 3. Define Placeholders
        $replacements = [
            '{{best-category}}' => $bestCat ? $bestCat->label : '',
            '{{best-category-description}}' => $bestCat ? ($bestCat->description_positive ?? $bestCat->description) : '',
            '{{worst-category}}' => $worstCat ? $worstCat->label : '',
            '{{worst-category-description}}' => $worstCat ? ($worstCat->description_negative ?? $worstCat->description) : '',
            '{{fokus_area}}' => $fokusHtml,
            '{{development_area}}' => $developmentHtml,
            '{{url}}' => url('/'),
        ];

        // 4. Text Resolution with Overrides
        // We resolve texts HERE, so strict PDF logic is handled before View
        
        // Report Title: Specific PDF override OR Standard Report Title
        $reportTitle = !empty($quiz->pdf_page2_title) ? $quiz->pdf_page2_title : ($quiz->report_title ?? 'Dein persönlicher Report');
        
        // Report Text: Specific PDF override OR Standard Web Report Text OR Fallback
        $reportText = !empty($quiz->pdf_page2_text) ? $quiz->pdf_page2_text : ($quiz->analysis_report_text ?? "Um deine detaillierte Auswertung inklusive individueller Übungen für deinen Bereich zu erhalten, trage bitte deine E-Mail-Adresse ein.");

        $texts = [
            'analysis_title' => $quiz->analysis_title ?? 'Deine Analyse ist bereit',
            'analysis_text' => $quiz->analysis_text ?? 'Hier ist der erste Blick auf dein Nervensystem-Profil.',
            'graph_header' => $quiz->analysis_graph_text ?? 'Hier ist ein Blick auf dein Nervensystem-Profil',
            'report_title' => $reportTitle,
            'report_text' => $reportText, // This is now the FINAL text for Page 2
        ];

        // Process Placeholders
        foreach ($texts as $key => $text) {
             if (!empty($text)) {
                 $texts[$key] = strtr($text, $replacements);
             }
        }

        // 5. Prepare Email Subject & Body (also processing placeholders)
        $emailSubject = !empty($quiz->email_subject) ? $quiz->email_subject : 'Dein persönliches Nervensystem-Profil';
        $emailSubject = strtr($emailSubject, $replacements);

        $defaultEmailBody = "Hallo,\n\nVielen Dank, dass du dir die Zeit genommen hast, den Nervensystem-Kompass auszufüllen.\n\nAnbei erhältst du deine persönliche Auswertung als PDF.\n\nSolltest du Fragen haben, melde dich gerne bei mir.\n\nHerzliche Grüße,\nSophie Philipp\n\nSophie Philipp\nwww.sophie-philipp.ch";
        
        $rawEmailBody = !empty($quiz->email_body) ? $quiz->email_body : $defaultEmailBody;
        
        // CRITICAL: We must nl2br() the raw text BEFORE inserting HTML placeholders
        // otherwise newlines in the user text become chaos or invisible.
        // We also e() escape the user input to prevent accidents, but then we inject OUR safe HTML.
        $processedBody = nl2br(e($rawEmailBody));
        
        // NOW inject the HTML snippets (which are already HTML safe)
        $emailBody = strtr($processedBody, $replacements);

        // Prepare Data
        $data = [
            'scores' => $sessionData['scores'],
            'lowest_category' => $sessionData['lowest_category'],
            'best_category_model' => $bestCat,
            'worst_category_model' => $worstCat,
            'categories' => $sessionData['labels_map'],
            'date' => date('d.m.Y'),
            'email' => $request->email,
            'logo' => $this->getLogoBase64(), 
            'quiz' => $quiz,
            'texts' => $texts,
            'title' => $texts['analysis_title'] ?? $quizTitle,
            'chart_image' => $request->chart_image,
            // Pass Pre-Rendered Blocks for Default Layout Fallback in Blade
            'fokusHtml' => $fokusHtml,
            'developmentHtml' => $developmentHtml
        ];

        // Generate PDF
        $pdf = Pdf::loadView('pdf.report', $data);
        $pdfContent = $pdf->output();

        // Save to Database
        $this->saveToDatabase($request, $sessionData);

        // Send Email (Passing processed Subject & Body)
        try {
            \Illuminate\Support\Facades\Mail::to($request->email)
                ->send(new \App\Mail\QuizResultMail($pdfContent, $emailSubject, $emailBody));
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
                // Defaults for NEW records
                'marketing_opt_in' => $request->has('marketing'),
                'contact_request' => $request->has('contact_request'),
                'ip_address' => $request->ip()
            ]
        );
        
        // Update flags for EXISTING records (User might have changed their mind)
        $respondent->update([
            'marketing_opt_in' => $request->has('marketing'),
            'contact_request' => $request->has('contact_request'),
            'ip_address' => $request->ip() // Update IP too just in case
        ]);

        $respondent->assessments()->create([
            'quiz_id' => $sessionData['quiz_id'] ?? null,
            'scores' => $sessionData['scores'],
            'answers' => $sessionData['answers'] ?? [],
            'lowest_category_key' => $sessionData['lowest_category']['key'],
            'lowest_category_score' => $sessionData['lowest_category']['value'],
        ]);
    }

}
