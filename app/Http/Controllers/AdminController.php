<?php

namespace App\Http\Controllers;

use App\Models\Respondent;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function index()
    {
        // Simple protection: Check for a query param ?secret=your_password
        // In a real app we would use Middleware/Auth, but for this MVP this is quick.
        if (request('secret') !== env('ADMIN_SECRET', 'sophie123')) {
            abort(403);
        }

        $respondents = Respondent::with('assessments')->latest()->get();

        // Pass Questions Map to decode answers
        $questions = [
            'Ich habe Menschen in meinem Leben, bei denen ich mich ... sicher fühle.',
            'Ich erlaube mir selbst, Raum für alles einzunehmen...',
            'Ich verstehe, wie mein Nervensystem auf Stress reagiert...',
            'Ich weiß theoretisch, was mir bei Stress hilft...',
            'Es fällt mir leicht, im Alltag bewusst Tempo rauszunehmen...',
            'Ich kann Momente der Stille aushalten...',
            'Ich spüre während des Tages rechtzeitig, wenn mein Körper Anzeichen von Stress zeigt.',
            'Ich kann meine Gedanken und Gefühle im Hier und Jetzt beobachten...',
            'Ich kann schwierige Gefühle oder Situationen annehmen...',
            'Ich bin in der Lage, liebevoll mit mir umzugehen...',
            'Es gelingt mir gut, belastende Gedanken am Feierabend loszulassen.',
            'Ich erkenne Dinge, die mir nicht mehr dienen...',
            'Ich kenne meine individuellen Kraftquellen...',
            'Ich fühle mich regelmäßig tief mit meiner inneren Energie verbunden.',
            'Ich finde eine gute Balance zwischen Aktivität und Erholung.',
            'Ich fühle mich emotional stabil, auch wenn es turbulent zugeht.',
            'Ich nehme mir bewusst Zeit für meine inneren Batterien.',
            'Selbstfürsorge hat für mich eine hohe Priorität.',
            'Ich nutze einfache körperliche Übungen...',
            'Ich fühle mich gut mit meinem Körper verbunden.',
            'Ich kann mich durch Kontakt mit anderen beruhigen.',
            'Ich fühle mich sicher darin, meine Ruhe weiterzugeben.'
        ];

        return view('admin.dashboard', compact('respondents', 'questions'));
    }
}
