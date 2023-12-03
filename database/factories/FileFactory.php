<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\File>
 */
class FileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'created_at' => now()
        ];
    }

    public function fromContents(string $fileName, string $contents, bool $public = false)
    {
        return $this->state(function (array $attributes) use ($fileName, $contents, $public) {
            $path = sprintf('files/%s', $fileName);

            Storage::put($path, $contents);

            return [
                'path' => $path,
                'name' => null,
                'is_public' => $public,
                'created_at' => Carbon::now(),
            ];
        });
    }
}
