<?php

namespace Database\Factories;

use App\Models\Note;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Note>
 */
class NoteFactory extends Factory
{
    protected $model = Note::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Generate a randomized, normalized 1536-dimension float vector for testing math
        $vector = [];
        $sumSq = 0.0;
        
        for ($i = 0; $i < 1536; $i++) {
            $num = (rand(-10000, 10000) / 10000.0);
            $vector[] = $num;
            $sumSq += $num * $num;
        }
        
        $magnitude = sqrt($sumSq);
        if ($magnitude > 0) {
            for ($i = 0; $i < 1536; $i++) {
                $vector[$i] = $vector[$i] / $magnitude;
            }
        }

        return [
            'title' => $this->faker->sentence(4),
            'content' => $this->faker->paragraph(6) . "\n\n" . $this->faker->text(200),
            'embedding' => $vector,
        ];
    }
}
