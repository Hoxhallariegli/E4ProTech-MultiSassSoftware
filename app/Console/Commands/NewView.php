<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Models\Permission;
use App\Models\RealtimeEvent;
use Illuminate\Support\Facades\DB;

class NewView extends Command
{
    protected $signature = 'new:view {name} {--api : Generate API + Flutter BLoC mobile layer automatically} {--firebase : Enable Firebase notifications}';
    protected $description = 'Universal DDD Scaffolder - God Version (Pro UI, Nested Modals, Hardened)';

    protected array $reserved = [
        'class', 'function', 'array', 'return', 'if', 'else', 'elseif', 'parent', 'self', 'static',
        'string', 'int', 'integer', 'float', 'bool', 'boolean', 'object', 'null', 'true', 'false',
        'interface', 'trait', 'namespace', 'use', 'new', 'clone', 'match', 'enum', 'fn', 'yield',
        'id', 'created_at', 'updated_at', 'deleted_at',
    ];

    public function handle()
    {
        try {
            return $this->scaffold();
        } catch (\Throwable $e) {
            $this->error('Scaffolding failed: ' . $e->getMessage());
            $this->line($e->getFile() . ':' . $e->getLine());
            return 1;
        }
    }

    protected function scaffold()
    {
        $rawName = trim((string) $this->argument('name'));
        $clean = preg_replace('/[^A-Za-z0-9_\- ]/', '', $rawName);
        $name = Str::studly((string) $clean);

        if (!$name || !preg_match('/^[A-Z][A-Za-z0-9]*$/', $name)) {
            $this->error("Invalid model name '$rawName'. Use letters and numbers only.");
            return 1;
        }
        if (in_array(strtolower($name), $this->reserved, true)) {
            $this->error("Error: '$name' is a reserved word.");
            return 1;
        }

        $tableName = Str::snake(Str::pluralStudly($name));
        $pluralName = Str::plural($name);
        $pluralKebab = Str::kebab($pluralName);
        $pluralSnake = Str::snake($pluralName);

        if (Schema::hasTable($tableName)) {
            $this->error("Table '$tableName' already exists. Aborting to avoid a conflicting migration.");
            return 1;
        }

        // --- Overwrite guard ---
        $modelPath = app_path("Models/$name.php");
        $domainPath = app_path("Domain/$name");
        $livewirePath = app_path("Livewire/Admin/$pluralName");
        $viewsPath = resource_path('views/livewire/admin/' . Str::kebab($pluralName));
        $routePath = base_path("routes/admin/$pluralKebab.php");

        $existing = array_filter([
            $modelPath => File::exists($modelPath),
            $domainPath => File::isDirectory($domainPath),
            $livewirePath => File::isDirectory($livewirePath),
            $viewsPath => File::isDirectory($viewsPath),
            $routePath => File::exists($routePath),
        ]);

        if (!empty($existing)) {
            $this->warn('The following already exist and will be OVERWRITTEN:');
            foreach (array_keys($existing) as $p) {
                $this->line(" - $p");
            }
            if (!$this->confirm('Continue and overwrite these?', false)) {
                $this->info('Aborted. Nothing was changed.');
                return 1;
            }
        }

        $fields = [];
        $usedNames = ['id', 'created_at', 'updated_at', 'deleted_at'];
        $this->info("🏗️ Starting ENTERPRISE Scaffolding for $name");

        while (true) {
            $fieldNameRaw = trim((string) $this->ask('Field name (leave empty to finish)'));
            if ($fieldNameRaw === '') {
                break;
            }

            $fieldName = Str::snake($fieldNameRaw);
            if (!preg_match('/^[a-z][a-z0-9_]*$/', $fieldName)) {
                $this->error("Invalid field name '$fieldNameRaw'. Use lowercase letters, numbers and underscores, starting with a letter.");
                continue;
            }
            if (in_array($fieldName, $usedNames, true)) {
                $this->error("Field '$fieldName' is already used or reserved.");
                continue;
            }

            $type = $this->choice('Field type', [
                'string', 'text', 'integer', 'bigInteger', 'decimal', 'qty', 'price', 'boolean', 'image', 'date', 'datetime', 'foreignId', 'enum'
            ], 0);

            $extra = ''; $relatedModel = ''; $labelField = 'name'; $options = []; $isUuid = false;

            if ($type === 'foreignId') {
                $extra = Str::snake(trim((string) $this->ask('Constrained table', Str::snake(Str::pluralStudly(str_replace('_id', '', $fieldName))))));
                if (!Schema::hasTable($extra)) {
                    $this->warn("Warning: table '$extra' doesn't exist yet — migration will fail unless it's created before this one runs.");
                }
                $relatedModel = Str::studly(Str::singular($extra));
                if (!class_exists("App\\Models\\$relatedModel")) {
                    $this->warn("Warning: App\\Models\\$relatedModel doesn't exist yet — the relation/dropdown will error until it's scaffolded too.");
                } else {
                    $modelInstance = new ("App\\Models\\$relatedModel");
                    $isUuid = $modelInstance->getKeyType() === 'string' && !$modelInstance->getIncrementing();
                }
                $labelField = trim((string) $this->ask("Display field for $relatedModel (supports dot notation like employee.name)?", 'name'));
                if (!preg_match('/^[a-z][a-z0-9_]*(\.[a-z][a-z0-9_]*)?$/', $labelField)) {
                    $this->warn("Invalid display field '$labelField', falling back to 'name'.");
                    $labelField = 'name';
                }
            }

            // Semantic field conventions ALWAYS win over numeric menu indexes.
            // This is important for piped/automated input: the field name must determine
            // the persisted type and the generated Laravel + Livewire + Flutter widgets.
            if (in_array($fieldName, ['qty', 'quantity', 'price', 'amount', 'cost', 'unit_price'], true)) {
                $type = 'decimal';
            } elseif ($fieldName === 'priority') {
                $type = 'enum';
            } elseif (preg_match('/^(is|has|can|should|allow)_/', $fieldName) || in_array($fieldName, ['active', 'enabled'], true)) {
                $type = 'boolean';
            } elseif (preg_match('/(^|_)(due_date|start_date|end_date|date)(_|$)/', $fieldName)) {
                $type = 'date';
            } elseif (str_ends_with($fieldName, '_at') || preg_match('/(^|_)(datetime|timestamp)(_|$)/', $fieldName)) {
                $type = 'datetime';
            } elseif (in_array($fieldName, ['image', 'photo', 'avatar', 'cover_photo', 'thumbnail'], true)) {
                $type = 'image';
            }

            if ($type === 'image') {
                $extra = 'image';
            }

            if ($type === 'enum') {
                // Always consume the enum-options answer, including for priority.
                // This keeps piped PowerShell input aligned: options must not become
                // the answer to the following Nullable? prompt.
                $optRaw = (string) $this->ask('Enum options (comma separated)', 'Low, Medium, High');
                if ($fieldName === 'priority') {
                    $optRaw = 'Low, Medium, High';
                }
                $options = array_values(array_unique(array_filter(array_map(
                    fn ($o) => trim($o),
                    explode(',', $optRaw)
                ))));
                if (count($options) < 2) {
                    $this->error('Enum needs at least 2 non-empty, unique options. Field skipped.');
                    continue;
                }
            }

            $fields[] = [
                'name' => $fieldName, 'type' => $type, 'constrained' => $extra,
                'relatedModel' => $relatedModel, 'labelField' => $labelField,
                'options' => $options, 'nullable' => $this->confirm('Nullable?', false),
                'isUuid' => $isUuid
            ];
            $usedNames[] = $fieldName;
        }

        $iconRaw = (string) $this->ask('Menu icon', 'chevron-right');
        $iconClean = preg_replace('/[^a-z0-9\-]/', '', strtolower($iconRaw)) ?: 'chevron-right';
        $iconMap = [
            'box' => 'archive-box', 'file' => 'document', 'clipboard' => 'clipboard-document',
            'office-building' => 'building-office', 'desktop' => 'computer-desktop',
            'pencil' => 'pencil-square', 'chart-bar' => 'chart-bar'
        ];
        $icon = $iconMap[$iconClean] ?? $iconClean;

        $withApi = $this->option('api') || ($this->choice('Generate API?', ['No', 'Yes'], 0) === 'Yes');

        $this->generateDomainStructure($name, $fields);
        $this->generateMigration($tableName, $fields);
        $this->generateModel($name, $fields);
        $this->generateLivewireComponents($name, $pluralSnake, $pluralName, $pluralKebab, $fields);
        $this->generateViews($name, $fields, $pluralSnake, $pluralName);

        if ($withApi) {
            // --api is intentionally a full-stack scaffold:
            // Laravel API Resource + Controller + routes + Flutter BLoC/Cubit module.
            $this->generateApiLayer($name, $pluralName, $pluralKebab, $fields);
            $this->generateRealtimeLayer($name, $pluralName, $pluralKebab);
            $this->generateFlutterBLoCLayer($name, $pluralName, $pluralKebab, $fields, $icon);
        }

        $this->info('💾 Migrating...');
        $exitCode = $this->call('migrate');
        if ($exitCode !== 0) {
            $this->error('Migration failed. Files were generated but the table was not created.');
            $this->warn('Fix database/migrations manually, run "php artisan migrate", then re-add permissions/nav by hand if needed.');
            return 1;
        }

        $this->addPermissions($name, $pluralSnake);
        $this->generateTranslationFiles($pluralKebab, $name, $fields);
        $this->generateRouteFile($pluralName, $pluralKebab, $name, $pluralSnake);
        $this->addNavigation($pluralName, $pluralKebab, $pluralSnake, $icon);

        $this->info("✅ DONE! $name is ready with Modal Support.");
        return 0;
    }

    protected function generateDomainStructure($name, $fields)
    {
        $baseDir = app_path("Domain/$name");
        foreach (['Actions', 'DTOs', 'Queries', 'Events'] as $d) {
            File::makeDirectory("$baseDir/$d", 0755, true, true);
        }
        $this->generateDTO($name, $fields);
        $this->generateQuery($name, $fields);
        $this->generateActions($name, $fields);
    }

    protected function generateActions($name, array $fields)
    {
        $dir = app_path("Domain/$name/Actions");
        $plural = Str::plural($name);
        File::put("$dir/Create{$name}Action.php", "<?php\n\nnamespace App\Domain\\$name\Actions;\n\nuse App\Models\\$name;\nuse App\Domain\\$name\DTOs\\{$name}DTO;\nuse App\Models\AuditTrail;\n\nclass Create{$name}Action\n{\n    public function execute({$name}DTO \$dto): $name \n    {\n        \$item = $name::create(\$dto->toArray());\n        AuditTrail::log(\$item, 'create', '$plural');\n        return \$item;\n    }\n}");
        $imageCleanup = '';
        foreach (array_filter($fields, fn ($f) => $f['type'] === 'image') as $imageField) {
            $imageCleanup .= "        if (\$model->{$imageField['name']} && isset(\$data['{$imageField['name']}']) && \$data['{$imageField['name']}'] !== \$model->{$imageField['name']}) { app(\\App\\Services\\ImageUploadService::class)->delete(\$model->{$imageField['name']}); }\n";
        }
        File::put("$dir/Update{$name}Action.php", "<?php\n\nnamespace App\Domain\\$name\Actions;\n\nuse App\Models\\$name;\nuse App\Domain\\$name\DTOs\\{$name}DTO;\nuse App\Models\AuditTrail;\n\nclass Update{$name}Action\n{\n    public function execute($name \$model, {$name}DTO \$dto): $name\n    {\n        \$data = \$dto->toArray();\n$imageCleanup        \$model->fill(\$data);\n        AuditTrail::log(\$model, 'update', '$plural');\n        \$model->save();\n        return \$model->fresh();\n    }\n}");
        $imageCleanup = '';
        foreach (array_filter($fields, fn ($f) => $f['type'] === 'image') as $imageField) {
            $imageCleanup .= "        if (\$model->{$imageField['name']}) { app(\\App\\Services\\ImageUploadService::class)->delete(\$model->{$imageField['name']}); }\n";
        }
        File::put("$dir/Delete{$name}Action.php", "<?php\n\nnamespace App\Domain\\$name\Actions;\n\nuse App\Models\\$name;\nuse App\Models\AuditTrail;\n\nclass Delete{$name}Action\n{\n    public function execute($name \$model): bool \n    {\n$imageCleanup        AuditTrail::log(\$model, 'delete', '$plural');\n        return \$model->delete(); \n    }\n}");
    }

    protected function generateDTO($name, $fields)
    {
        $props = ''; $args = ''; $toArray = '';
        foreach ($fields as $f) {
            $props .= "        public readonly mixed \${$f['name']},\n";
            $args .= "            {$f['name']}: \$data['{$f['name']}'] ?? null,\n";
            $toArray .= "            '{$f['name']}' => \$this->{$f['name']},\n";
        }
        $stub = "<?php\n\nnamespace App\Domain\\$name\DTOs;\n\nclass {$name}DTO\n{\n    public function __construct(\n$props    ) {}\n    public static function fromArray(array \$data): self { return new self(\n$args        ); }\n    public function toArray(): array { return [\n$toArray        ]; }\n}";
        File::put(app_path("Domain/$name/DTOs/{$name}DTO.php"), $stub);
    }

    protected function generateQuery($name, $fields)
    {
        $with = collect($fields)->filter(fn ($f) => $f['type'] === 'foreignId')->map(function($f) {
            $rel = Str::camel(str_replace('_id', '', $f['name']));
            if (Str::contains($f['labelField'], '.')) {
                $subRel = Str::beforeLast($f['labelField'], '.');
                return "'$rel.$subRel'";
            }
            return "'$rel'";
        })->unique()->implode(', ');

        $searchFields = collect($fields)->filter(fn ($f) => in_array($f['type'], ['string', 'text']))->map(fn ($f) => "                \$query->orWhere('{$f['name']}', 'like', '%' . \$params['search'] . '%');")->implode("\n");
        $filters = collect($fields)->filter(fn ($f) => $f['type'] === 'foreignId')->map(fn ($f) => "        if (isset(\$params['{$f['name']}']) && \$params['{$f['name']}']) \$query->where('{$f['name']}', \$params['{$f['name']}']);")->implode("\n");
        File::put(app_path("Domain/$name/Queries/{$name}ListQuery.php"), "<?php\n\nnamespace App\Domain\\$name\Queries;\n\nuse App\Models\\$name;\nuse Illuminate\Database\Eloquent\Builder;\n\nclass {$name}ListQuery\n{\n    public function handle(array \$params = [], string \$sortField = 'id', string \$sortAsc = 'asc'): Builder\n    {\n        \$query = $name::query()" . ($with ? "->with([$with])" : '') . ";\n        if (isset(\$params['search']) && \$params['search']) {\n            \$query->where(function(\$query) use (\$params) {\n                \$query->where('id', 'like', '%' . \$params['search'] . '%');\n$searchFields\n            });\n        }\n$filters\n        \$sortField = in_array(\$sortField, $name::sortable(), true) ? \$sortField : 'id';\n        \$sortAsc = in_array(strtolower((string) \$sortAsc), ['asc', 'desc'], true) ? \$sortAsc : 'asc';\n        return \$query->orderBy(\$sortField, \$sortAsc);\n    }\n}");
    }

    protected function generateModel($name, $fields)
    {
        $fillable = collect($fields)->map(fn ($f) => "'{$f['name']}'")->implode(', ');
        $relations = ''; $casts = ''; $rules = ''; $sortable = "'id'";
        foreach ($fields as $f) {
            if ($f['type'] === 'foreignId') {
                $rel = Str::camel(str_replace('_id', '', $f['name']));
                $relations .= "\n    public function $rel(): \Illuminate\Database\Eloquent\Relations\BelongsTo { return \$this->belongsTo(\\App\Models\\{$f['relatedModel']}::class, '{$f['name']}'); }\n";
            }
            if ($f['type'] === 'boolean') {
                $casts .= "            '{$f['name']}' => 'boolean',\n";
            }
            if (in_array($f['type'], ['date', 'datetime'])) {
                $casts .= "            '{$f['name']}' => 'datetime',\n";
            }
            if ($f['type'] === 'decimal') {
                $casts .= "            '{$f['name']}' => 'decimal:2',\n";
            }

            $req = $f['nullable'] ? "'nullable'" : "'required'";
            if ($f['type'] === 'boolean') {
                $req = "'boolean'";
            }
            $typeRule = match ($f['type']) {
                'string', 'image' => "'string', 'max:255'",
                'text' => "'string'",
                'integer', 'bigInteger' => "'integer'",
                'boolean' => null, // Already handled by $req
                'decimal' => "'numeric'",
                'date', 'datetime' => "'date'",
                'foreignId' => ($f['isUuid'] ?? false) ? "'string'" : "'integer'",
                'enum' => "\\Illuminate\Validation\Rule::in(['" . implode("', '", array_map('addslashes', $f['options'])) . "'])",
                default => null,
            };
            $rules .= "            '{$f['name']}' => [$req" . ($typeRule ? ", $typeRule" : '') . "],\n";
            $sortable .= ", '{$f['name']}'";
        }
        File::put(app_path("Models/$name.php"), "<?php\n\nnamespace App\Models;\n\nuse Illuminate\Database\Eloquent\Factories\HasFactory;\nuse Illuminate\Database\Eloquent\Model;\n\nclass $name extends Model\n{\n    use HasFactory;\n    protected \$fillable = [$fillable];\n    protected function casts(): array { return [\n$casts        ]; }\n    public static function rules(\$id = null): array { return [\n$rules        ]; }\n    public static function sortable(): array { return [$sortable]; }\n\n    protected static function booted(): void\n    {\n        static::observe(\\App\\Observers\\{$name}Observer::class);\n    }\n$relations\n}");
    }

