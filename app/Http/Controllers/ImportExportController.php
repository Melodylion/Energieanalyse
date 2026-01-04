<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Quiz;
use App\Models\Category;
use App\Models\Question;
use Illuminate\Support\Str;

class ImportExportController extends Controller
{
    // --- EXPORT ---
    public function export(Request $request, Quiz $quiz)
    {
        $separator = $request->query('separator') === ',' ? ',' : ';';
        $filename = 'quiz_' . $quiz->slug . '_' . date('Y-m-d') . '.csv';

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use ($quiz, $separator) {
            $file = fopen('php://output', 'w');
            fwrite($file, "\xEF\xBB\xBF"); // BOM

            // 1. [SETTINGS]
            fputcsv($file, ['[SETTINGS]'], $separator); 
            fputcsv($file, ['Key', 'Value'], $separator);
            
            $settings = [
                'title' => $quiz->title,
                'slug' => $quiz->slug,
                'intro_text' => $quiz->intro_text,
                'analysis_title' => $quiz->analysis_title,
                'analysis_text' => $quiz->analysis_text,
                'analysis_graph_text' => $quiz->analysis_graph_text,
                'report_title' => $quiz->report_title,
                'analysis_report_text' => $quiz->analysis_report_text,
                'description' => $quiz->description,
            ];

            foreach ($settings as $key => $val) {
                 // Escape Newlines AND Tabs
                 $cleanVal = str_replace(["\r", "\n", "\t"], ["", "\\n", "\\t"], $val ?? '');
                 fputcsv($file, [$key, $cleanVal], $separator);
            }

            fwrite($file, "\n"); 

            // 2. [CATEGORIES]
            fputcsv($file, ['[CATEGORIES]'], $separator);
            fputcsv($file, ['Key', 'Label', 'Description_Pos', 'Description_Neg', 'Description_Gen'], $separator);
            
            $categories = $quiz->categories()->orderBy('id')->get();
            foreach ($categories as $cat) {
                fputcsv($file, [
                    $cat->key,
                    $cat->label,
                    str_replace(["\r", "\n", "\t"], ["", "\\n", "\\t"], $cat->description_positive ?? ''),
                    str_replace(["\r", "\n", "\t"], ["", "\\n", "\\t"], $cat->description_negative ?? ''),
                    str_replace(["\r", "\n", "\t"], ["", "\\n", "\\t"], $cat->description ?? '')
                ], $separator);
            }

            fwrite($file, "\n");

            // 3. [MATRIX]
            fputcsv($file, ['[MATRIX]'], $separator);
            
            $matrixHeader = ['Question'];
            foreach ($categories as $cat) {
                $matrixHeader[] = $cat->key; 
            }
            fputcsv($file, $matrixHeader, $separator);

            $questions = $quiz->questions()->orderBy('order')->get();
            foreach ($questions as $q) {
                $row = [str_replace(["\r", "\n", "\t"], ["", "\\n", "\\t"], $q->text)];
                $weights = $q->categories()->pluck('weight', 'category_id');
                
                foreach ($categories as $cat) {
                    $row[] = $weights[$cat->id] ?? 0;
                }
                fputcsv($file, $row, $separator);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // --- IMPORT UI ---
    public function showImportForm()
    {
        return view('admin.quizzes.import');
    }

    // --- IMPORT LOGIC ---
    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file', 
            'title' => 'required|string|max:255',
            'slug' => 'required|string|unique:quizzes,slug'
        ]);

        $file = $request->file('csv_file');
        $filePath = $file->getPathname();
        
        // --- AUTO DETECT SEPARATOR ---
        $handle = fopen($filePath, 'r');
        if (!$handle) return back()->with('error', 'Konnte Datei nicht öffnen.');

        // Read first few lines to find the "Key" header
        $detectedSeparator = ';'; 
        
        // Scan up to 20 lines to find "[SETTINGS]" then "Key..."
        for ($i = 0; $i < 20; $i++) {
            $line = fgets($handle);
            if (!$line) break;
            
            // Check for standard Header line: "Key;Value" or "Key,Value"
            // We look for "Key" at start, and then determine if ; or , follows
            if (str_starts_with($line, 'Key')) {
                if (str_contains($line, ';')) {
                    $detectedSeparator = ';';
                    break;
                } elseif (str_contains($line, ',')) {
                    $detectedSeparator = ',';
                    break;
                }
            }
        }
        
        rewind($handle); // Reset pointer
        // -----------------------------

        // Initialize Quiz
        $quiz = Quiz::create([
            'title' => $request->title,
            'slug' => Str::slug($request->slug),
            'active' => false
        ]);

        $currentSection = null;
        $catKeyToIdMap = [];
        $matrixColIndexToCatId = [];

        while (($row = fgetcsv($handle, 0, $detectedSeparator, '"', '\\')) !== false) {
            // Clean BOM
            if (isset($row[0])) {
                $row[0] = str_replace("\xEF\xBB\xBF", '', $row[0]); 
                $row[0] = trim($row[0]);
            }
            
            if (empty($row) || (count($row) === 1 && empty($row[0]))) continue;

            if (str_starts_with($row[0], '[')) {
                $currentSection = strtoupper($row[0]);
                continue; 
            }

            if ($currentSection === '[SETTINGS]') {
                if ($row[0] === 'Key') continue; 
                
                $key = $row[0];
                $val = str_replace(["\\n", "\\t"], ["\n", "\t"], $row[1] ?? '');
                
                if (in_array($key, [
                    'intro_text', 'analysis_title', 'analysis_text', 
                    'analysis_graph_text', 'report_title', 'analysis_report_text', 'description'
                ])) {
                    $quiz->update([$key => $val]);
                }

            } elseif ($currentSection === '[CATEGORIES]') {
                if ($row[0] === 'Key') continue;
                
                $catKey = Str::slug(substr($row[0], 0, 250)); // Safety truncate & slugify
                if (empty($catKey)) continue;

                $cat = $quiz->categories()->create([
                    'key' => $catKey,
                    'label' => $row[1] ?? $catKey,
                    'description_positive' => isset($row[2]) ? str_replace(["\\n", "\\t"], ["\n", "\t"], $row[2]) : null,
                    'description_negative' => isset($row[3]) ? str_replace(["\\n", "\\t"], ["\n", "\t"], $row[3]) : null,
                    'description' => isset($row[4]) ? str_replace(["\\n", "\\t"], ["\n", "\t"], $row[4]) : null,
                ]);

                $catKeyToIdMap[$catKey] = $cat->id;

            } elseif ($currentSection === '[MATRIX]') {
                if ($row[0] === 'Question') {
                    $matrixColIndexToCatId = [];
                    for ($i = 1; $i < count($row); $i++) {
                        $key = $row[$i];
                        if (isset($catKeyToIdMap[$key])) {
                            $matrixColIndexToCatId[$i] = $catKeyToIdMap[$key];
                        }
                    }
                    continue;
                }

                $qText = str_replace(["\\n", "\\t"], ["\n", "\t"], $row[0]);
                if (empty($qText)) continue;

                $question = $quiz->questions()->create([
                    'text' => $qText, 
                    'order' => $quiz->questions()->count() + 1
                ]);

                foreach ($matrixColIndexToCatId as $idx => $catId) {
                    if (isset($row[$idx])) {
                        $weight = (int)$row[$idx];
                        if ($weight != 0) {
                            $question->categories()->attach($catId, ['weight' => $weight]);
                        }
                    }
                }

            }
        }

        fclose($handle);

        return redirect()->route('admin.quizzes.edit', $quiz)->with('success', 'Quiz importiert (Format: ' . ($detectedSeparator == ';' ? 'Semikolon' : 'Komma') . ')!');
    }
}
