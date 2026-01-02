<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Session;

class QuizController extends Controller
{
    // The 11 Categories
    private $categories = [
        'gehoert_werden' => 'Gehört werden',
        'wissen_anwendung' => 'Wissen & Anwendung',
        'ankommen' => 'Ankommen',
        'wahrnehmen' => 'Wahrnehmen',
        'akzeptanz' => 'Akzeptanz',
        'loslassen' => 'Loslassen',
        'kraftquelle' => 'Kraftquelle Aktivierung',
        'stabilitaet' => 'Stabilität',
        'selbstfuersorge' => 'Selbstfürsorge',
        'koerperarbeit' => 'Körperarbeit',
        'co_regulation' => 'Co-Regulation',
    ];

    public function index()
    {
        return view('quiz', ['categories' => $this->categories]);
    }

    public function submit(Request $request)
    {
        // Validate 22 inputs (q1..q22)
        // 2 questions per category.
        // Map: q0, q1 -> gehoert_werden; q2, q3 -> wissen_anwendung ...
        
        $data = $request->validate([
            'answers' => 'required|array|min:22|max:22',
            'answers.*' => 'required|integer|min:1|max:10',
        ]);

        $answers = $data['answers'];
        $scores = [];
        $i = 0;
        
        foreach ($this->categories as $key => $label) {
            // Average of the next 2 questions
            $val1 = isset($answers[$i]) ? $answers[$i] : 1;
            $val2 = isset($answers[$i+1]) ? $answers[$i+1] : 1;
            $avg = ($val1 + $val2) / 2;
            $scores[$key] = $avg;
            $i += 2;
        }

        // Find lowest category
        $sortedScores = $scores;
        asort($sortedScores);
        $lowestKey = array_key_first($sortedScores);
        $lowestLabel = $this->categories[$lowestKey];
        $lowestValue = $sortedScores[$lowestKey];

        // Store in session for the next step (Email capture)
        Session::put('quiz_result', [
            'scores' => $scores,
            'answers' => $answers, // <--- ADDED: Save raw answers
            'lowest_category' => [
                'key' => $lowestKey,
                'label' => $lowestLabel,
                'value' => $lowestValue
            ]
        ]);

        return response()->json([
            'success' => true,
            'redirect' => route('quiz.email')
        ]);
    }

    public function showEmailForm()
    {
        if (!Session::has('quiz_result')) {
            return redirect()->route('quiz.index');
        }

        return view('email_capture', [
            'result' => Session::get('quiz_result'),
            'categories' => $this->categories
        ]);
    }

    public function generatePDF(Request $request)
    {
        \Illuminate\Support\Facades\Log::info('PDF Generation Started. Request Data: ', $request->all());

        $request->validate([
            'email' => 'required|email',
            'privacy' => 'accepted',
            // 'marketing' => 'boolean'
        ]);

        $sessionData = Session::get('quiz_result');
        if (!$sessionData) {
            \Illuminate\Support\Facades\Log::error('PDF Fail: Session quiz_result is missing!');
            return redirect()->route('quiz.index');
        }

        \Illuminate\Support\Facades\Log::info('Session Data found. Scores: ', $sessionData['scores']);
        
        // Handle Logo for PDF (Base64 allow it to work without absolute paths issues)
        $logoBase64 = null;
        $logoPath = public_path('Logo_Final_Solo.png'); // User must upload this to public/
        if (file_exists($logoPath)) {
            $type = pathinfo($logoPath, PATHINFO_EXTENSION);
            $data = file_get_contents($logoPath);
            $logoBase64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        }

        // Prepare Data for PDF
        $data = [
            'scores' => $sessionData['scores'],
            'lowest_category' => $sessionData['lowest_category'],
            'categories' => $this->categories,
            'date' => date('d.m.Y'),
            'email' => $request->email,
            'logo' => $logoBase64
        ];

        // 1. Generate PDF (in memory)
        try {
             $pdf = Pdf::loadView('pdf.report', $data);
             $pdfContent = $pdf->output();
             \Illuminate\Support\Facades\Log::info('PDF Generated in Memory.');
        } catch (\Exception $e) {
             \Illuminate\Support\Facades\Log::error('DomPDF Gen Failed: '.$e->getMessage());
             throw $e;
        }

        // 2. Save to Database
        try {
            // Find or create respondent
            $respondent = \App\Models\Respondent::firstOrCreate(
                ['email' => $request->email],
                [
                    'marketing_opt_in' => $request->has('marketing'),
                    'contact_request' => $request->has('contact_request'),
                    'ip_address' => $request->ip()
                ]
            );

            // Create Assessment
            $rawAnswers = isset($sessionData['answers']) ? $sessionData['answers'] : [];

            $respondent->assessments()->create([
                'scores' => $sessionData['scores'],
                'answers' => $rawAnswers,
                'lowest_category_key' => $sessionData['lowest_category']['key'],
                'lowest_category_score' => $sessionData['lowest_category']['value'],
            ]);
            \Illuminate\Support\Facades\Log::info('DB Saved Successfully.');

        } catch (\Exception $e) {
            // Log error but continue to download
            \Illuminate\Support\Facades\Log::error('DB Save Failed: '.$e->getMessage());
        }

        // 3. Send Email
        try {
            \Illuminate\Support\Facades\Mail::to($request->email)
                ->send(new \App\Mail\QuizResultMail($pdfContent));
            \Illuminate\Support\Facades\Log::info('Email Sent Successfully.');
        } catch (\Exception $e) {
             \Illuminate\Support\Facades\Log::error('Mail Send Failed: '.$e->getMessage());
        }
        
        // 4. Download
        return response()->streamDownload(
            fn () => print($pdfContent),
            'nervensystem-kompass.pdf'
        );
    }
}