    protected function generateLivewireComponents($name, $pluralSnake, $pluralName, $pluralKebab, $fields)
    {
        $camel = Str::camel($name);
        $dir = app_path("Livewire/Admin/$pluralName");
        File::makeDirectory($dir, 0755, true, true);
        $viewPath = 'livewire.admin.' . Str::kebab($pluralName);

        $hasFile = collect($fields)->contains(fn ($f) => $f['type'] === 'image' || Str::contains(strtolower($f['name']), ['file', 'document', 'image', 'photo']));
        $availableListsIndex = ''; $availableListsForm = ''; $props = ''; $dataMap = ''; $fileHandlers = ''; $livewireRules = '';
        $filterReset = ''; $filterProps = ''; $renderFilters = '';

        foreach ($fields as $f) {
            $props .= "    public \${$f['name']} = '';\n";
            $dataMap .= "            '{$f['name']}' => \$this->{$f['name']},\n";
            if ($f['type'] === 'foreignId') {
                $rv = Str::plural(Str::camel(str_replace('_id', '', $f['name'])));

                if (Str::contains($f['labelField'], '.')) {
                    $relPart = explode('.', $f['labelField'])[0];
                    $availableListsIndex .= "            '{$rv}' => \\App\\Models\\{$f['relatedModel']}::with('$relPart')->get()->pluck('{$f['labelField']}', 'id')->toArray(),\n";
                } else {
                    $availableListsIndex .= "            '{$rv}' => \\App\\Models\\{$f['relatedModel']}::pluck('{$f['labelField']}', 'id')->toArray(),\n";
                }

                $availableListsForm .= "            '{$rv}' => \$this->get{$rv}List(),\n";
                $filterReset .= "'{$f['name']}', ";
                $filterProps .= "    #[Url(history: true)] public \${$f['name']} = '';\n";
                $renderFilters .= "            '{$f['name']}' => \$this->{$f['name']},\n";
            }
            if ($f['type'] === 'image') {
                $fileHandlers .= "        if (\$this->{$f['name']} && !is_string(\$this->{$f['name']})) { \$this->{$f['name']} = app(\\App\\Services\\ImageUploadService::class)->upload(\$this->{$f['name']}, 'uploads/$pluralKebab'); }\n";
                $livewireRules .= "        if (\$this->{$f['name']} instanceof \\Illuminate\\Http\\UploadedFile) { \$rules['{$f['name']}'] = " . ($f['nullable'] ? "['nullable', 'image', 'max:10240']" : "['required', 'image', 'max:10240']") . "; } elseif (is_string(\$this->{$f['name']}) && \$this->{$f['name']} !== '') { \$rules['{$f['name']}'] = ['nullable', 'string', 'max:255']; } else { \$rules['{$f['name']}'] = " . ($f['nullable'] ? "['nullable']" : "['required']") . "; }\n";
            }
        }

        $imports = "use Livewire\Component;\nuse Livewire\WithPagination;\nuse Livewire\Attributes\Title;\nuse Livewire\Attributes\Url;\nuse Livewire\Attributes\On;\n";
        if ($hasFile) {
            $imports .= "use Livewire\WithFileUploads;\n";
        }
        $traits = '    use WithPagination' . ($hasFile ? ', WithFileUploads' : '') . ";\n";

        // $onEvents / $formHelperMethods are shared by EVERY component that has a
        // foreignId field, including QuickCreate. This is what makes nested \"create
        // related record from inside a modal\" propagate the new id back into the
        // field that opened it, at any nesting depth.
        $formHelperMethods = ''; $onEvents = ''; $updatedHooks = '';
        foreach ($fields as $f) {
            if ($f['type'] === 'foreignId') {
                $rv = Str::plural(Str::camel(str_replace('_id', '', $f['name'])));
                $onEvents .= "\n    #[On('" . Str::kebab($f['relatedModel']) . "-created')] \n    public function refresh" . Str::studly($rv) . "(\$id) { \$this->{$f['name']} = \$id; \$this->updated" . Str::studly($f['name']) . "(\$id); }\n";

                // Add updated hook for auto-filling related fields
                $updatedHooks .= "\n    public function updated" . Str::studly($f['name']) . "(\$value)\n    {\n        if (!\$value) return;\n        \$related = \\App\\Models\\{$f['relatedModel']}::find(\$value);\n        if (!\$related) return;\n";
                foreach ($fields as $otherField) {
                    if ($otherField['name'] !== $f['name'] && $otherField['type'] === 'foreignId') {
                        // If the related model has a field with the same name as our other field, auto-fill it
                        $updatedHooks .= "        if (isset(\$related->{$otherField['name']})) { \$this->{$otherField['name']} = \$related->{$otherField['name']}; }\n";
                    }
                }
                $updatedHooks .= "    }\n";

                $formHelperMethods .= "\n    protected function get{$rv}List() {\n";
                if (Str::contains($f['labelField'], '.')) {
                    $relPart = explode('.', $f['labelField'])[0];
                    $formHelperMethods .= "        return \\App\\Models\\{$f['relatedModel']}::with('$relPart')->get()->pluck('{$f['labelField']}', 'id')->toArray();\n";
                } else {
                    $formHelperMethods .= "        return \\App\\Models\\{$f['relatedModel']}::pluck('{$f['labelField']}', 'id')->toArray();\n";
                }
                $formHelperMethods .= "    }\n";
            }
        }

        $indexStub = "<?php\n\nnamespace App\Livewire\Admin\\$pluralName;\n\nuse App\Models\\$name;\nuse App\Domain\\$name\Queries\\{$name}ListQuery;\nuse App\Domain\\$name\Actions\\Delete{$name}Action;\n$imports\n#[Title('$pluralName')]\nclass $pluralName extends Component\n{\n    $traits\n    public int \$paginate = 10;\n    #[Url(history: true)] public string \$search = '';\n$filterProps    public bool \$openFilter = false;\n    public string \$sortField = 'id';\n    public bool \$sortAsc = true;\n\n    public function resetFilters() { \$this->reset(['search', 'openFilter', $filterReset]); \$this->resetPage(); }

    #[On('echo-private:mobile.{$pluralKebab},.{$pluralKebab}.changed')]
    public function onBroadcastCloudChange(\$event)
    {
        // Realtime refresh when DB changes via APK or other sessions
        \$this->render();
    }

    public function render()
    {\n        abort_if_cannot('view_{$pluralSnake}');\n        \$query = (new {$name}ListQuery())->handle(['search' => \$this->search, $renderFilters], \$this->sortField, \$this->sortAsc ? 'asc' : 'desc');\n\n        return view('$viewPath.index', [\n            'items' => \$query->paginate(\$this->paginate),\n            'sortableFields' => $name::sortable(),\n$availableListsIndex        ])->layout('components.layouts.app');\n    }\n\n    public function sortBy(\$field) { if (!in_array(\$field, $name::sortable(), true)) return; if (\$this->sortField === \$field) { \$this->sortAsc = ! \$this->sortAsc; } \$this->sortField = \$field; }\n\n    public function delete$name(\$id, Delete{$name}Action \$action) \n    {\n        abort_if_cannot('delete_{$pluralSnake}');\n        \$item = $name::find(\$id);\n        if (!\$item) { \$this->dispatch('toast', message: __('$pluralKebab.not_found'), type: 'error'); return; }\n        try { \$action->execute(\$item); \$this->dispatch('toast', message: __('$pluralKebab.deleted'), type: 'success'); \$this->resetPage(); } \n        catch (\\Illuminate\\Database\\QueryException \$e) { \$this->dispatch('toast', message: __('$pluralKebab.delete_error_referenced'), type: 'error'); }\n        catch (\\Exception \$e) { \$this->dispatch('toast', message: __('$pluralKebab.delete_error'), type: 'error'); }\n    }\n}";
        File::put("$dir/$pluralName.php", $indexStub);

