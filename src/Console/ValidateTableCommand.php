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
     * The laravel validation rule to map column types to.
     *
     * @var array
     */
    protected $columnTypeRules = [
        '_varchar' => 'string',
        'bigint' => 'integer',
        'bigserial' => 'integer',
        // 'binary' => '',
        'binary_double' => 'numeric',
        'binary_float' => 'numeric',
        'binary_integer' => 'integer',
        'bit' => 'boolean',
        // 'blob' => '',
        'bool' => 'boolean',
        'boolean' => 'boolean',
        'bpchar' => 'string',
        // 'bytea' => '',
        'char' => 'string',
        'character' => 'string',
        'clob' => 'string',
        'date' => 'date',
        'datetime' => 'date',
        'datetime2' => 'date',
        'datetimeoffset' => 'date',
        'decimal' => 'numeric',
        'double' => 'numeric',
        'double precision' => 'numeric',
        'float' => 'numeric',
        'float4' => 'numeric',
        'float8' => 'numeric',
        // 'image' => '',
        'inet' => 'string',
        'int' => 'integer',
        'int2' => 'integer',
        'int4' => 'integer',
        'int8' => 'integer',
        'integer' => 'integer',
        'interval' => 'string',
        'json' => 'array',
        'jsonb' => 'array',
        'long' => 'string',
        // 'long raw' => '',
        // 'longblob' => '',
        'longtext' => 'string',
        'longvarchar' => 'string',
        // 'mediumblob' => '',
        'mediumint' => 'integer',
        'mediumtext' => 'string',
        'money' => 'numeric',
        'nchar' => 'string',
        'nclob' => 'string',
        'ntext' => 'string',
        'number' => 'integer',
        'numeric' => 'numeric',
        'nvarchar' => 'string',
        'nvarchar2' => 'string',
        'pls_integer' => 'boolean',
        // 'raw' => '',
        'real' => 'numeric',
        'rowid' => 'string',
        'serial' => 'integer',
        'serial4' => 'integer',
        'serial8' => 'integer',
        'set' => 'array',
        'smalldatetime' => 'date',
        'smallint' => 'integer',
        'smallmoney' => 'integer',
        'string' => 'string',
        'text' => 'string',
        'time' => 'date_format:H:i',
        'timestamp' => 'date',
        'timestamptz' => 'date',
        'timetz' => 'date_format:H:i',
        // 'tinyblob' => '',
        'tinyint' => 'integer',
        'tinytext' => 'string',
        'tsvector' => 'string',
        'uniqueidentifier' => 'uuid',
        'urowid' => 'string',
        'uuid' => 'uuid',
        // 'varbinary' => '',
        'varchar' => 'string',
        'varchar2' => 'string',
        'year' => 'date',
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tableName = $this->argument('tableName');
        $exclude = ['id', 'uuid', 'ulid', 'created_at', 'updated_at', 'deleted_at'];
        if (! Schema::hasTable($tableName)) {
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
            if (array_key_exists($typeName, $this->columnTypeRules)) {
                $rules[] = $this->columnTypeRules[$typeName];
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
