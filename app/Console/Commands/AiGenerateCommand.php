<?php

namespace Phantom\Console\Commands;

use Exception;
use Throwable;

class AiGenerateCommand
{
    public $signature = 'ai:generate';
    public $description = 'Generate code (migrations, models, controllers) using AI';

    public function handle($args = [])
    {
        $prompt = implode(' ', $args);

        if (empty($prompt)) {
            echo "Please provide a prompt. Example: php phantom ai:generate 'create a model for Product with name and price'\n";
            return;
        }

        echo "🤖 Phantom AI is thinking...\n";

        try {
            $date = date('Y_m_d_His');
            $systemPrompt = <<<EOT
You are an expert developer for the Phantom Framework. 
The Phantom Framework is a minimalist PHP framework similar to Laravel.

Current directory structure:
- app/Models: For models.
- app/Http/Controllers: For controllers.
- database/migrations: For migrations (use Y_m_d_His_name.php format).

Your task is to respond ONLY with a valid JSON object. Do not include any other text, markdown blocks, or explanations.

The JSON structure must be:
{
    "action": "create_file",
    "path": "relative/path/to/file.php",
    "content": "<?php ... complete file content ...",
    "explanation": "Brief explanation of what was created"
}

Rules:
1. Use PSR-4 namespaces: Phantom\Models for models, App\Http\Controllers for controllers.
2. For migrations, use the current date: {$date}.
3. Ensure the code is elegant and follows the project's style.
EOT;

            $response = ai()->chat($systemPrompt . "\n\nUser Request: " . $prompt);

            // Clean response in case AI adds markdown code blocks
            $response = preg_replace('/^```json\s*/', '', $response);
            $response = preg_replace('/\s*```$/', '', $response);
            $response = trim($response);

            $data = json_decode($response, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                echo "Error: AI returned an invalid JSON response.\n";
                echo "Response received: " . $response . "\n";
                return;
            }

            if (isset($data['action']) && $data['action'] === 'create_file') {
                $path = $data['path'];
                $content = $data['content'];

                $fullPath = rtrim(getcwd(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR);
                $directory = dirname($fullPath);

                if (!file_exists($directory)) {
                    mkdir($directory, 0755, true);
                }

                file_put_contents($fullPath, $content);

                echo "✅ Created: {$path}\n";
                echo "📝 Explanation: " . ($data['explanation'] ?? 'Success') . "\n";
            } else {
                echo "AI suggested an unknown action or format is incorrect.\n";
            }

        } catch (Throwable $e) {
            echo "❌ Error: " . $e->getMessage() . "\n";
        }
    }
}