        File::put("$dir/Create.php", "<?php\n\nnamespace App\Livewire\Admin\\$pluralName;\n\nuse App\Models\\$name;\nuse App\Domain\\$name\DTOs\\{$name}DTO;\nuse App\Domain\\$name\Actions\\Create{$name}Action;\n$imports\n#[Title('Add $name')]\nclass Create extends Component\n{\n    $traits $props $onEvents $updatedHooks $formHelperMethods\n    public function render() { abort_if_cannot('add_{$pluralSnake}'); return view('$viewPath.create', [\n$availableListsForm        ])->layout('components.layouts.app'); }\n    public function store(Create{$name}Action \$action) { \$this->validate(); $fileHandlers \$dto = {$name}DTO::fromArray([\n$dataMap        ]); \$action->execute(\$dto); session()->flash('success', __('$pluralKebab.created')); return to_route('admin.$pluralKebab.index'); }\n    protected function rules(): array { \$rules = $name::rules();
$livewireRules        return \$rules; }\n}");

        $dateFills = collect($fields)->filter(fn ($f) => in_array($f['type'], ['date', 'datetime']))->map(function ($f) use ($camel) {
            $fmt = $f['type'] === 'datetime' ? "'Y-m-d\\TH:i'" : "'Y-m-d'";
            return "\$this->{$f['name']} = \$" . $camel . "->{$f['name']}?->format($fmt);";
        })->implode(' ');
        File::put("$dir/Edit.php", "<?php\n\nnamespace App\Livewire\Admin\\$pluralName;\n\nuse App\Models\\$name;\nuse App\Domain\\$name\DTOs\\{$name}DTO;\nuse App\Domain\\$name\Actions\\Update{$name}Action;\n$imports\n#[Title('Edit $name')]\nclass Edit extends Component\n{\n    $traits public $name \$item;\n$props $onEvents $updatedHooks $formHelperMethods\n    public function mount($name \$" . $camel . ") { \$this->item = \$" . $camel . "; \$this->fill(\$" . $camel . "->toArray()); $dateFills }\n    public function render() { abort_if_cannot('edit_{$pluralSnake}'); return view('$viewPath.edit', [\n$availableListsForm        ])->layout('components.layouts.app'); }\n    public function update(Update{$name}Action \$action) { \$this->validate(); $fileHandlers \$dto = {$name}DTO::fromArray([\n$dataMap        ]); \$action->execute(\$this->item, \$dto); session()->flash('success', __('$pluralKebab.updated')); return to_route('admin.$pluralKebab.index'); }\n    protected function rules(): array { \$rules = $name::rules(\$this->item->id);
$livewireRules        return \$rules; }\n}");
        File::put("$dir/Row.php", "<?php\n\nnamespace App\Livewire\Admin\\$pluralName;\n\nuse App\Models\\$name;\nuse Livewire\Component;\n\nclass Row extends Component { public $name \$item; public function render() { return view('$viewPath.row'); } }");

        // QuickCreate — the modal/nested version. It now gets $onEvents + $formHelperMethods
        // too, exactly like Create/Edit, so any foreignId field inside a modal can ALSO
        // listen for its own nested \"-created\" events and resolve its own dropdown lists.
        //
        // It no longer dispatches 'close-modal' — the modal stays open after a successful
        // save, shows an inline success state (see quick-create.blade.php), and is closed
        // only by the user (via the modal's own X button). \$this->reset() only clears the
        // input fields, not \$created/\$createdId/\$createdLabel, so the success state sticks.
        $displayField = 'id';
        foreach ($fields as $f) {
            if (in_array($f['name'], ['name', 'title', 'label'], true)) {
                $displayField = $f['name'];
                break;
            }
        }
        $resetList = collect($fields)->map(fn ($f) => "'{$f['name']}'")->implode(', ');

        $kebabName = Str::kebab($name);
        File::put("$dir/QuickCreate.php", "<?php\n\nnamespace App\Livewire\Admin\\$pluralName;\n\nuse App\Models\\$name;\nuse App\Domain\\$name\DTOs\\{$name}DTO;\nuse App\Domain\\$name\Actions\\Create{$name}Action;\n$imports\nclass QuickCreate extends Component\n{\n    $traits $props $onEvents $updatedHooks $formHelperMethods\n    public bool \$created = false;\n    public ?int \$createdId = null;\n    public string \$createdLabel = '';\n\n    public function render() { return view('$viewPath.quick-create', [\n$availableListsForm        ]); }\n\n    public function store(Create{$name}Action \$action)\n    {\n        \$this->validate();\n$fileHandlers        \$dto = {$name}DTO::fromArray([\n$dataMap        ]);\n        \$item = \$action->execute(\$dto);\n        \$this->dispatch('$kebabName-created', id: \$item->id);\n        \$this->js(\"Livewire.dispatch('$kebabName-created', { id: {\$item->id} })\");\n        \$this->dispatch('toast', message: __('$pluralKebab.created'), type: 'success');\n        \$this->created = true;\n        \$this->createdId = \$item->id;\n        \$this->createdLabel = (string) (\$item->{$displayField} ?? \$item->id);\n        \$this->reset([$resetList]);\n    }\n\n    public function addAnother()\n    {\n        \$this->created = false;\n        \$this->createdId = null;\n        \$this->createdLabel = '';\n    }\n\n    protected function rules(): array { \$rules = $name::rules();
$livewireRules        return \$rules; }\n}");
    }

    protected function generateViews($name, $fields, $pluralSnake, $pluralName)
    {
        $dir = resource_path('views/livewire/admin/' . Str::kebab($pluralName));
        File::makeDirectory($dir, 0755, true, true);
        $pk = Str::kebab($pluralName);

        File::put("$dir/index.blade.php", $this->getIndexStub($name, $pluralName, $pk, $fields));
        File::put("$dir/create.blade.php", $this->getCreateStub($name, $pk, $fields));
        File::put("$dir/edit.blade.php", $this->getEditStub($name, $pk, $fields));
        File::put("$dir/row.blade.php", $this->getRowStub($name, $pk, $fields, $pluralSnake));
        File::put("$dir/quick-create.blade.php", "<div class=\"p-6\">\n    @if(\$created)\n        <div class=\"flex flex-col items-center text-center py-10\">\n            <div class=\"w-12 h-12 rounded-full bg-green-50 dark:bg-green-900/30 flex items-center justify-center mb-4\">\n                <x-heroicon-o-check class=\"w-6 h-6 text-green-500\" />\n            </div>\n            <p class=\"font-bold text-gray-900 dark:text-white\">{{ __('$pk.created') }}</p>\n            @if(\$createdLabel)<p class=\"text-sm text-gray-500 dark:text-gray-400 mt-1\">{{ \$createdLabel }}</p>@endif\n            <button type=\"button\" wire:click=\"addAnother\" class=\"mt-6 text-xs font-black uppercase tracking-widest text-blue-600 dark:text-blue-400\">{{ __('$pk.Add $name') }}</button>\n        </div>\n    @else\n        " . $this->getInputs($fields, $pk, true) . "\n        <div class=\"mt-8 flex justify-end\"><x-button wire:click=\"store\" variant=\"blue\">{{ __('$pk.Save') }}</x-button></div>\n    @endif\n</div>");
    }

    protected function getIndexStub($name, $pluralName, $pk, $fields)
    {
        $searchable = collect($fields)->filter(fn ($f) => in_array($f['type'], ['string', 'text']))->map(fn ($f) => Str::title($f['name']))->prepend('ID')->implode(', ');
        $filters = collect($fields)->filter(fn ($f) => $f['type'] === 'foreignId')->map(function ($f) use ($pk) {
            $rv = Str::plural(Str::camel(str_replace('_id', '', $f['name'])));
            $label = Str::title(str_replace('_', ' ', $f['name']));
            return "<div><label class=\"block mb-1.5 text-[10px] font-bold uppercase tracking-widest ml-1 text-gray-900 dark:text-gray-100\">$label</label><x-form.dropdown-search name=\"{$f['name']}\" wire:model.live=\"{$f['name']}\" label=\"none\" :data=\"\${$rv}\" placeholder=\"Filter $label\" /></div>";
        })->implode("\n");

        return "<div x-data=\"{ openFilter: @entangle('openFilter') }\">\n    <div class=\"card !p-0 overflow-hidden shadow-none border-gray-200 dark:border-gray-700 dark:bg-gray-800\">\n        <div class=\"p-6\">\n            <div class=\"flex flex-col sm:flex-row sm:items-center justify-between gap-4\">\n                <div><x-h1>{{ __('$pk.$pluralName') }}</x-h1><x-short-description class=\"dark:text-gray-400\">{{ __('$pk.List of') }} ".strtolower($pluralName)."</x-short-description></div>\n                <div class=\"flex items-center gap-3\">\n                    @if(\$search || \$openFilter)\n                        <button wire:click=\"resetFilters\" class=\"inline-flex items-center gap-1.5 px-3 py-2 text-xs font-bold text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-2xl transition-none shadow-none\"><span>{{ __('$pk.Reset') }}</span></button>\n                    @endif\n                    <button @click=\"openFilter = !openFilter\" class=\"inline-flex items-center gap-2 px-4 py-2.5 text-sm font-semibold text-gray-600 bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-2xl shadow-sm transition-none\"><span>{{ __('$pk.Filters') }}</span></button>\n                    <x-btn :href=\"route('admin.$pk.create')\" icon=\"plus\">{{ __('$pk.Add $name') }}</x-btn>\n                </div>\n            </div>\n\n            <div x-show=\"openFilter\" x-cloak class=\"mt-6 p-6 bg-gray-50 dark:bg-gray-900 border border-gray-100 dark:border-gray-700 rounded-2xl\">\n                <div class=\"grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6\">\n                    <div>\n                        <label class=\"block mb-1.5 text-[10px] font-bold uppercase tracking-widest ml-1 text-gray-900 dark:text-gray-100\">{{ __('$pk.Search') }}</label>\n                        <input name=\"search\" wire:model.live.debounce.300ms=\"search\" type=\"text\" placeholder=\"Search by $searchable\" class=\"w-full p-3 text-sm font-bold bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl focus:ring-2 focus:ring-blue-500/20 dark:text-white\">\n                    </div>\n                    $filters\n                </div>\n            </div>\n        </div>\n\n        @include('errors.messages')\n\n        <div class=\"overflow-x-auto border-t border-gray-100 dark:border-gray-700\">\n            <table class=\"w-full text-sm text-left text-gray-500 dark:text-gray-400\">\n                <thead class=\"bg-gray-100/50 dark:bg-gray-700/50\"><tr><x-table.th name=\"id\" :label=\"__('$pk.ID')\" :\$sortField :\$sortAsc :sortable=\"true\" />" . collect($fields)->map(fn ($f) => "<x-table.th name=\"{$f['name']}\" :label=\"__('$pk.".Str::title(str_replace('_', ' ', $f['name']))."')\" :\$sortField :\$sortAsc :sortable=\"in_array('{$f['name']}', \$sortableFields)\" />")->implode("\n") . "<th class=\"px-6 py-4 text-right text-[10px] font-black uppercase text-gray-400 tracking-widest\">{{ __('$pk.Action') }}</th></tr></thead>\n                <tbody class=\"divide-y divide-gray-50 dark:divide-gray-700/50\">@forelse(\$items as \$item) <livewire:admin.$pk.row :\$item :key=\"\$item->id\" /> @empty <tr><td colspan=\"100\" class=\"px-6 py-10 text-center text-sm text-gray-400\">{{ __('$pk.No records found.') }}</td></tr> @endforelse</tbody>\n            </table>\n        </div>\n        <div class=\"p-4 border-t border-gray-50 dark:border-gray-700/50\">{{ \$items->links() }}</div>\n    </div>\n</div>";
    }

    protected function getCreateStub($name, $pk, $fields)
    {
        return "<div class=\"space-y-10\">\n    <div class=\"flex items-center justify-between gap-4 px-1\"><div><x-h1>{{ __('$pk.Add $name') }}</x-h1><x-short-description class=\"dark:text-gray-400\">{{ __('$pk.New record') }}</x-short-description></div><x-back-btn route=\"admin.$pk.index\" /></div>\n    @include('errors.errors')\n    <div class=\"bg-white dark:bg-gray-800 p-8 sm:p-12 rounded-[2.5rem] shadow-sm border border-gray-50 dark:border-gray-700\"><form wire:submit.prevent=\"store\" class=\"space-y-8\">".$this->getInputs($fields, $pk, true)."<div class=\"mt-10 flex justify-end\"><x-button type=\"submit\" variant=\"blue\" class=\"w-full sm:w-auto !px-12 !py-4 !rounded-2xl\">{{ __('$pk.Save') }}</x-button></div></form></div>\n</div>";
    }

    protected function getEditStub($name, $pk, $fields)
    {
        return "<div class=\"space-y-10\">\n    <div class=\"flex items-center justify-between gap-4 px-1\"><div><x-h1>{{ __('$pk.Edit $name') }}</x-h1><x-short-description class=\"dark:text-gray-400\">{{ __('$pk.Update info') }}</x-short-description></div><x-back-btn route=\"admin.$pk.index\" /></div>\n    @include('errors.errors')\n    <div class=\"bg-white dark:bg-gray-800 p-8 sm:p-12 rounded-[2.5rem] shadow-sm border border-gray-50 dark:border-gray-700\"><form wire:submit.prevent=\"update\" class=\"space-y-8\">".$this->getInputs($fields, $pk, false)."<div class=\"mt-10 flex justify-end\"><x-button type=\"submit\" variant=\"blue\" class=\"w-full sm:w-auto !px-12 !py-4 !rounded-2xl\">{{ __('$pk.Update') }}</x-button></div></form></div>\n</div>";
    }

    protected function getRowStub($name, $pk, $fields, $pluralSnake)
    {
        $cells = collect($fields)->map(function ($f) {
            if ($f['type'] === 'foreignId') {
                $labelPath = str_replace('.', '?->', $f['labelField']);
                return "<td class=\"px-6 py-5 font-bold text-gray-900 dark:text-white\">{{ \$item->".Str::camel(str_replace('_id', '', $f['name']))."?->{$labelPath} ?? '-' }}</td>";
            }
            if ($f['type'] === 'image' || Str::contains(strtolower($f['name']), ['file', 'document', 'image', 'photo'])) {
                return "<td class=\"px-6 py-3\">@if(\$item->{$f['name']}) <a href=\"{{ asset(ltrim(\$item->{$f['name']}, '/')) }}\" target=\"_blank\" rel=\"noopener\" class=\"inline-block group\" title=\"View image\"><img src=\"{{ asset(ltrim(\$item->{$f['name']}, '/')) }}\" alt=\"{{ e(ucwords(str_replace('_', ' ', '{$f['name']}'))) }}\" class=\"w-12 h-12 rounded-xl object-cover border border-gray-200 dark:border-gray-700 shadow-sm group-hover:scale-105 transition-transform duration-150\" loading=\"lazy\" /></a> @else <span class=\"text-gray-400\">-</span> @endif</td>";
            }
            if (in_array($f['type'], ['date', 'datetime'])) {
                return "<td class=\"px-6 py-5 text-gray-600 dark:text-gray-300\">{{ \$item->{$f['name']}?->format('d/m/Y H:i') ?? '-' }}</td>";
            }
            return "<td class=\"px-6 py-5 text-gray-600 dark:text-gray-300\">{{ \$item->{$f['name']} }}</td>";
        })->implode("\n");

        $displayNameField = 'id';
        foreach ($fields as $f) {
            if (in_array($f['name'], ['name', 'title', 'label'], true)) {
                $displayNameField = $f['name'];
            }
        }

        return "<tr class=\"hover:bg-gray-50/50 dark:hover:bg-gray-900/50 transition-none border-b border-gray-50 dark:border-gray-700/50 last:border-none\">\n    <td class=\"px-6 py-5 font-bold text-blue-600 dark:text-blue-400\">{{ \$item->id }}</td>\n    $cells\n    <td class=\"px-6 py-5 text-right !transition-none\">\n        <div class=\"flex justify-end gap-3 !transition-none\">\n            @can('edit_{$pluralSnake}')\n                <x-a href=\"{{ route('admin.$pk.edit', \$item) }}\" class=\"!rounded-xl !bg-blue-50 dark:!bg-blue-900/30 !text-blue-600 dark:!text-blue-400 !px-4 !py-1.5 !text-[10px] !font-black !uppercase !border-none\">Edit</x-a>\n            @endcan\n            @can('delete_{$pluralSnake}')\n                <div x-data=\"{ confirmation: '' }\" x-cloak class=\"inline-block\">\n                    <x-modal>\n                        <x-slot name=\"trigger\"><button @click=\"on = true\" class=\"text-[10px] font-black uppercase text-red-400 hover:text-red-600 dark:hover:text-red-300\">Delete</button></x-slot>\n                        <x-slot name=\"modalTitle\"><div class=\"text-left dark:text-white\">Delete {{ \$item->{$displayNameField} }}?</div></x-slot>\n                        <x-slot name=\"content\"><div class=\"text-left space-y-2\"><p class=\"text-sm text-gray-500 dark:text-gray-400\">This action cannot be undone.</p><input x-model=\"confirmation\" placeholder=\"Type {{ \$item->{$displayNameField} }} to confirm\" class=\"w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:ring-2 focus:ring-red-500 outline-none\"></div></x-slot>\n                        <x-slot name=\"footer\"><x-button variant=\"gray\" @click=\"on = false\">Cancel</x-button><x-button variant=\"red\" x-bind:disabled=\"confirmation !== '{{ \$item->{$displayNameField} }}'\" wire:click=\"\$parent.delete$name('{{ \$item->id }}')\" @click=\"on = false\">Delete</x-button></x-slot>\n                    </x-modal>\n                </div>\n            @endcan\n        </div>\n    </td>\n</tr>";
    }

    /**
     * NOTE: the old \$inModal flag used to HIDE the \"+\" add button whenever a field
     * was rendered inside a QuickCreate modal — that's what blocked adding e.g. a
     * missing Brand while inside the Vehicle quick-create. The \"+\" now always renders
     * for foreignId fields, so modals can nest (Vehicle -> Model field -> Brand modal)
     * as deep as the data actually needs.
     */
    protected function getInputs($fields, $pk, $isCreating = true)
    {
        $inputs = '<div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-8">';
        $inputs .= collect($fields)->map(function ($f) use ($pk, $isCreating) {
            $label = Str::title(str_replace('_', ' ', $f['name']));
            if ($f['type'] === 'foreignId') {
                $rv = Str::plural(Str::camel(str_replace('_id', '', $f['name'])));
                $comp = 'admin.' . Str::kebab(Str::plural($f['relatedModel'])) . '.quick-create';

                return "<div>\n    <div class=\"flex items-end gap-2\">\n        <div class=\"flex-1\"><x-form.dropdown-search name=\"{$f['name']}\" wire:model.live=\"{$f['name']}\" :label=\"__('$pk.$label')\" :data=\"\${$rv}\" /></div>\n        <x-modal>\n            <x-slot name=\"trigger\"><button type=\"button\" @click=\"on = true\" class=\"mb-6 p-3 bg-blue-50 dark:bg-zinc-900/30 text-blue-600 dark:text-blue-400 rounded-2xl hover:scale-105 transition-transform\"><x-heroicon-o-plus class=\"w-5 h-5\" /></button></x-slot>\n            <x-slot name=\"modalTitle\"><div class=\"dark:text-white px-6 pt-6\">Add New " . $f['relatedModel'] . "</div></x-slot>\n            <x-slot name=\"content\"><livewire:$comp /></x-slot>\n        </x-modal>\n    </div>\n</div>";
            }
            if ($f['type'] === 'image' || Str::contains(strtolower($f['name']), ['file', 'document', 'image', 'photo'])) {
                $editing = $isCreating ? 'false' : 'true';
                return "<div><x-file-upload name=\"{$f['name']}\" wire:model=\"{$f['name']}\" :label=\"__('$pk.$label')\" id=\"{$f['name']}\" :isEditing=\"$editing\" /></div>";
            }
            if ($f['type'] === 'text') {
                return "<div class=\"md:col-span-2\"><x-form.textarea name=\"{$f['name']}\" wire:model=\"{$f['name']}\" :label=\"__('$pk.$label')\" class=\"dark:bg-gray-900\" /></div>";
            }
            if ($f['type'] === 'boolean') {
                return "<div><x-form.checkbox name=\"{$f['name']}\" wire:model=\"{$f['name']}\" :label=\"__('$pk.$label')\" /></div>";
            }
            if ($f['type'] === 'enum') {
                $opts = collect($f['options'])->map(fn ($o) => '<option value="' . e($o) . '">' . e($o) . '</option>')->implode('');
                return "<div><label class=\"block mb-1.5 text-[10px] font-bold uppercase tracking-widest\">{{ __('$pk.$label') }}</label><select name=\"{$f['name']}\" wire:model=\"{$f['name']}\" class=\"w-full p-3 text-sm font-bold bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 rounded-2xl\"><option value=\"\">--</option>$opts</select></div>";
            }
            if (in_array($f['type'], ['qty', 'price', 'decimal'], true)) {
                return "<div><x-form.input name=\"{$f['name']}\" type=\"number\" step=\"0.01\" min=\"0\" inputmode=\"decimal\" oninput=\"this.value=this.value.replace(/[^0-9.]/g,'').replace(/(\\..*)\\./g,'$1').replace(/(\\.\\d{2}).*/g,'$1')\" wire:model.live=\"{$f['name']}\" :label=\"__('$pk.$label')\" class=\"dark:bg-gray-900\" /></div>";
            }
            $type = ($f['type'] === 'datetime') ? 'datetime-local' : (($f['type'] === 'date') ? 'date' : 'text');
            return "<div><x-form.input name=\"{$f['name']}\" type=\"$type\" wire:model.live=\"{$f['name']}\" :label=\"__('$pk.$label')\" class=\"dark:bg-gray-900\" /></div>";
        })->implode("\n");
        return $inputs . '</div>';
    }

    protected function generateTranslationFiles($pk, $name, $fields)
    {
        $locales = ['en', 'sq'];
        if (class_exists(\App\Models\Setting::class)) {
            $setting = \App\Models\Setting::where('key', 'supported_locales')->value('value');
            if ($setting) {
                $locales = json_decode($setting, true) ?: ['en', 'sq'];
            }
        }

        foreach ($locales as $lang) {
            $dir = lang_path($lang);
            File::makeDirectory($dir, 0755, true, true);
            $path = "$dir/$pk.php";

            $existing = [];
            if (File::exists($path)) {
                try {
                    $loaded = include $path;
                    $existing = is_array($loaded) ? $loaded : [];
                } catch (\Throwable $e) {
                    $this->warn("Could not read existing $path ({$e->getMessage()}) — treating as empty, nothing will be lost since we still merge below.");
                }
            }

            $defaults = [
                'ID' => 'ID', $name => $name, Str::plural($name) => Str::plural($name), 'Action' => 'Action',
                'Reset' => 'Reset', 'Filters' => 'Filters', 'Search' => 'Search', 'List of' => 'List of',
                'Save' => 'Save', 'Update' => 'Update', 'Add ' . $name => 'Add ' . $name, 'Edit ' . $name => 'Edit ' . $name,
                'New record' => 'New record', 'Update info' => 'Update info', 'No records found.' => 'No records found.',
                'created' => $name . ' created.', 'updated' => $name . ' updated.', 'deleted' => $name . ' deleted.',
                'not_found' => 'Record not found.',
                'delete_error_referenced' => 'Record is referenced by other items and cannot be deleted.',
                'delete_error' => 'Could not delete record.',
            ];
            foreach ($fields as $f) {
                $l = Str::title(str_replace('_', ' ', $f['name']));
                $defaults[$l] = $l;
            }

            // Existing values win for any key already translated. Only genuinely
            // new keys (new fields, new UI strings) get the generated default.
            $merged = array_merge($defaults, $existing);

            if ($existing !== [] && $merged === $existing) {
                $this->line("$path already has every key — left untouched.");
                continue;
            }
            if ($existing !== []) {
                $this->line("$path merged — existing translations preserved, new keys added.");
            }

            $content = "<?php\n\nreturn " . var_export($merged, true) . ";\n";
            $content = str_replace(['array (', ')'], ['[', ']'], $content);
            File::put($path, $content);
        }
    }

    protected function generateFlutterTranslationFiles($l10nDir, $snake, $name, $pluralName, array $fields): void
    {
        File::makeDirectory($l10nDir, 0755, true, true);

        $locales = ['en', 'sq'];
        if (class_exists(\App\Models\Setting::class)) {
            $setting = \App\Models\Setting::where('key', 'supported_locales')->value('value');
            if ($setting) {
                $locales = json_decode($setting, true) ?: ['en', 'sq'];
            }
        }

        $fieldLabel = fn ($field) => Str::headline($field);

        $en = [
            'common.yes' => 'Yes',
            'common.no' => 'No',
            'list.title' => Str::headline($pluralName),
            'list.subtitle' => 'Manage your ' . Str::lower(Str::headline($pluralName)),
            'list.filters' => 'Filters',
            'list.refresh' => 'Refresh',
            'list.add' => 'Add',
            'list.search_hint' => 'Search ' . Str::headline($pluralName) . '...',
            'list.clear' => 'Clear',
            'list.apply' => 'Apply',
            'list.nothing' => 'Nothing here yet',
            'list.no_results' => 'No results found',
            'list.create_first' => 'Create your first record to get started.',
            'list.try_different' => 'Try a different search phrase.',
            'list.create_record' => 'Create record',
            'list.edit' => 'Edit',
            'list.delete' => 'Delete',
            'list.delete_confirm_title' => 'Delete record?',
            'list.delete_confirm_message' => 'This action cannot be undone. ID: {id}',
            'form.create_title' => 'Create ' . Str::headline($name),
            'form.edit_title' => 'Edit ' . Str::headline($name),
            'form.update_record' => 'Update record',
            'form.new_record' => 'New record',
            'form.review_update' => 'Review and update the information below.',
            'form.fill_create' => 'Fill in the information to create a new record.',
            'form.save' => 'Save',
            'form.saving' => 'Saving...',
            'form.required' => 'Required',
            'form.invalid_integer' => 'Invalid integer',
            'form.invalid_decimal' => 'Enter a valid number with up to 2 decimals',
            'form.select' => 'Please select a value',
            'form.select_date' => 'Select date',
            'form.select_datetime' => 'Select date & time',
            'form.search' => 'Search...',
            'form.could_not_load' => 'Could not load form data',
            'form.save_error' => 'Could not save this record. Please check the highlighted fields.',
        ];

        $sq = [
            'common.yes' => 'Po',
            'common.no' => 'Jo',
            'list.title' => Str::headline($pluralName),
            'list.subtitle' => 'Menaxho ' . Str::lower(Str::headline($pluralName)),
            'list.filters' => 'Filtrat',
            'list.refresh' => 'Rifresko',
            'list.add' => 'Shto',
            'list.search_hint' => 'Kërko ' . Str::headline($pluralName) . '...',
            'list.clear' => 'Pastro',
            'list.apply' => 'Apliko',
            'list.nothing' => 'Nuk ka të dhëna ende',
            'list.no_results' => 'Nuk u gjetën rezultate',
            'list.create_first' => 'Krijo regjistrimin e parë për të filluar.',
            'list.try_different' => 'Provo një kërkim tjetër.',
            'list.create_record' => 'Krijo regjistrim',
            'list.edit' => 'Ndrysho',
            'list.delete' => 'Fshi',
            'list.delete_confirm_title' => 'Të fshihet regjistrimi?',
            'list.delete_confirm_message' => 'Ky veprim nuk mund të zhbëhet. ID: {id}',
            'form.create_title' => 'Krijo ' . Str::headline($name),
            'form.edit_title' => 'Ndrysho ' . Str::headline($name),
            'form.update_record' => 'Përditëso regjistrimin',
            'form.new_record' => 'Regjistrim i ri',
            'form.review_update' => 'Kontrollo dhe përditëso informacionin më poshtë.',
            'form.fill_create' => 'Plotëso informacionin për të krijuar regjistrim të ri.',
            'form.save' => 'Ruaj',
            'form.saving' => 'Duke ruajtur...',
            'form.required' => 'E detyrueshme',
            'form.invalid_integer' => 'Numër i plotë i pavlefshëm',
            'form.invalid_decimal' => 'Vendos një numër të vlefshëm me deri në 2 shifra dhjetore',
            'form.select' => 'Zgjidh një vlerë',
            'form.select_date' => 'Zgjidh datën',
            'form.select_datetime' => 'Zgjidh datën dhe orën',
            'form.search' => 'Kërko...',
            'form.could_not_load' => 'Nuk u ngarkuan të dhënat e formularit',
            'form.save_error' => 'Regjistrimi nuk u ruajt. Kontrollo fushat e shënuara.',
        ];

        $existingMaps = [];
        $service = app(\App\Services\TranslationService::class);

        foreach ($locales as $lang) {
            $base = ($lang === 'sq') ? $sq : $en;
            if ($lang !== 'en' && $lang !== 'sq') {
                $base = $service->translate($en, $lang, 'en');
            }

            foreach ($fields as $f) {
                $key = 'field.' . $f['name'];
                $label = $fieldLabel($f['name']);
                $base[$key] = ($lang === 'en') ? $label : $service->translate($label, $lang, 'en');
            }
            $existingMaps[$lang] = $base;
        }

        foreach ($existingMaps as $lang => $defaults) {
            $path = "{$l10nDir}/{$snake}_{$lang}.dart";
            $existing = [];
            if (File::exists($path)) {
                $raw = File::get($path);
                preg_match_all("/'((?:\\\\'|[^'])*)'\\s*=>\\s*'((?:\\\\'|[^'])*)'/", $raw, $matches, PREG_SET_ORDER);
                foreach ($matches as $match) {
                    $key = str_replace(["\\\\", "\\'"], ["\\", "'"], $match[1]);
                    $value = str_replace(["\\\\", "\\'"], ["\\", "'"], $match[2]);
                    $existing[$key] = $value;
                }
            }

            $merged = array_merge($defaults, $existing);
            $entries = [];
            foreach ($merged as $key => $value) {
                $escapedKey = str_replace(['\\', "'"], ['\\\\', "\\'"], (string) $key);
                $escapedValue = str_replace(['\\', "'"], ['\\\\', "\\'"], (string) $value);
                $entries[] = "  '{$escapedKey}': '{$escapedValue}',";
            }

            File::put($path, "const Map<String, String> {$snake}" . ucfirst($lang) . " = {\n" . implode("\n", $entries) . "\n};\n");
        }

        $localizerPath = "{$l10nDir}/{$snake}_localization.dart";
        $imports = "";
        $cases = "";
        foreach ($locales as $lang) {
            $uLang = ucfirst($lang);
            $imports .= "import '{$snake}_{$lang}.dart';\n";
            $cases .= "    if (language == '$lang') return {$snake}{$uLang};\n";
        }

        File::put($localizerPath, <<<DART
import 'package:flutter/widgets.dart';
$imports
String {$snake}Tr(
  BuildContext context,
  String key, [
  Map<String, String> args = const {},
]) {
  final language = Localizations.localeOf(context).languageCode.toLowerCase();

  Map<String, String> getSource() {
$cases    return {$snake}En;
  }

  final source = getSource();
  var value = source[key] ?? {$snake}En[key] ?? key;
  args.forEach((name, replacement) {
    value = value.replaceAll('{\$name}', replacement);
  });

  return value;
}
DART
);
    }

    protected function generateMigration($tableName, $fields)
    {
        $schema = collect($fields)->map(function ($f) {
            if ($f['type'] === 'enum') {
                // Persist enum as a real database ENUM, never as string/varchar.
                $enumOptions = $f['options'];
                if ($f['name'] === 'priority') {
                    $enumOptions = ['Low', 'Medium', 'High'];
                }
                $opts = "['" . implode("', '", array_map('addslashes', $enumOptions)) . "']";
                $line = "\$table->enum('{$f['name']}', $opts)";
            } elseif ($f['type'] === 'foreignId') {
                $method = ($f['isUuid'] ?? false) ? 'foreignUuid' : 'foreignId';
                $line = "\$table->{$method}('{$f['name']}')->constrained('{$f['constrained']}')";
            } else {
                if ($f['type'] === 'decimal') {
                    $line = "\$table->decimal('{$f['name']}', 12, 2)";
                } elseif ($f['type'] === 'image') {
                    $line = "\$table->string('{$f['name']}')";
                } else {
                    $line = "\$table->{$f['type']}('{$f['name']}')";
                }
            }
            if ($f['nullable']) {
                $line .= '->nullable()';
            }
            return "            $line;";
        })->implode("\n");
        File::put(
            'database/migrations/' . date('Y_m_d_His') . "_create_{$tableName}_table.php",
            "<?php\nuse Illuminate\Database\Migrations\Migration;\nuse Illuminate\Database\Schema\Blueprint;\nuse Illuminate\Support\Facades\Schema;\nreturn new class extends Migration { public function up() { Schema::create('$tableName', function (Blueprint \$table) { \$table->id();\n$schema\n            \$table->timestamps(); }); } public function down() { Schema::dropIfExists('$tableName'); } };"
        );
    }

    protected function generateRouteFile($pluralName, $pluralKebab, $name, $pluralSnake)
    {
        $camel = Str::camel($name);

        $routeDir = base_path('routes/admin');

        if (!File::isDirectory($routeDir)) {
            File::makeDirectory($routeDir, 0755, true);
        }

        File::put(
            base_path("routes/admin/$pluralKebab.php"),
            "<?php\nuse Illuminate\Support\Facades\Route;\nuse App\Livewire\Admin\\$pluralName\\$pluralName;\nuse App\Livewire\Admin\\$pluralName\\Create;\nuse App\Livewire\Admin\\$pluralName\\Edit;\nRoute::prefix('$pluralKebab')->group(function () {\n    Route::get('/', $pluralName::class)->name('admin.$pluralKebab.index');\n    Route::get('create', Create::class)->name('admin.$pluralKebab.create');\n    Route::get('/{' . '$camel' . '}/edit', Edit::class)->name('admin.$pluralKebab.edit');\n});"
        );
    }

    protected function addPermissions($name, $pluralSnake)
    {
        foreach (['view', 'add', 'edit', 'delete'] as $act) {
            Permission::firstOrCreate(['name' => "{$act}_{$pluralSnake}"], ['label' => ucfirst($act) . ' ' . $name, 'module' => Str::plural($name)]);
        }
    }

    protected function addNavigation($pluralName, $pluralKebab, $pluralSnake, $icon)
    {
        $navPath = resource_path('views/components/layouts/app/navigation.blade.php');
        if (!File::exists($navPath)) {
            $this->warn("Navigation file not found at $navPath — skipped.");
            return;
        }
        $content = File::get($navPath);
        if (str_contains($content, "admin.{$pluralKebab}.index")) {
            $this->line("Nav link for $pluralKebab already exists — skipped.");
            return;
        }
        $newLink = "\n@can('view_{$pluralSnake}')\n    <x-nav.link route=\"admin.{$pluralKebab}.index\" icon=\"$icon\">{{ __('$pluralKebab.$pluralName') }}</x-nav.link>\n@endcan\n";
        $search = "<x-nav.divider>{{ __('admin.Account') }}</x-nav.divider>";
        if (str_contains($content, $search)) {
            File::put($navPath, str_replace($search, $newLink . $search, $content));
        } else {
            File::append($navPath, $newLink);
            $this->warn('Divider marker not found in navigation.blade.php — appended link at the end, please review placement.');
        }
    }

    protected function generateApiLayer($name, $pluralName, $pluralKebab, $fields)
    {
        $controllerDir = app_path('Http/Controllers/Api/Mobile');
        $resourceDir = app_path('Http/Resources/Mobile');
        File::makeDirectory($controllerDir, 0755, true, true);
        File::makeDirectory($resourceDir, 0755, true, true);

        $permPrefix = Str::snake($pluralName);
        $model = "\\App\\Models\\{$name}";
        $jsonFields = collect($fields)
            ->filter(fn ($f) => in_array($f['type'], ['array', 'json', 'object', 'collection']))
            ->pluck('name')
            ->values()
            ->all();

        $searchable = collect($fields)
            ->filter(fn ($f) => in_array($f['type'], ['string', 'text']))
            ->pluck('name')
            ->values()
            ->all();

        $relations = collect($fields)
            ->filter(fn ($f) => $f['type'] === 'foreignId')
            ->mapWithKeys(fn ($f) => [
                Str::camel(str_replace('_id', '', $f['name'])) => [
                    'field' => $f['name'],
                    'endpoint' => Str::plural(Str::kebab($f['relatedModel'])),
                ]
            ])->all();

        $with = array_keys($relations);
        $withPhp = var_export($with, true);
        $searchPhp = var_export($searchable, true);
        $jsonPhp = var_export($jsonFields, true);
        $fileFields = collect($fields)
            ->filter(fn ($f) => $f['type'] === 'image' || Str::contains(strtolower($f['name']), ['file', 'document', 'image', 'photo']))
            ->pluck('name')
            ->values()
            ->all();
        $filePhp = var_export($fileFields, true);
        $fieldNames = collect($fields)->pluck('name')->values()->all();
        $fieldNamesPhp = var_export($fieldNames, true);

        $resourceFields = "            'id' => \$this->id,\n";
        foreach ($fields as $f) {
            if ($f['type'] === 'image') {
                $resourceFields .= "            '{$f['name']}' => \$this->{$f['name']} ? asset(ltrim((string) \$this->{$f['name']}, '/')) : null,\n";
            } else {
                $resourceFields .= "            '{$f['name']}' => \$this->{$f['name']},\n";
            }
        }
        foreach ($relations as $method => $meta) {
            $resourceFields .= "            '{$method}' => \$this->whenLoaded('{$method}'),\n";
        }

        $resourceStub = <<<PHP
<?php

namespace App\Http\Resources\Mobile;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class {$name}Resource extends JsonResource
{
    public function toArray(Request \$request): array
    {
        return [
{$resourceFields}        ];
    }
}
PHP;
        File::put("$resourceDir/{$name}Resource.php", $resourceStub);

        $dtoImports = '';
        $storeBody = '';
        $updateBody = '';

        $domainBase = "App\\Domain\\{$name}";
        $dtoClass = "{$domainBase}\\DTOs\\{$name}DTO";
        $createAction = "{$domainBase}\\Actions\\Create{$name}Action";
        $updateAction = "{$domainBase}\\Actions\\Update{$name}Action";

        if (class_exists($dtoClass) && class_exists($createAction)) {
            $dtoImports .= "use {$dtoClass};\nuse {$createAction};\n";
            $storeBody = <<<PHP
        \$data = \$this->prepareData(\$request);
        \$validated = validator(\$data, {$name}::rules())->validate();
        \$item = app(\\{$createAction}::class)->execute(\\{$dtoClass}::fromArray(\$validated));
        return (new {$name}Resource(\$item->loadMissing({$withPhp})))->response()->setStatusCode(201);
PHP;
        } else {
            $storeBody = <<<PHP
        \$data = \$this->prepareData(\$request);
        \$validated = validator(\$data, {$name}::rules())->validate();
        \$item = {$name}::create(\$validated);
        return (new {$name}Resource(\$item->loadMissing({$withPhp})))->response()->setStatusCode(201);
PHP;
        }

        if (class_exists($dtoClass) && class_exists($updateAction)) {
            if (!str_contains($dtoImports, "use {$dtoClass};")) {
                $dtoImports .= "use {$dtoClass};\n";
            }
            $dtoImports .= "use {$updateAction};\n";
            $updateBody = <<<PHP
        \$item = {$name}::findOrFail(\$id);
        \$data = \$this->prepareData(\$request);
        \$validated = validator(\$data, {$name}::rules(\$id))->validate();
        \$item = app(\\{$updateAction}::class)->execute(\$item, \\{$dtoClass}::fromArray(\$validated));
        return new {$name}Resource(\$item->loadMissing({$withPhp}));
PHP;
        }
else {
            $updateBody = <<<PHP
        \$item = {$name}::findOrFail(\$id);
        \$data = \$this->prepareData(\$request);
        \$validated = validator(\$data, {$name}::rules(\$id))->validate();
        \$item->update(\$validated);
        return new {$name}Resource(\$item->fresh()->loadMissing({$withPhp}));
PHP;
        }

        $controllerStub = <<<PHP
<?php

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Controller;
use App\Http\Resources\Mobile\\{$name}Resource;
use {$model};
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
{$dtoImports}
class {$name}Controller extends Controller
{
    public function index(Request \$request)
    {
        abort_if_cannot('view_{$permPrefix}');

        \$perPage = min(max((int) \$request->integer('per_page', 20), 1), 100);
        \$sortField = (string) \$request->input('sort', 'id');
        \$direction = strtolower((string) \$request->input('direction', 'desc'));
        \$allowedSorts = {$name}::sortable();
        \$sortField = in_array(\$sortField, \$allowedSorts, true) ? \$sortField : 'id';
        \$direction = in_array(\$direction, ['asc', 'desc'], true) ? \$direction : 'desc';

        \$query = {$name}::query()->with($withPhp);

        \$search = trim((string) \$request->input('search', ''));
        if (\$search !== '') {
            \$query->where(function (\$q) use (\$search) {
                \$q->where('id', 'like', "%{\$search}%");
                foreach ({$searchPhp} as \$field) {
                    \$q->orWhere(\$field, 'like', "%{\$search}%");
                }
            });
        }

        foreach (\$request->all() as \$key => \$value) {
            if (\$value === null || \$value === '' || !str_ends_with(\$key, '_id')) {
                continue;
            }
            if (in_array(\$key, {$jsonPhp}, true)) {
                continue;
            }
            if (in_array(\$key, array_keys({$name}::rules()), true)) {
                \$query->where(\$key, \$value);
            }
        }

        \$items = \$query->orderBy(\$sortField, \$direction)->paginate(\$perPage)->withQueryString();
        return {$name}Resource::collection(\$items);
    }

    public function show(\$id)
    {
        abort_if_cannot('view_{$permPrefix}');
        \$item = {$name}::with($withPhp)->findOrFail(\$id);
        return new {$name}Resource(\$item);
    }

    public function store(Request \$request)
    {
        abort_if_cannot('add_{$permPrefix}');
        {$storeBody}
    }

    public function update(Request \$request, \$id)
    {
        abort_if_cannot('edit_{$permPrefix}');
        {$updateBody}
    }

    public function destroy(\$id): JsonResponse
    {
        abort_if_cannot('delete_{$permPrefix}');

        try {
            \$item = {$name}::findOrFail(\$id);
            \$item->delete();
            return response()->json(['success' => true, 'message' => '{$name} deleted.']);
        } catch (\\Throwable \$e) {
            \Illuminate\Support\Facades\Log::error("Delete Error: " . \$e->getMessage());
            return response()->json([
                'success' => false,
                'message' => config('app.debug') ? \$e->getMessage() : 'Record is referenced by other data and cannot be deleted.',
            ], 409);
        }
    }

    private function prepareData(Request \$request): array
    {
        \$data = \$request->all();

        foreach ($jsonPhp as \$field) {
            if (isset(\$data[\$field]) && is_string(\$data[\$field])) {
                \$decoded = json_decode(\$data[\$field], true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    \$data[\$field] = \$decoded;
                }
            }
        }

        foreach ({$filePhp} as \$field) {
            if (\$request->hasFile(\$field)) {
                \$data[\$field] = app(\App\Services\ImageUploadService::class)->upload(\$request->file(\$field), 'uploads/{$pluralKebab}');
            }
        }

        return \$data;
    }
}
PHP;
        File::put("$controllerDir/{$name}Controller.php", $controllerStub);

        $apiRoutePath = base_path('routes/api.php');
        $content = File::exists($apiRoutePath) ? File::get($apiRoutePath) : "<?php\n\nuse Illuminate\\Support\\Facades\\Route;\n";
        $route = "Route::apiResource('{$pluralKebab}', \\App\\Http\\Controllers\\Api\\Mobile\\{$name}Controller::class);";

        // Foreign-key lookup: if the related mobile resource does not exist, create
        // a read-only endpoint so Flutter can resolve the relation (e.g. users).
        foreach ($fields as $relationField) {
            if ($relationField['type'] !== 'foreignId') continue;
            $relationPlural = Str::plural(Str::kebab($relationField['relatedModel']));
            $relatedModelClass = "\\App\\Models\\{$relationField['relatedModel']}";
            $displayField = $relationField['labelField'] ?? 'name';
            $hasRoute = (bool) preg_match('/Route::(?:apiResource|get)\s*\(\s*[\"\']' . preg_quote($relationPlural, '/') . '[\"\']/', $content);
            if (!$hasRoute) {
                $lookupRoute = "Route::get('{$relationPlural}', function (\\Illuminate\\Http\\Request \$request) {\n"
                    . "        \$search = trim((string) \$request->input('search', ''));\n"
                    . "        \$query = {$relatedModelClass}::query();\n"
                    . "        if (\$search !== '') { \$query->where('{$displayField}', 'like', '%' . \$search . '%'); }\n"
                    . "        \$items = \$query->limit(50)->get()->map(fn (\$item) => ['id' => \$item->getKey(), 'name' => \$item->{$displayField}])->values();\n"
                    . "        return response()->json(['data' => \$items]);\n"
                    . "    });";
                $marker = "Route::middleware('auth:sanctum')->prefix('mobile')->group(function () {";
                if (Str::contains($content, $marker)) {
                    $content = str_replace($marker, $marker . "\n    " . $lookupRoute, $content);
                } else {
                    $content .= "\nRoute::middleware('auth:sanctum')->prefix('mobile')->group(function () {\n    {$lookupRoute}\n});\n";
                }
            }
        }
        if (!Str::contains($content, "apiResource('{$pluralKebab}'")) {
            $marker = "Route::middleware('auth:sanctum')->prefix('mobile')->group(function () {";
            if (Str::contains($content, $marker)) {
                $content = str_replace($marker, $marker . "\n    " . $route, $content);
            } else {
                $content .= "\nRoute::middleware('auth:sanctum')->prefix('mobile')->group(function () {\n    {$route}\n});\n";
            }
        }
        File::put($apiRoutePath, $content);
    }

    protected function generateRealtimeLayer($name, $pluralName, $pluralKebab)
    {
        $observerDir = app_path('Observers');
        File::makeDirectory($observerDir, 0755, true, true);

        $eventDir = app_path('Events');
        File::makeDirectory($eventDir, 0755, true, true);

        File::put("$eventDir/{$name}Changed.php", <<<PHP
<?php

namespace App\Events;

use App\Models\{$name};
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class {$name}Changed implements ShouldBroadcast, ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public {$name} \$item,
        public string \$action,
    ) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('mobile.{$pluralKebab}')];
    }

    public function broadcastAs(): string
    {
        return '{$pluralKebab}.changed';
    }

    public function broadcastWith(): array
    {
        return [
            'action' => \$this->action,
            'data' => \$this->action === 'deleted'
                ? ['id' => \$this->item->getKey()]
                : \$this->item->fresh()->toArray(),
        ];
    }
}
PHP);
$eventClass = $name . 'Changed';
$observerClass = $name . 'Observer';

        $observerContent = "<?php\n\n" .
            "namespace App\\Observers;\n\n" .
            "use App\\Events\\{$eventClass};\n" .
            "use App\\Models\\{$name};\n" .
            "use App\\Services\\NotificationRouter;\n\n" .
            "class {$observerClass}\n" .
            "{\n" .
            "    public function created({$name} \$item): void\n" .
            "    {\n" .
            "        try {\n" .
            "            event(new {$eventClass}(\$item, 'created'));\n" .
            "            app(NotificationRouter::class)->maybeNotify('{$pluralKebab}.created', \$item, 'created');\n" .
            "        } catch (\\Throwable \$e) {\n" .
            "            \\Illuminate\\Support\\Facades\\Log::warning(\"Observer (created) failure for {$name}: \" . \$e->getMessage());\n" .
            "        }\n" .
            "    }\n\n" .
            "    public function updated({$name} \$item): void\n" .
            "    {\n" .
            "        try {\n" .
            "            event(new {$eventClass}(\$item, 'updated'));\n" .
            "            app(NotificationRouter::class)->maybeNotify('{$pluralKebab}.updated', \$item, 'updated');\n" .
            "        } catch (\\Throwable \$e) {\n" .
            "            \\Illuminate\\Support\\Facades\\Log::warning(\"Observer (updated) failure for {$name}: \" . \$e->getMessage());\n" .
            "        }\n" .
            "    }\n\n" .
            "    public function deleted({$name} \$item): void\n" .
            "    {\n" .
            "        try {\n" .
            "            event(new {$eventClass}(\$item, 'deleted'));\n" .
            "            app(NotificationRouter::class)->maybeNotify('{$pluralKebab}.deleted', \$item, 'deleted');\n" .
            "        } catch (\\Throwable \$e) {\n" .
            "            \\Illuminate\\Support\\Facades\\Log::warning(\"Observer (deleted) failure for {$name}: \" . \$e->getMessage());\n" .
            "        }\n" .
            "    }\n" .
            "}\n";

        File::put("$observerDir/{$observerClass}.php", $observerContent);

        // Register event in DB for Firebase management UI
        if (class_exists(RealtimeEvent::class) && Schema::hasTable('realtime_events')) {
            RealtimeEvent::updateOrCreate(
                ['event' => "{$pluralKebab}.created"],
                [
                    'label' => "New {$name}",
                    'description' => "Push notification when a new record is created in {$pluralName}.",
                    'firebase_enabled' => $this->option('firebase'),
                ]
            );
        }

        $channelsPath = base_path('routes/channels.php');
        if (!File::exists($channelsPath)) {
            File::put($channelsPath, "<?php\n\nuse Illuminate\\Support\\Facades\\Broadcast;\n\n");
        }
        $channels = File::get($channelsPath);
        $channelLine = "Broadcast::channel('mobile.{$pluralKebab}', function (\\App\\Models\\User \$user) { return \$user->can('view_" . Str::snake($pluralName) . "'); });";
        if (!Str::contains($channels, "Broadcast::channel('mobile.{$pluralKebab}'")) {
            $channels .= "\n{$channelLine}\n";
            File::put($channelsPath, $channels);
        }

        $this->info("🔴 Realtime layer generated for {$name}: observer + broadcast event + private channel");
    }

    protected function generateFlutterBLoCLayer($name, $pluralName, $pluralKebab, $fields, $icon = 'chevron-right')
    {
        $base = base_path('mobile-gateway/lib');
        $snake = Str::snake($name);
        $moduleDir = "$base/modules/dashboard/$snake";
        $coreDir = "$base/core";
        $flutterL10nDir = "$base/l10n";
        $modelDir = "$moduleDir/data";
        $presentationDir = "$moduleDir/presentation";
        $cubitDir = "$presentationDir/cubit";
        $pagesDir = "$presentationDir/pages";
        $widgetsDir = "$presentationDir/widgets";

        foreach ([$moduleDir, $coreDir, $flutterL10nDir, $modelDir, $presentationDir, $cubitDir, $pagesDir, $widgetsDir] as $dir) {
            File::makeDirectory($dir, 0755, true, true);
        }

        // Flutter translations are generated per module, exactly like Laravel's
        // lang/{en,sq}/{module}.php files. Existing keys are preserved.
        $this->generateFlutterTranslationFiles($flutterL10nDir, $snake, $name, $pluralName, $fields);

        $dartType = function ($f) {
            if ($f['type'] === 'foreignId' && ($f['isUuid'] ?? false)) return 'String';
            return match ($f['type']) {
                'integer', 'bigInteger', 'foreignId' => 'int',
                'decimal' => 'double',
                'boolean' => 'bool',
                'json', 'array', 'object', 'collection' => 'dynamic',
                default => 'String',
            };
        };

        $fieldsDecl = ""; $ctor = ""; $fromJson = ""; $toJson = "";
        foreach ($fields as $f) {
            $type = $dartType($f);
            $camel = Str::camel($f['name']);
            $fieldsDecl .= "  final {$type}? {$camel};\n";
            $ctor .= "    this.{$camel},\n";
            if ($f['type'] === 'foreignId' && ($f['isUuid'] ?? false)) {
                $fromJson .= "      {$camel}: json['{$f['name']}']?.toString(),\n";
            } elseif (in_array($f['type'], ['integer', 'bigInteger', 'foreignId'], true)) {
                $fromJson .= "      {$camel}: json['{$f['name']}'] == null ? null : int.tryParse(json['{$f['name']}'].toString()),\n";
            } elseif ($f['type'] === 'decimal') {
                $fromJson .= "      {$camel}: json['{$f['name']}'] == null ? null : double.tryParse(json['{$f['name']}'].toString()),\n";
            } elseif ($f['type'] === 'boolean') {
                $fromJson .= "      {$camel}: json['{$f['name']}'] == true || json['{$f['name']}'] == 1 || json['{$f['name']}'] == '1',\n";
            } elseif (in_array($f['type'], ['json', 'array', 'object', 'collection'], true)) {
                $fromJson .= "      {$camel}: json['{$f['name']}'],\n";
            } else {
                $fromJson .= "      {$camel}: json['{$f['name']}']?.toString(),\n";
            }
            $toJson .= "      '{$f['name']}': {$camel},\n";
        }

        File::put("$modelDir/{$snake}_model.dart", <<<DART
class {$name}Model {
  final int? id;
$fieldsDecl
  const {$name}Model({
    this.id,
$ctor  });

  factory {$name}Model.fromJson(Map<String, dynamic> json) => {$name}Model(
      id: json['id'] == null ? null : int.tryParse(json['id'].toString()),
$fromJson  );

  Map<String, dynamic> toJson() => {
      'id': id,
$toJson  };
}
DART);

        $relationMethods = collect($fields)->filter(fn($f) => $f['type'] === 'foreignId')->map(function($f) {
            $method = Str::camel(str_replace('_id', '', $f['name']));
            return "  Future<List<Map<String, dynamic>>> {$method}Options({String search = ''}) async {\n    return repository.lookup('/" . Str::plural(Str::kebab($f['relatedModel'])) . "', search: search);\n  }\n";
        })->implode("\n");

        $namedRecordType = "({$name}Page page, Map<String, dynamic> meta)";
        $namedRecordReturn = "(page: {$name}Page(data), meta: meta)";

        // Ensure named records have curly braces in the output Dart code
        $dartRecordType = "({$name}Page page, Map<String, dynamic> meta)";
        $dartRecordTypeWithBraces = "({$name}Page page, Map<String, dynamic> meta)";
        // Actually, let's just use concatenation to be 100% sure PHP doesn't eat them.
        $finalDartType = '({' . $name . 'Page page, Map<String, dynamic> meta})';
        $finalDartReturn = '(page: ' . $name . 'Page(data), meta: meta)';

        File::put("$modelDir/{$snake}_repository.dart", <<<DART
import 'dart:convert';
import '../../../../services/api_service.dart';

class {$name}Repository {
  static const _perPage = 25;

  Future<$finalDartType> index({
    int page = 1,
    String search = '',
    String? sort,
    String direction = 'desc',
    Map<String, dynamic> filters = const {},
  }) async {
    final query = <String, String>{
      'page': page.toString(),
      'per_page': _perPage.toString(),
      if (search.trim().isNotEmpty) 'search': search.trim(),
      if (sort != null && sort.isNotEmpty) 'sort': sort,
      'direction': direction,
    };
    filters.forEach((key, value) {
      if (value != null && value.toString().isNotEmpty) query[key] = value.toString();
    });

    final res = await ApiService.get('/{$pluralKebab}?' + Uri(queryParameters: query).query);
    _ensureSuccess(res);
    final decoded = Map<String, dynamic>.from(jsonDecode(res.body));
    final data = (decoded['data'] as List? ?? [])
        .map((e) => Map<String, dynamic>.from(e as Map))
        .toList();
    final meta = Map<String, dynamic>.from(decoded['meta'] ?? {});
    return $finalDartReturn;
  }

  Future<Map<String, dynamic>> show(dynamic id) async {
    final res = await ApiService.get('/{$pluralKebab}/\$id');
    _ensureSuccess(res);
    return Map<String, dynamic>.from(jsonDecode(res.body)['data'] ?? {});
  }

  Future<Map<String, dynamic>> save(Map<String, dynamic> payload, {dynamic id, Map<String, String>? files}) async {
    final multipartPayload = Map<String, dynamic>.from(payload);
    if (id != null) multipartPayload['_method'] = 'PUT';
    final res = (files != null && files.isNotEmpty)
        ? await ApiService.postMultipart(
            id == null ? '/{$pluralKebab}' : '/{$pluralKebab}/\$id',
            multipartPayload,
            files: files,
          )
        : id == null
            ? await ApiService.post('/{$pluralKebab}', payload)
            : await ApiService.put('/{$pluralKebab}/\$id', payload);
    _ensureSuccess(res);
    return Map<String, dynamic>.from(jsonDecode(res.body)['data'] ?? {});
  }

  Future<void> delete(dynamic id) async {
    final res = await ApiService.delete('/{$pluralKebab}/\$id');
    _ensureSuccess(res);
  }

  Future<List<Map<String, dynamic>>> lookup(String endpoint, {String search = ''}) async {
    final query = search.trim().isEmpty ? '' : '?search=\${Uri.encodeQueryComponent(search.trim())}&per_page=50';
    final res = await ApiService.get('/\$endpoint\$query');
    _ensureSuccess(res);
    final decoded = jsonDecode(res.body);
    final data = decoded is Map ? decoded['data'] : decoded;
    return (data as List? ?? []).map((e) => Map<String, dynamic>.from(e as Map)).toList();
  }

  void _ensureSuccess(dynamic res) {
    if (res.statusCode < 200 || res.statusCode >= 300) {
      throw Exception(ApiService.extractErrorMessage(res));
    }
  }
}

class {$name}Page {
  final List<Map<String, dynamic>> items;
  const {$name}Page(this.items);
}
DART);

        File::put("$cubitDir/{$snake}_state.dart", <<<DART
sealed class {$name}State {
  const {$name}State();
}

final class {$name}Initial extends {$name}State {
  const {$name}Initial();
}

final class {$name}Loading extends {$name}State {
  final List<Map<String, dynamic>> items;
  const {$name}Loading([this.items = const []]);
}

final class {$name}Loaded extends {$name}State {
  final List<Map<String, dynamic>> items;
  final bool hasMore;
  final bool refreshing;
  const {$name}Loaded(this.items, {this.hasMore = false, this.refreshing = false});
}

final class {$name}Saving extends {$name}State {
  const {$name}Saving();
}

final class {$name}Saved extends {$name}State {
  final Map<String, dynamic> item;
  const {$name}Saved(this.item);
}

final class {$name}Deleting extends {$name}State {
  final int id;
  const {$name}Deleting(this.id);
}

final class {$name}Failure extends {$name}State {
  final String message;
  final List<Map<String, dynamic>> items;
  const {$name}Failure(this.message, [this.items = const []]);
}
DART);

        File::put("$cubitDir/{$snake}_cubit.dart", <<<DART
import 'dart:async';
import 'package:flutter_bloc/flutter_bloc.dart';
import '../../data/{$snake}_repository.dart';
import ' {$snake}_state.dart';

class {$name}Cubit extends Cubit<{$name}State> {
  final {$name}Repository repository;
  final List<Map<String, dynamic>> _items = [];
  Timer? _searchDebounce;
  int _page = 1;
  bool _hasMore = true;
  String _search = '';
  String? _sort;
  String _direction = 'desc';
  Map<String, dynamic> _filters = {};

  {$name}Cubit(this.repository) : super(const {$name}Initial());

  List<Map<String, dynamic>> get items => List.unmodifiable(_items);

  Future<void> load({bool refresh = false, String? search, Map<String, dynamic>? filters}) async {
    if (search != null) _search = search;
    if (filters != null) _filters = Map<String, dynamic>.from(filters);
    if (refresh) { _page = 1; _hasMore = true; _items.clear(); }
    if (_page == 1) emit({$name}Loading(List.unmodifiable(_items)));

    try {
      final result = await repository.index(
        page: _page,
        search: _search,
        sort: _sort,
        direction: _direction,
        filters: _filters,
      );
      if (_page == 1) _items.clear();
      _items.addAll(result.page.items);
      final current = int.tryParse(result.meta['current_page']?.toString() ?? '') ?? _page;
      final last = int.tryParse(result.meta['last_page']?.toString() ?? '') ?? current;
      _hasMore = current < last || result.meta['next_page_url'] != null;
      emit({$name}Loaded(List.unmodifiable(_items), hasMore: _hasMore));
    } catch (e) {
      emit({$name}Failure(_cleanError(e), List.unmodifiable(_items)));
    }
  }

  void search(String value) {
    _searchDebounce?.cancel();
    _searchDebounce = Timer(const Duration(milliseconds: 350), () {
      load(refresh: true, search: value);
    });
  }

  Future<void> loadMore() async {
    if (!_hasMore || state is {$name}Loading) return;
    _page++;
    await load();
  }

  Future<void> refresh() => load(refresh: true);

  void setSort(String field) {
    if (_sort == field) {
      _direction = _direction == 'asc' ? 'desc' : 'asc';
    } else {
      _sort = field;
      _direction = 'asc';
    }
    load(refresh: true);
  }

  Future<void> save(Map<String, dynamic> payload, {dynamic id, Map<String, String>? files}) async {
    emit(const {$name}Saving());
    try {
      final item = await repository.save(payload, id: id, files: files);
      emit({$name}Saved(item));
    } catch (e) {
      emit({$name}Failure(_cleanError(e), List.unmodifiable(_items)));
    }
  }

  Future<void> delete(dynamic id) async {
    emit({$name}Deleting(id));
    try {
      await repository.delete(id);
      _items.removeWhere((item) => item['id'].toString() == id.toString());
      emit({$name}Loaded(List.unmodifiable(_items), hasMore: _hasMore));
    } catch (e) {
      emit({$name}Failure(_cleanError(e), List.unmodifiable(_items)));
    }
  }

  String _cleanError(Object error) {
    final text = error.toString();
    return text.startsWith('Exception: ') ? text.substring(11) : text;
  }

  void handleRealtime(String action, Map<String, dynamic> data) {
    final id = data['id']?.toString();
    if (action == 'deleted') {
      if (id != null) _items.removeWhere((item) => item['id']?.toString() == id);
      emit({$name}Loaded(List.unmodifiable(_items), hasMore: _hasMore));
      return;
    }
    if (id == null) return;
    final index = _items.indexWhere((item) => item['id']?.toString() == id);
    if (action == 'created' && index == -1) {
      _items.insert(0, data);
    } else if (action == 'updated' && index != -1) {
      _items[index] = data;
    } else if (action == 'updated' && index == -1) {
      _items.insert(0, data);
    }
    emit({$name}Loaded(List.unmodifiable(_items), hasMore: _hasMore));
  }

  @override
  Future<void> close() {
    _searchDebounce?.cancel();
    return super.close();
  }
}
DART);

        // Fix the intentional leading space in the generated state import.
        $cubit = File::get("$cubitDir/{$snake}_cubit.dart");
        $cubit = str_replace("import ' {$snake}_state.dart';", "import '{$snake}_state.dart';", $cubit);
        File::put("$cubitDir/{$snake}_cubit.dart", $cubit);

        $titleExpression = "(item['name'] ?? item['title'] ?? 'ID: \${item['id']}').toString()";
        $fileFields = collect($fields)->filter(fn($f) => $f['type'] === 'image' || Str::contains(strtolower($f['name']), ['file','document','image','photo']))->pluck('name')->values()->all();
        $hasFile = !empty($fileFields);
        $firstFile = $fileFields[0] ?? null;

        $rowFields = collect($fields)->take(3)->map(function($f) use ($snake) {
            $fieldKey = addslashes("field.{$f['name']}");
            $value = match ($f['type']) {
                'boolean' => "item['{$f['name']}'] == true || item['{$f['name']}'] == 1 ? {$snake}Tr(context, 'common.yes') : {$snake}Tr(context, 'common.no')",
                'foreignId' => "item['{$f['name']}']?.toString() ?? '-'",
                default => "item['{$f['name']}']?.toString() ?? '-'",
            };
            return "_InfoChip(label: {$snake}Tr(context, '$fieldKey'), value: $value),";
        })->implode("\n");

        $filtersUi = collect($fields)->filter(fn($f) => in_array($f['type'], ['foreignId','boolean','enum'], true))->map(function($f) use ($snake) {
            $label = Str::headline($f['name']);
            $fieldKey = addslashes("field.{$f['name']}");
            $camel = Str::camel($f['name']);
            if ($f['type'] === 'enum') {
                $opts = '[' . implode(', ', array_map(fn($o) => "'" . addslashes($o) . "'", $f['options'])) . ']';
                return "_FilterDropdown(label: {$snake}Tr(context, '$fieldKey'), value: _filters['{$f['name']}']?.toString(), options: $opts, onChanged: (v) => setState(() => _filters['{$f['name']}'] = v)),";
            }
            if ($f['type'] === 'boolean') {
                return "SwitchListTile.adaptive(contentPadding: EdgeInsets.zero, title: Text({$snake}Tr(context, '$fieldKey')), value: _filters['{$f['name']}'] == true, onChanged: (v) => setState(() => _filters['{$f['name']}'] = v)),";
            }
            return "_FilterText(label: {$snake}Tr(context, '$fieldKey'), value: _filters['{$f['name']}']?.toString() ?? '', onChanged: (v) => _filters['{$f['name']}'] = v),";
        })->implode("\n");

        $filterFields = collect($fields)->filter(fn($f) => in_array($f['type'], ['foreignId','boolean','enum'], true))->count();

        File::put("$pagesDir/{$snake}_list_page.dart", <<<DART
import 'package:flutter/material.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import '../../../../../core/widgets/premium_widgets.dart';
import '../../../../../core/realtime/realtime_service.dart';
import '../../../../../l10n/{$snake}_localization.dart';
import '../cubit/{$snake}_cubit.dart';
import '../cubit/{$snake}_state.dart';
import '../widgets/{$snake}_card.dart';
import '{$snake}_form_page.dart';

class {$name}ListPage extends StatelessWidget {
  const {$name}ListPage({super.key});

  @override
  Widget build(BuildContext context) {
    return BlocProvider(
      create: (_) => {$name}Cubit({$name}Repository())..load(),
      child: const _{$name}ListView(),
    );
  }
}

class _{$name}ListView extends StatefulWidget {
  const _{$name}ListView();
  @override State<_{$name}ListView> createState() => _{$name}ListViewState();
}

class _{$name}ListViewState extends State<_{$name}ListView> {
  final _search = TextEditingController();
  final _scroll = ScrollController();
  final _filters = <String, dynamic>{};

  @override
  void initState() {
    super.initState();
    _scroll.addListener(() {
      if (_scroll.position.pixels >= _scroll.position.maxScrollExtent - 320) {
        context.read<{$name}Cubit>().loadMore();
      }
    });
    RealtimeService.instance.subscribe('{$pluralKebab}', (action, data) {
      if (!mounted) return;
      context.read<{$name}Cubit>().handleRealtime(action, data);
    });
  }

  @override
  void dispose() {
    _search.dispose();
    _scroll.dispose();
    super.dispose();
  }

  Future<void> _openFilters() async {
    await showModalBottomSheet<void>(
      context: context,
      isScrollControlled: true,
      backgroundColor: Theme.of(context).colorScheme.surface,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(30))),
      builder: (_) => Padding(
        padding: EdgeInsets.fromLTRB(22, 14, 22, MediaQuery.of(context).viewInsets.bottom + 24),
        child: Column(mainAxisSize: MainAxisSize.min, children: [
          Container(width: 42, height: 4, decoration: BoxDecoration(color: Theme.of(context).dividerColor, borderRadius: BorderRadius.circular(10))),
          const SizedBox(height: 20),
          Align(alignment: Alignment.centerLeft, child: Text({$snake}Tr(context, 'list.filters'), style: const TextStyle(fontSize: 22, fontWeight: FontWeight.w800))),
          const SizedBox(height: 18),
          $filtersUi
          if ($filterFields > 0) const SizedBox(height: 8),
          Row(children: [
            Expanded(child: OutlinedButton(onPressed: () { _filters.clear(); setState(() {}); Navigator.pop(context); context.read<{$name}Cubit>().load(refresh: true, filters: {}); }, child: Text({$snake}Tr(context, 'list.clear')))),
            const SizedBox(width: 12),
            Expanded(child: FilledButton(onPressed: () { Navigator.pop(context); context.read<{$name}Cubit>().load(refresh: true, filters: _filters); }, child: Text({$snake}Tr(context, 'list.apply')))),
          ]),
        ]),
      ),
    );
  }

  Future<void> _openForm([Map<String, dynamic>? item]) async {
    final changed = await Navigator.push<bool>(context, MaterialPageRoute(builder: (_) => {$name}FormPage(item: item)));
    if (changed == true && mounted) context.read<{$name}Cubit>().load(refresh: true);
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return Scaffold(
      backgroundColor: theme.colorScheme.surface,
      appBar: AppBar(
        titleSpacing: 20,
        title: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Text({$snake}Tr(context, 'list.title'), style: const TextStyle(fontSize: 22, fontWeight: FontWeight.w800)),
          Text({$snake}Tr(context, 'list.subtitle'), style: TextStyle(fontSize: 12, color: theme.colorScheme.onSurfaceVariant, fontWeight: FontWeight.w500)),
        ]),
        actions: [
          IconButton(tooltip: {$snake}Tr(context, 'list.filters'), onPressed: $filterFields > 0 ? _openFilters : null, icon: const Icon(Icons.tune_rounded)),
          IconButton(tooltip: {$snake}Tr(context, 'list.refresh'), onPressed: () => context.read<{$name}Cubit>().refresh(), icon: const Icon(Icons.refresh_rounded)),
          const SizedBox(width: 8),
        ],
      ),
      floatingActionButton: FloatingActionButton.extended(onPressed: () => _openForm(), icon: const Icon(Icons.add_rounded), label: Text({$snake}Tr(context, 'list.add'))),
      body: Column(children: [
        Padding(padding: const EdgeInsets.fromLTRB(20, 8, 20, 12), child: PremiumSearchBar(
          controller: _search,
          hintText: {$snake}Tr(context, 'list.search_hint'),
          onChanged: (v) => context.read<{$name}Cubit>().search(v),
        )),
        Expanded(child: BlocConsumer<{$name}Cubit, {$name}State>(
          listener: (context, state) {
            if (state is {$name}Failure) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(state.message), behavior: SnackBarBehavior.floating));
            if (state is {$name}Saved) Navigator.of(context).pop(true);
          },
          builder: (context, state) {
            final items = state is {$name}Loaded ? state.items : state is {$name}Loading ? state.items : state is {$name}Failure ? state.items : const <Map<String,dynamic>>[];
            if (state is {$name}Loading && items.isEmpty) return ListView.separated(padding: const EdgeInsets.all(20), itemCount: 7, separatorBuilder: (_, __) => const SizedBox(height: 12), itemBuilder: (_, __) => PremiumSkeleton());
            if (items.isEmpty) return PremiumEmptyState(title: _search.text.isEmpty ? {$snake}Tr(context, 'list.nothing') : {$snake}Tr(context, 'list.no_results'), message: _search.text.isEmpty ? {$snake}Tr(context, 'list.create_first') : {$snake}Tr(context, 'list.try_different'), icon: _search.text.isEmpty ? Icons.inbox_rounded : Icons.search_off_rounded, actionLabel: _search.text.isEmpty ? {$snake}Tr(context, 'list.create_record') : null, action: _search.text.isEmpty ? () => _openForm() : null);
            return RefreshIndicator(
              onRefresh: context.read<{$name}Cubit>().refresh,
              child: ListView.separated(
                controller: _scroll,
                physics: const AlwaysScrollableScrollPhysics(),
                padding: const EdgeInsets.fromLTRB(20, 4, 20, 120),
                itemCount: items.length + 1,
                separatorBuilder: (_, __) => const SizedBox(height: 10),
                itemBuilder: (context, index) {
                  if (index == items.length) return state is {$name}Loaded && state.hasMore ? const Padding(padding: EdgeInsets.all(22), child: Center(child: CircularProgressIndicator.adaptive())) : const SizedBox(height: 20);
                  return {$name}Card(item: items[index], onTap: () => _openForm(items[index]), onDelete: () => _confirmDelete(context, items[index]));
                },
              ),
            );
          },
        )),
      ]),
    );
  }

  Future<void> _confirmDelete(BuildContext context, Map<String, dynamic> item) async {
    final ok = await PremiumDialog.confirm(
      context,
      title: {$snake}Tr(context, 'list.delete_confirm_title'),
      message: {$snake}Tr(context, 'list.delete_confirm_message', {'id': item['id'].toString()}),
      confirmLabel: {$snake}Tr(context, 'list.delete'),
      destructive: true,
    );
    if (ok == true && context.mounted) context.read<{$name}Cubit>().delete(item['id']);
  }
}

