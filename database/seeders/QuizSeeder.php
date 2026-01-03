<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Quiz;
use App\Models\Category;
use App\Models\Question;

class QuizSeeder extends Seeder
{
    public function run()
    {
        // 1. Create Quiz
        $quiz = Quiz::firstOrCreate(
            ['slug' => 'energieanalyse'],
            [
                'title' => 'Nervensystem Kompass',
                'description' => 'Persönliche Analyse für dein Nervensystem.',
                'active' => true,
            ]
        );

        // 2. Define Categories (Keys must match Controller logic for now)
        $categoriesData = [
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

        $categoryModels = [];
        foreach ($categoriesData as $key => $label) {
            $categoryModels[$key] = Category::firstOrCreate(
                ['quiz_id' => $quiz->id, 'key' => $key],
                ['label' => $label]
            );
        }

        // 3. Define Questions (Hardcoded from previous Blade file)
        // Grouped by Category Key for easier seeding
        $questionsData = [
            'gehoert_werden' => [
                'Ich habe Menschen in meinem Leben, bei denen ich mich mit all meinen Themen sicher und gehört fühle.',
                'Ich erlaube mir selbst, Raum für alles einzunehmen, was mich aktuell beschäftigt.'
            ],
            'wissen_anwendung' => [
                'Ich verstehe, wie mein Nervensystem auf Stress reagiert (z.B. Kampf/Flucht/Starre).',
                'Ich weiß theoretisch, was mir bei Stress hilft, und kann dieses Wissen im Alltag abrufen.'
            ],
            'ankommen' => [
                'Es fällt mir leicht, im Alltag bewusst Tempo rauszunehmen und innerlich anzukommen.',
                'Ich kann Momente der Stille aushalten, ohne mich sofort ablenken zu müssen.'
            ],
            'wahrnehmen' => [
                'Ich spüre während des Tages rechtzeitig, wenn mein Körper Anzeichen von Stress zeigt.',
                'Ich kann meine Gedanken und Gefühle im Hier und Jetzt beobachten, ohne sie sofort zu bewerten.'
            ],
            'akzeptanz' => [
                'Ich kann schwierige Gefühle oder Situationen annehmen, anstatt gegen sie anzukämpfen.',
                'Ich bin in der Lage, liebevoll mit mir umzugehen, auch wenn ich mich gerade nicht „perfekt“ fühle.'
            ],
            'loslassen' => [
                'Es gelingt mir gut, belastende Gedanken oder Aufgaben am Feierabend loszulassen.',
                'Ich erkenne Dinge, die mir nicht mehr dienen, und kann mich aktiv von ihnen distanzieren.'
            ],
            'kraftquelle' => [
                'Ich kenne meine individuellen Kraftquellen und weiß, wie ich sie aktiviere.',
                'Ich fühle mich regelmäßig tief mit meiner inneren Energie und Stärke verbunden.'
            ],
            'stabilitaet' => [
                'Ich finde eine gute Balance zwischen Phasen der Aktivität und Phasen der Erholung.',
                'Ich fühle mich emotional stabil, auch wenn es im Außen turbulent zugeht.'
            ],
            'selbstfuersorge' => [
                'Ich nehme mir bewusst Zeit für Tätigkeiten, die meine inneren Batterien aufladen.',
                'Selbstfürsorge hat für mich im Alltag eine hohe Priorität, nicht nur im Notfall.'
            ],
            'koerperarbeit' => [
                'Ich nutze einfache körperliche Übungen (z.B. Atmen, Dehnen), um mein System zu beruhigen.',
                'Ich fühle mich gut mit meinem Körper verbunden und achte auf seine Signale.'
            ],
            'co_regulation' => [
                'Ich kann mich in stressigen Momenten durch den Kontakt mit anderen Menschen beruhigen.',
                'Ich fühle mich sicher darin, meine eigene Ruhe auch an mein Umfeld (Partner/Kinder) weiterzugeben.'
            ]
        ];

        $order = 1;

        foreach ($questionsData as $catKey => $questions) {
            $category = $categoryModels[$catKey];

            foreach ($questions as $qText) {
                // Create Question
                $q = Question::firstOrCreate(
                    ['quiz_id' => $quiz->id, 'text' => $qText],
                    ['order' => $order++]
                );

                // Pivot: Attach to Category with Weight 10 (Standard Logic)
                if (!$q->categories()->where('category_id', $category->id)->exists()) {
                    $q->categories()->attach($category->id, ['weight' => 10]);
                }
            }
        }
    }
}
