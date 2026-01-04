<?php

namespace App\Http\Controllers;

use App\Models\Respondent;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index()
    {
        // Secret check removed - now handled by 'auth' middleware

        $respondents = Respondent::with('assessments')->latest()->get();

        // Pass Questions Structure to View for mapping IDs to Text
        // We fetch the 'Main' quiz for this mapping, or just all questions if they are unique enough
        $questions = \App\Models\Question::pluck('text', 'id')->toArray();

        return view('admin.dashboard', compact('respondents', 'questions'));
    }

    public function destroy(Respondent $respondent)
    {
        $respondent->delete();
        return back()->with('success', 'Eintrag gelöscht.');
    }

    public function runMigrate()
    {
        try {
            \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
            $output = \Illuminate\Support\Facades\Artisan::output();
            return back()->with('success', 'Datenbank Update erfolgreich! Log: <br>' . nl2br($output));
        } catch (\Exception $e) {
            return back()->with('error', 'Fehler beim Update: ' . $e->getMessage());
        }
    }
}