class _PremiumLoadingList extends StatelessWidget {
  const _PremiumLoadingList();
  @override Widget build(BuildContext context) => ListView.separated(padding: const EdgeInsets.all(20), itemCount: 7, separatorBuilder: (_, __) => const SizedBox(height: 12), itemBuilder: (_, __) => Container(height: 92, decoration: BoxDecoration(color: Theme.of(context).colorScheme.surfaceContainerHighest, borderRadius: BorderRadius.circular(22))));
}

class _EmptyState extends StatelessWidget {
  final VoidCallback onAdd; final String query;
  const _EmptyState({required this.onAdd, required this.query});
  @override Widget build(BuildContext context) => Center(child: Padding(padding: const EdgeInsets.all(32), child: Column(mainAxisSize: MainAxisSize.min, children: [
    Container(width: 76, height: 76, decoration: BoxDecoration(color: Theme.of(context).colorScheme.primaryContainer, shape: BoxShape.circle), child: Icon(query.isEmpty ? Icons.inbox_rounded : Icons.search_off_rounded, size: 34)),
    const SizedBox(height: 18), Text(query.isEmpty ? {$snake}Tr(context, 'list.nothing') : {$snake}Tr(context, 'list.no_results'), style: const TextStyle(fontSize: 19, fontWeight: FontWeight.w800)),
    const SizedBox(height: 7), Text(query.isEmpty ? {$snake}Tr(context, 'list.create_first') : {$snake}Tr(context, 'list.try_different'), textAlign: TextAlign.center, style: TextStyle(color: Colors.grey)),
    if (query.isEmpty) ...[const SizedBox(height: 18), FilledButton.icon(onPressed: onAdd, icon: const Icon(Icons.add_rounded), label: Text({$snake}Tr(context, 'list.create_record')))],
  ])));
}

