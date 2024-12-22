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
        $exclude = ['id','uuid','ulid','created_at','updated_at','deleted_at'];
        if (!Schema::hasTable($tableName)) {
            $this->error("Table '{$tableName}' does not exist.");
            return;
        }

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
            if (in_array($typeName, ['varchar', 'text', 'char'])) {
                $rules[] = 'string';
                if (preg_match('/\((\d+)\)/', $column['type'], $matches)) {
                    $rules[] = 'max:' . $matches[1];
                }
            } elseif (in_array($typeName, ['int', 'bigint', 'smallint'])) {
                $rules[] = 'integer';
            } elseif (in_array($typeName, ['decimal', 'float', 'double'])) {
                $rules[] = 'numeric';
            } elseif ($typeName === 'timestamp' || $typeName === 'datetime') {
                $rules[] = 'date';
            } elseif ($typeName === 'boolean') {
                $rules[] = 'boolean';
            }

            // Assign rules to the column
            $validationRules[$column['name']] = $rules;
        }

        // Output validation array
        $this->info("Validation rules for table '{$tableName}':");
        $output = str_replace(['":','{','}'], ['" =>','[',']'], json_encode($validationRules, JSON_PRETTY_PRINT));

        $this->line($output);
    }
}
