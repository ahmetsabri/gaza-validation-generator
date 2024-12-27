<?php

namespace Gaza\ValidationGenerator\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class ValidateTableCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'validate-table {tableName}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a validation array for the specified table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tableName = $this->argument('tableName');
        if (! Schema::hasTable($tableName)) {
            $this->error("Table '{$tableName}' does not exist.");

            return;
        }

        // Get the rule file path
        $filePath = __DIR__ . '/../../resources/data/validationGeneratorConfig.json';
        if (! file_exists($filePath)) {
            $this->error('Rule file not found.');

            return;
        }

        // Read and process the JSON file
        $jsonContent = file_get_contents($filePath);
        $configData = json_decode($jsonContent, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->error('Invalid JSON format.');

            return;
        }

        $exclude = $configData['exclude'];
        $columnTypeRules = $configData['columnTypeRules'];

        // Get table columns
        $columns = Schema::getColumns($tableName);

        // Build validation rules
        $validationRules = [];
        foreach ($columns as $column) {
            if (in_array($column['name'], $exclude)) {
                continue;
            }
            $rules = [];

            if ($column['nullable']) {
                $rules[] = 'nullable';
            } else {
                $rules[] = 'required';
            }

            // Add type-specific rules
            $typeName = strtolower($column['type_name']);
            if (array_key_exists($typeName, $columnTypeRules)) {
                $rules[] = $columnTypeRules[$typeName];
            }
            if (in_array('string', $rules)) {
                if (preg_match('/\((\d+)\)/', $column['type'], $matches)) {
                    $rules[] = 'max:' . $matches[1];
                }
            }

            // Assign rules to the column
            $validationRules[$column['name']] = $rules;
        }

        // Output validation array
        $this->info("Validation rules for table '{$tableName}':");
        $output = str_replace(['":', '{', '}'], ['" =>', '[', ']'], json_encode($validationRules, JSON_PRETTY_PRINT));

        $this->line($output);
    }
}