class _FilterText extends StatelessWidget {
  final String label, value; final ValueChanged<String> onChanged;
  const _FilterText({required this.label, required this.value, required this.onChanged});
  @override Widget build(BuildContext context) => Padding(padding: const EdgeInsets.only(bottom: 12), child: TextFormField(initialValue: value, onChanged: onChanged, decoration: InputDecoration(labelText: label, border: const OutlineInputBorder())));
}

class _FilterDropdown extends StatelessWidget {
  final String label; final String? value; final List<String> options; final ValueChanged<String?> onChanged;
  const _FilterDropdown({required this.label, required this.value, required this.options, required this.onChanged});
  @override Widget build(BuildContext context) => Padding(padding: const EdgeInsets.only(bottom: 12), child: DropdownButtonFormField<String>(value: value, items: options.map((e) => DropdownMenuItem(value: e, child: Text(e))).toList(), onChanged: onChanged, decoration: InputDecoration(labelText: label, border: const OutlineInputBorder())));
}
DART);

        $listPath = "$pagesDir/{$snake}_list_page.dart";
        $list = File::get($listPath);
        $list = str_replace("import '../widgets/{$snake}_card.dart';", "import '../widgets/{$snake}_card.dart';\nimport '../../data/{$snake}_repository.dart';", $list);
        File::put($listPath, $list);

        $cardImage = $hasFile
            ? "          if (item['$firstFile'] != null && item['$firstFile'].toString().isNotEmpty)\n            ClipRRect(borderRadius: BorderRadius.circular(17), child: Image.network('\${ApiService.serverUrl}/\${item['$firstFile']}', width: 58, height: 58, fit: BoxFit.cover, errorBuilder: (_, __, ___) => _Avatar(title: title)))\n          else\n            _Avatar(title: title),"
            : "          _Avatar(title: title),";

        File::put("$widgetsDir/{$snake}_card.dart", <<<DART
import 'package:flutter/material.dart';
import '../../../../../services/api_service.dart';
import '../../../../../core/widgets/premium_widgets.dart';
import '../../../../../l10n/{$snake}_localization.dart';

class {$name}Card extends StatelessWidget {
  final Map<String, dynamic> item;
  final VoidCallback onTap;
  final VoidCallback onDelete;
  const {$name}Card({super.key, required this.item, required this.onTap, required this.onDelete});

  String get title {
    final value = {$titleExpression};
    return value.toString().trim().isEmpty ? 'ID: ' + item['id'].toString() : value.toString();
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return PremiumCard(
      onTap: onTap,
      padding: const EdgeInsets.all(14),
      child: Row(children: [
$cardImage
        const SizedBox(width: 14),
        Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Text(title, maxLines: 1, overflow: TextOverflow.ellipsis, style: const TextStyle(fontSize: 15.5, fontWeight: FontWeight.w800)),
          const SizedBox(height: 7),
          Wrap(spacing: 6, runSpacing: 6, children: [$rowFields]),
        ])),
        PopupMenuButton<String>(onSelected: (value) { if (value == 'edit') onTap(); if (value == 'delete') onDelete(); }, itemBuilder: (_) => [PopupMenuItem(value: 'edit', child: Text({$snake}Tr(context, 'list.edit'))), PopupMenuItem(value: 'delete', child: Text({$snake}Tr(context, 'list.delete')))]),
      ]),
    );
  }
}

class _Avatar extends StatelessWidget {
  final String title;
  const _Avatar({required this.title});
  @override Widget build(BuildContext context) => Container(width: 58, height: 58, decoration: BoxDecoration(gradient: LinearGradient(colors: [Theme.of(context).colorScheme.primaryContainer, Theme.of(context).colorScheme.secondaryContainer]), borderRadius: BorderRadius.circular(17)), child: Center(child: Text(title.isEmpty ? '?' : title.substring(0,1).toUpperCase(), style: const TextStyle(fontSize: 20, fontWeight: FontWeight.w900))));
}

class _InfoChip extends StatelessWidget {
  final String label, value;
  const _InfoChip({required this.label, required this.value});
  @override Widget build(BuildContext context) => Container(padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4), decoration: BoxDecoration(color: Theme.of(context).colorScheme.surfaceContainerHighest, borderRadius: BorderRadius.circular(8)), child: Text('\$label: \$value', style: TextStyle(fontSize: 10.5, color: Theme.of(context).colorScheme.onSurfaceVariant, fontWeight: FontWeight.w600)));
}
DART);

        $controllers = ''; $dispose = ''; $init = ''; $widgets = ''; $payload = ''; $relationVars = ''; $relationLoaders = ''; $relationPickers = '';
        $fileState = ''; $filesMap = '';
        foreach ($fields as $f) {
            $field = $f['name']; $camel = Str::camel($field); $label = Str::headline($field);
            if ($f['type'] === 'foreignId') {
                $method = Str::camel(str_replace('_id', '', $field));
                $isUuid = $f['isUuid'] ?? false;
                $selectedAssignment = $isUuid ? "selected['id'].toString()" : "int.tryParse(selected['id'].toString())";
                $dartType = $isUuid ? 'String' : 'int';
                $parser = $isUuid ? "widget.item!['{$field}'].toString()" : "int.tryParse(widget.item!['{$field}'].toString())";

                $relationVars .= "  List<Map<String, dynamic>> _{$method}Options = [];\n  {$dartType}? _{$camel};\n";
                $relationLoaders .= "    _{$method}Options = await repository.lookup('" . Str::plural(Str::kebab($f['relatedModel'])) . "');\n    if (widget.item?['{$field}'] != null) _{$camel} = $parser;\n";
                $relationPickers .= <<<DART
  Future<void> _pick$camel() async {
    var filtered = List<Map<String, dynamic>>.from(_{$method}Options);
    final selected = await showModalBottomSheet<Map<String, dynamic>>(
      context: context,
      isScrollControlled: true,
      backgroundColor: Theme.of(context).colorScheme.surface,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(30))),
      builder: (sheetContext) => StatefulBuilder(builder: (context, setSheet) => SizedBox(height: MediaQuery.of(context).size.height * .72, child: Padding(padding: const EdgeInsets.all(20), child: Column(children: [
        Container(width: 42, height: 4, decoration: BoxDecoration(color: Theme.of(context).dividerColor, borderRadius: BorderRadius.circular(10))),
        const SizedBox(height: 18), Align(alignment: Alignment.centerLeft, child: Text({$snake}Tr(context, 'field.{$field}'), style: const TextStyle(fontSize: 21, fontWeight: FontWeight.w800))),
        const SizedBox(height: 14), TextField(decoration: InputDecoration(prefixIcon: const Icon(Icons.search_rounded), hintText: {$snake}Tr(context, 'form.search'), border: const OutlineInputBorder()), onChanged: (q) => setSheet(() => filtered = _{$method}Options.where((e) => _displayName(e).toLowerCase().contains(q.toLowerCase())).toList())),
        const SizedBox(height: 12), Expanded(child: ListView.separated(itemCount: filtered.length, separatorBuilder: (_, __) => const Divider(height: 1), itemBuilder: (_, i) { final option = filtered[i]; return ListTile(title: Text(_displayName(option), style: const TextStyle(fontWeight: FontWeight.w700)), trailing: option['id'].toString() == _{$camel}?.toString() ? const Icon(Icons.check_circle_rounded) : null, onTap: () => Navigator.pop(sheetContext, option)); })),
      ])))),
    );
    if (selected != null) setState(() => _{$camel} = $selectedAssignment);
  }
DART;
                $widgets .= "            _FieldShell(label: " . $snake . "Tr(context, 'field." . $f['name'] . "'), child: InkWell(onTap: _pick$camel, borderRadius: BorderRadius.circular(16), child: Container(padding: const EdgeInsets.all(16), decoration: BoxDecoration(border: Border.all(color: Theme.of(context).colorScheme.outlineVariant), borderRadius: BorderRadius.circular(16)), child: Row(children: [Expanded(child: Text(_displayName(_{$method}Options.firstWhere((e) => e['id'].toString() == _{$camel}?.toString(), orElse: () => {'id': '', 'name': " . $snake . "Tr(context, 'form.select')})))), const Icon(Icons.keyboard_arrow_down_rounded)])))),\n";
                $payload .= "    payload['{$field}'] = _{$camel};\n";
                continue;
            }
            if ($f['type'] === 'boolean') {
                $controllers .= "  bool _{$camel} = false;\n";
                $init .= "    _{$camel} = widget.item?['{$field}'] == true || widget.item?['{$field}'] == 1 || widget.item?['{$field}'] == '1';\n";
                $widgets .= "            Container(decoration: BoxDecoration(border: Border.all(color: Theme.of(context).colorScheme.outlineVariant), borderRadius: BorderRadius.circular(16)), child: SwitchListTile.adaptive(contentPadding: const EdgeInsets.symmetric(horizontal: 16), title: Text({$snake}Tr(context, 'field.{$field}'), style: const TextStyle(fontWeight: FontWeight.w700)), value: _{$camel}, onChanged: (v) => setState(() => _{$camel} = v))),\n";
                $payload .= "    payload['{$field}'] = _{$camel};\n";
                continue;
            }
            if ($f['type'] === 'enum') {
                $opts = '[' . implode(', ', array_map(fn($o) => "'" . addslashes($o) . "'", $f['options'])) . ']';
                $controllers .= "  String? _{$camel};\n";
                $init .= "    _{$camel} = widget.item?['{$field}']?.toString();\n";
                $widgets .= "            _FieldShell(label: " . $snake . "Tr(context, 'field." . $f['name'] . "'), child: DropdownButtonFormField<String>(value: $opts.contains(_{$camel}) ? _{$camel} : null, items: $opts.map((v) => DropdownMenuItem<String>(value: v, child: Text(v))).toList(), onChanged: (v) => setState(() => _{$camel} = v), validator: (v) { if (v == null || v.isEmpty) return " . $snake . "Tr(context, 'form.select'); return null; }, decoration: const InputDecoration(border: InputBorder.none, isDense: true))),\n";
                $payload .= "    payload['{$field}'] = _{$camel};\n";
                continue;
            }
            if ($f['type'] === 'image' || Str::contains(strtolower($f['name']), ['file', 'document', 'image', 'photo'])) {
                $fileState .= "  String? _{$camel}Path;\n";
                $widgets .= "            PremiumImagePicker(label: " . $snake . "Tr(context, 'field." . $f['name'] . "'), path: _{$camel}Path, currentUrl: widget.item?['{$field}'] != null ? '\${ApiService.serverUrl}/\${widget.item!['{$field}']}' : null, onPicked: (p) => setState(() => _{$camel}Path = p)),\n";
                $filesMap .= "    if (_{$camel}Path != null) files['{$field}'] = _{$camel}Path!;\n";
                continue;
            }
            $controller = "_{$camel}Controller";

            if (in_array($f['type'], ['date', 'datetime'], true)) {
                $controllers .= "  final $controller = TextEditingController();\n";
                $dispose .= "    $controller.dispose();\n";
                $init .= "    $controller.text = widget.item?['{$field}']?.toString() ?? '';\n";
                $iconName = $f['type'] === 'date' ? 'calendar_today_rounded' : 'event_rounded';
                $pickMethod = $f['type'] === 'date' ? "_pickDate($controller)" : "_pickDateTime($controller)";
                $placeholder = $f['type'] === 'date' ? 'form.select_date' : 'form.select_datetime';
                $widgets .= "            _FieldShell(label: " . $snake . "Tr(context, 'field." . $f['name'] . "'), child: InkWell(onTap: () => $pickMethod, borderRadius: BorderRadius.circular(12), child: InputDecorator(decoration: InputDecoration(border: InputBorder.none, isDense: true, suffixIcon: const Icon(Icons.$iconName)), child: Text($controller.text.isEmpty ? {$snake}Tr(context, '$placeholder') : $controller.text, style: TextStyle(color: $controller.text.isEmpty ? Theme.of(context).colorScheme.onSurfaceVariant : null, fontWeight: FontWeight.w600))))),\n";
                $payload .= "    payload['{$field}'] = $controller.text.isEmpty ? null : $controller.text;\n";
                continue;
            }

            $controllers .= "  final $controller = TextEditingController();\n";
            $dispose .= "    $controller.dispose();\n";
            if ($f['type'] === 'decimal') {
                $init .= "    final _initial{$camel} = widget.item?['{$field}'];\n    $controller.text = _initial{$camel} == null ? '' : (double.tryParse(_initial{$camel}.toString())?.toStringAsFixed(2) ?? _initial{$camel}.toString());\n";
            } else {
                $init .= "    $controller.text = widget.item?['{$field}']?.toString() ?? '';\n";
            }

            $formatters = '';
            $extraValidator = '';
            $keyboard = '';
            $placeholder = "{\$label}";

            if (in_array($f['type'], ['integer', 'bigInteger'], true)) {
                $keyboard = "keyboardType: const TextInputType.numberWithOptions(decimal: false), ";
                $formatters = 'inputFormatters: [FilteringTextInputFormatter.digitsOnly], ';
                $extraValidator = "if (v != null && v.isNotEmpty && int.tryParse(v) == null) return " . $snake . "Tr(context, 'form.invalid_integer'); ";
                $payload .= "    payload['{$field}'] = int.tryParse($controller.text);\n";
            } elseif ($f['type'] === 'decimal') {
                $keyboard = "keyboardType: const TextInputType.numberWithOptions(decimal: true), ";
                $formatters = "inputFormatters: [TextInputFormatter.withFunction((oldValue, newValue) { final text = newValue.text; if (text.isEmpty || RegExp(r'^\\d*\\.?\\d{0,2}$').hasMatch(text)) return newValue; return oldValue; })], ";
                $extraValidator = "if (v != null && v.isNotEmpty && (double.tryParse(v) == null || !RegExp(r'^\\d+(\\.\\d{1,2})?$').hasMatch(v))) return 'Enter a valid number with up to 2 decimals'; ";
                $placeholder = '0.00';
                $payload .= "    payload['{$field}'] = double.tryParse($controller.text);\n";
            } else {
                $payload .= "    payload['{$field}'] = $controller.text;\n";
            }

            $requiredRule = $f['nullable'] ? '' : "if (v == null || v.trim().isEmpty) return " . $snake . "Tr(context, 'form.required'); ";
            $widgets .= "            _FieldShell(label: " . $snake . "Tr(context, 'field." . $f['name'] . "'), child: TextFormField(controller: $controller, $keyboard$formatters decoration: InputDecoration(hintText: " . $snake . "Tr(context, 'field." . $f['name'] . "'), border: InputBorder.none, isDense: true), validator: (v) { $requiredRule$extraValidator return null; })),\n";

        }

        $saveArgs = $hasFile ? "id: widget.item?['id'], files: files" : "id: widget.item?['id']";

        File::put("$pagesDir/{$snake}_form_page.dart", <<<DART
import 'dart:convert';
import 'dart:io';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_bloc/flutter_bloc.dart';
import 'package:image_picker/image_picker.dart';
import '../../../../../core/widgets/premium_widgets.dart';
import '../../../../../core/widgets/premium_image_picker.dart';
import '../../../../../l10n/{$snake}_localization.dart';
import '../cubit/{$snake}_cubit.dart';
import '../cubit/{$snake}_state.dart';
import '../../data/{$snake}_repository.dart';
import '../../../../../services/api_service.dart';

class {$name}FormPage extends StatefulWidget {
  final Map<String, dynamic>? item;
  const {$name}FormPage({super.key, this.item});
  @override State<{$name}FormPage> createState() => _{$name}FormPageState();
}

class _{$name}FormPageState extends State<{$name}FormPage> {
  final _formKey = GlobalKey<FormState>();
  final repository = {$name}Repository();
  bool _loading = true;
$fileState
$controllers
$relationVars

  @override void initState() { super.initState(); _init(); }

  Future<void> _init() async {
$init
    try {
$relationLoaders    } catch (_) {
      if (mounted) ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text({$snake}Tr(context, 'form.could_not_load')), behavior: SnackBarBehavior.floating));
    } finally { if (mounted) setState(() => _loading = false); }
  }

$relationPickers
  Future<void> _pickDate(TextEditingController controller) async {
    final initial = DateTime.tryParse(controller.text) ?? DateTime.now();
    final picked = await showDatePicker(context: context, initialDate: initial, firstDate: DateTime(1900), lastDate: DateTime(2200));
    if (picked != null && mounted) setState(() => controller.text = picked.toIso8601String().split('T').first);
  }

  Future<void> _pickDateTime(TextEditingController controller) async {
    final initial = DateTime.tryParse(controller.text) ?? DateTime.now();
    final date = await showDatePicker(context: context, initialDate: initial, firstDate: DateTime(1900), lastDate: DateTime(2200));
    if (date == null || !mounted) return;
    final time = await showTimePicker(context: context, initialTime: TimeOfDay.fromDateTime(initial));
    if (time == null || !mounted) return;
    final value = DateTime(date.year, date.month, date.day, time.hour, time.minute);
    setState(() => controller.text = value.toIso8601String());
  }

  String _displayName(Map<String, dynamic> item) {
    return (item['name'] ?? item['title'] ?? 'ID: ' + item['id'].toString()).toString();
  }

  Future<void> _save(BuildContext context) async {
    if (!_formKey.currentState!.validate()) return;
    final payload = <String, dynamic>{};
$payload
    final files = <String, String>{};
$filesMap
    context.read<{$name}Cubit>().save(payload, $saveArgs);
  }

  @override Widget build(BuildContext context) {
    final theme = Theme.of(context);
    return BlocProvider(
      create: (_) => {$name}Cubit(repository),
      child: BlocListener<{$name}Cubit, {$name}State>(
        listener: (context, state) {
          if (state is {$name}Saved) Navigator.pop(context, true);
          if (state is {$name}Failure) {
            final message = state.message.trim().isEmpty ? {$snake}Tr(context, 'form.save_error') : state.message;
            ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(message), behavior: SnackBarBehavior.floating, duration: const Duration(seconds: 4)));
          }
        },
        child: Scaffold(
          appBar: AppBar(title: Text(widget.item == null ? {$snake}Tr(context, 'form.create_title') : {$snake}Tr(context, 'form.edit_title'), style: const TextStyle(fontWeight: FontWeight.w800))),
          body: _loading ? const Center(child: CircularProgressIndicator.adaptive()) : Form(key: _formKey, child: Builder(builder: (formContext) => ListView(padding: const EdgeInsets.fromLTRB(20, 12, 20, 120), children: [
            _FormHeader(isEdit: widget.item != null),
            const SizedBox(height: 22),
$widgets
            const SizedBox(height: 14),
            BlocBuilder<{$name}Cubit, {$name}State>(builder: (context, state) => PremiumButton(onPressed: () => _save(context), label: state is {$name}Saving ? {$snake}Tr(context, 'form.saving') : {$snake}Tr(context, 'form.save'), icon: Icons.check_rounded, loading: state is {$name}Saving, expand: true)),
          ]))),
        ),
      ),
    );
  }

  @override void dispose() {
$dispose    super.dispose();
  }
}
class _FormHeader extends StatelessWidget {
  final bool isEdit;
  const _FormHeader({required this.isEdit});
  @override Widget build(BuildContext context) => Container(padding: const EdgeInsets.all(20), decoration: BoxDecoration(gradient: LinearGradient(colors: [Theme.of(context).colorScheme.primaryContainer, Theme.of(context).colorScheme.secondaryContainer]), borderRadius: BorderRadius.circular(24)), child: Row(children: [Container(width: 48, height: 48, decoration: BoxDecoration(color: Theme.of(context).colorScheme.surface.withOpacity(.7), borderRadius: BorderRadius.circular(15)), child: Icon(isEdit ? Icons.edit_rounded : Icons.add_rounded)), const SizedBox(width: 14), Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [Text(isEdit ? {$snake}Tr(context, 'form.update_record') : {$snake}Tr(context, 'form.new_record'), style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w900)), const SizedBox(height: 3), Text(isEdit ? {$snake}Tr(context, 'form.review_update') : {$snake}Tr(context, 'form.fill_create'), style: TextStyle(fontSize: 12, color: Theme.of(context).colorScheme.onSurfaceVariant))]))]));
}

class _FieldShell extends StatelessWidget {
  final String label; final Widget child;
  const _FieldShell({required this.label, required this.child});
  @override Widget build(BuildContext context) => Container(margin: const EdgeInsets.only(bottom: 14), padding: const EdgeInsets.fromLTRB(16, 12, 16, 6), decoration: BoxDecoration(color: Theme.of(context).colorScheme.surfaceContainerHighest.withOpacity(.35), border: Border.all(color: Theme.of(context).colorScheme.outlineVariant.withOpacity(.7)), borderRadius: BorderRadius.circular(18)), child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [Text(label, style: TextStyle(fontSize: 11, fontWeight: FontWeight.w800, color: Theme.of(context).colorScheme.onSurfaceVariant)), child]));
}

class _FilePickerCard extends StatelessWidget {
  final String? current, path; final VoidCallback onPick;
  const _FilePickerCard({this.current, this.path, required this.onPick});
  @override Widget build(BuildContext context) => InkWell(onTap: onPick, borderRadius: BorderRadius.circular(20), child: Container(height: 150, margin: const EdgeInsets.only(bottom: 14), decoration: BoxDecoration(borderRadius: BorderRadius.circular(20), border: Border.all(color: Theme.of(context).colorScheme.outlineVariant), color: Theme.of(context).colorScheme.surfaceContainerHighest.withOpacity(.25)), child: path != null ? ClipRRect(borderRadius: BorderRadius.circular(20), child: Image.file(File(path!), fit: BoxFit.cover, width: double.infinity)) : current != null ? ClipRRect(borderRadius: BorderRadius.circular(20), child: Image.network('\${ApiService.serverUrl}/\$current', fit: BoxFit.cover, width: double.infinity)) : const Column(mainAxisAlignment: MainAxisAlignment.center, children: [Icon(Icons.cloud_upload_outlined, size: 34), SizedBox(height: 8), Text('Tap to choose image', style: TextStyle(fontWeight: FontWeight.w700)), SizedBox(height: 3), Text('PNG, JPG', style: TextStyle(fontSize: 11, color: Colors.grey))])));
}
DART);

        $imagePickerPath = "$coreDir/widgets/premium_image_picker.dart";
        File::makeDirectory(dirname($imagePickerPath), 0755, true, true);
        File::put($imagePickerPath, <<<'DART'
import 'dart:io';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';

class PremiumImagePicker extends StatelessWidget {
  final String label;
  final String? path;
  final String? currentUrl;
  final ValueChanged<String> onPicked;
  final double height;

  const PremiumImagePicker({
    super.key,
    required this.label,
    required this.path,
    required this.currentUrl,
    required this.onPicked,
    this.height = 190,
  });

  Future<void> _pick(BuildContext context) async {
    final picker = ImagePicker();
    final source = await showModalBottomSheet<ImageSource>(
      context: context,
      showDragHandle: true,
      builder: (_) => SafeArea(
        child: Wrap(children: [
          ListTile(leading: const Icon(Icons.photo_camera_rounded), title: const Text('Camera'), onTap: () => Navigator.pop(context, ImageSource.camera)),
          ListTile(leading: const Icon(Icons.photo_library_rounded), title: const Text('Gallery'), onTap: () => Navigator.pop(context, ImageSource.gallery)),
        ]),
      ),
    );
    if (source == null) return;
    final image = await picker.pickImage(source: source, imageQuality: 88, maxWidth: 1800, maxHeight: 1800);
    if (image != null) onPicked(image.path);
  }

  @override
  Widget build(BuildContext context) {
    final theme = Theme.of(context);
    Widget preview;
    if (path != null && path!.isNotEmpty) {
      preview = Image.file(File(path!), fit: BoxFit.cover, width: double.infinity, height: height);
    } else if (currentUrl != null && currentUrl!.isNotEmpty) {
      preview = Image.network(currentUrl!, fit: BoxFit.cover, width: double.infinity, height: height, errorBuilder: (_, __, ___) => _placeholder(theme));
    } else {
      preview = _placeholder(theme);
    }

    return Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
      Padding(padding: const EdgeInsets.only(left: 4, bottom: 8), child: Text(label, style: TextStyle(fontSize: 12, fontWeight: FontWeight.w800, color: theme.colorScheme.onSurfaceVariant))),
      InkWell(onTap: () => _pick(context), borderRadius: BorderRadius.circular(22), child: ClipRRect(borderRadius: BorderRadius.circular(22), child: Stack(children: [
        preview,
        Positioned.fill(child: IgnorePointer(child: DecoratedBox(decoration: BoxDecoration(gradient: LinearGradient(begin: Alignment.topCenter, end: Alignment.bottomCenter, colors: [Colors.transparent, Colors.black.withOpacity(.38)]))))),
        Positioned(left: 16, right: 16, bottom: 14, child: Row(children: [
          Container(padding: const EdgeInsets.all(10), decoration: BoxDecoration(color: Colors.black.withOpacity(.45), shape: BoxShape.circle), child: const Icon(Icons.camera_alt_rounded, color: Colors.white, size: 18)),
          const SizedBox(width: 10),
          const Expanded(child: Text('Tap to change image', style: TextStyle(color: Colors.white, fontWeight: FontWeight.w800))),
        ])),
      ]))),
    ]);
  }

  Widget _placeholder(ThemeData theme) => Container(height: height, width: double.infinity, color: theme.colorScheme.surfaceContainerHighest.withOpacity(.35), child: Column(mainAxisAlignment: MainAxisAlignment.center, children: [
    Icon(Icons.add_photo_alternate_outlined, size: 42, color: theme.colorScheme.primary),
    const SizedBox(height: 10),
    const Text('Choose an image', style: TextStyle(fontWeight: FontWeight.w800)),
    const SizedBox(height: 4),
    const Text('JPG, PNG • optimized automatically', style: TextStyle(fontSize: 11)),
  ]));
}
DART
);

        // Fix generated imports with a leading space and add required dependencies.
        $formPath = "$pagesDir/{$snake}_form_page.dart";
        $form = File::get($formPath);
        File::put($formPath, str_replace("import ' {$snake}", "import '{$snake}", $form));

        $realtimeDir = "$coreDir/realtime";
        File::makeDirectory($realtimeDir, 0755, true, true);
        // Reverb is a shared Flutter core service. Never overwrite its config when
        // another CRUD module is scaffolded; doing so resets real host/key/API values
        // and can produce CONFIG ERR in the APK.
        $realtimeConfigPath = "$realtimeDir/realtime_config.dart";
        if (!File::exists($realtimeConfigPath)) {
            File::put($realtimeConfigPath, <<<'DART'
class RealtimeConfig {
  static const host = String.fromEnvironment('REVERB_HOST', defaultValue: '127.0.0.1');
  static const port = int.fromEnvironment('REVERB_PORT', defaultValue: 8080);
  static const appKey = String.fromEnvironment('REVERB_APP_KEY', defaultValue: 'CHANGE_ME');
  static const apiBaseUrl = String.fromEnvironment('API_BASE_URL', defaultValue: 'http://10.0.2.2:8000');
  static const useTls = bool.fromEnvironment('REVERB_TLS', defaultValue: false);
  static String get authEndpoint => '${apiBaseUrl}/broadcasting/auth';
}
DART);
        }
        File::put("$realtimeDir/realtime_service.dart", <<<'DART'
import 'dart:convert';
import 'package:pusher_reverb_flutter/pusher_reverb_flutter.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'realtime_config.dart';

class RealtimeService {
  RealtimeService._();
  static final instance = RealtimeService._();

  ReverbClient? _client;
  ReverbClient? get client => _client;
  final Set<String> _subscribed = {};
  final Map<String, List<void Function(String action, Map<String, dynamic> data)>> _listeners = {};

  Future<void> start() async {
    if (_client != null) return;
    final prefs = await SharedPreferences.getInstance();
    final token = prefs.getString('auth_token');
    if (token == null || token.isEmpty || RealtimeConfig.appKey == 'CHANGE_ME') return;

    Future<Map<String, String>> authorizer(String channelName, String socketId) async => {
      'Authorization': 'Bearer $token',
      'Accept': 'application/json',
    };

    _client = ReverbClient.instance(
      host: RealtimeConfig.host,
      port: RealtimeConfig.port,
      appKey: RealtimeConfig.appKey,
      useTLS: RealtimeConfig.useTls,
      authEndpoint: RealtimeConfig.authEndpoint,
      authorizer: authorizer,
    );
    await _client!.connect();
  }

  Future<void> subscribe(String resource, void Function(String action, Map<String, dynamic> data) listener) async {
    _listeners.putIfAbsent(resource, () => []).add(listener);
    await start();
    if (_client == null || _subscribed.contains(resource)) return;

    final channel = _client!.subscribeToPrivateChannel('private-mobile.$resource');
    channel.bind('$resource.changed', (eventName, eventData) {
      final data = eventData is String ? jsonDecode(eventData) : eventData;
      if (data is! Map) return;
      final action = data['action']?.toString();
      final record = Map<String, dynamic>.from(data['data'] is Map ? data['data'] as Map : {});
      if (action == null) return;
      for (final callback in List.of(_listeners[resource] ?? const [])) {
        callback(action, record);
      }
    });
    _subscribed.add(resource);
  }

  Future<void> stop() async {
    for (final resource in _subscribed) {
      _client?.unsubscribeFromChannel('private-mobile.$resource');
    }
    _subscribed.clear();
    _listeners.clear();
    _client?.disconnect();
    _client = null;
  }
}
DART
);

        if ($this->option('firebase')) {
            $firebaseDir = "$coreDir/notifications";
            File::makeDirectory($firebaseDir, 0755, true, true);
            File::put("$firebaseDir/push_service.dart", <<<'DART'
import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';

@pragma('vm:entry-point')
Future<void> firebaseMessagingBackgroundHandler(RemoteMessage message) async {
  await Firebase.initializeApp();
}

class PushService {
  static Future<void> initialize() async {
    await Firebase.initializeApp();
    FirebaseMessaging.onBackgroundMessage(firebaseMessagingBackgroundHandler);
    await FirebaseMessaging.instance.requestPermission(alert: true, badge: true, sound: true);

    final token = await FirebaseMessaging.instance.getToken();
    if (token != null) {
      // TODO: send this token to your Laravel / device-tokens endpoint.
    }

    FirebaseMessaging.onMessage.listen((message) {
      // Realtime UI is handled by Reverb. FCM is for background/terminated delivery.
    });
  }
}
DART
);
            $this->warn('Firebase files generated. Add google-services.json / GoogleService-Info.plist and call PushService.initialize() in main().');
        }

        $pubspec = base_path('mobile-gateway/pubspec.yaml');
        if (File::exists($pubspec)) {
            $yaml = File::get($pubspec);
            $deps = [
                'flutter_bloc' => '^9.1.1',
                'image_picker' => '^1.1.2',
                'shared_preferences' => '^2.5.3',
                'pusher_reverb_flutter' => '^0.0.10',
            ];
            if ($this->option('firebase')) {
                $deps['firebase_core'] = '^4.0.0';
                $deps['firebase_messaging'] = '^16.0.0';
            }
            foreach ($deps as $package => $version) {
                if (!preg_match('/^\s*' . preg_quote($package, '/') . '\s*:/m', $yaml)) {
                    $yaml = preg_replace('/^dependencies:\s*$/m', "dependencies:\n  $package: $version", $yaml, 1, $count);
                    if ($count === 0) $this->warn("Could not add $package automatically; add it to mobile-gateway/pubspec.yaml.");
                }
            }
            File::put($pubspec, $yaml);
            $this->warn('Run: flutter pub get');
        } else {
            $this->warn("Flutter project not found at $base — Laravel/API was generated, Flutter files were skipped.");
        }

        $this->info("📱 PREMIUM Flutter BLoC + Realtime module generated: {$snake}");
        $this->warn('Realtime requires Laravel Broadcasting/Reverb, a Sanctum token stored as SharedPreferences key auth_token, and dart-defines for REVERB_HOST/PORT/APP_KEY/API_BASE_URL.');

        $this->registerFlutterModule($name, $snake, $icon);
    }

    protected function registerFlutterModule($name, $snake, $icon)
    {
        $registryPath = base_path('mobile-gateway/lib/modules/dashboard/presentation/pages/module_registry.dart');
        if (!File::exists($registryPath)) return;

        $content = File::get($registryPath);

        $import = "import '../../$snake/presentation/pages/{$snake}_list_page.dart';";
        if (!str_contains($content, $import)) {
            $content = str_replace('// [REGISTRY_IMPORTS]', "$import\n    // [REGISTRY_IMPORTS]", $content);
        }

        $flutterIcon = match($icon) {
            'bolt' => 'Icons.bolt_rounded',
            'user' => 'Icons.person_outline_rounded',
            'users' => 'Icons.group_outlined',
            'home' => 'Icons.home_outlined',
            'cog', 'settings' => 'Icons.settings_suggest_outlined',
            'bell' => 'Icons.notifications_active_outlined',
            'chart-bar' => 'Icons.bar_chart_rounded',
            'archive-box', 'box' => 'Icons.inventory_2_outlined',
            'document', 'file' => 'Icons.description_outlined',
            'building-office', 'office-building' => 'Icons.business_outlined',
            'computer-desktop', 'desktop' => 'Icons.desktop_windows_outlined',
            'pencil-square', 'pencil' => 'Icons.edit_outlined',
            default => 'Icons.layers_outlined',
        };

        $entry = "ModuleEntry(name: '$name', icon: $flutterIcon, page: const {$name}ListPage()),";
        if (!str_contains($content, $entry)) {
            $content = str_replace('// [REGISTRY_ENTRIES]', "$entry\n    // [REGISTRY_ENTRIES]", $content);
        }

        File::put($registryPath, $content);
        $this->info("✓ Registered module in Flutter ModuleRegistry");
    }

}
