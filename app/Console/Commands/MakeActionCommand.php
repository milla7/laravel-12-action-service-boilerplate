<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeActionCommand extends Command
{
    protected $signature = 'make:action {name} {--force : Sobrescribir archivo si existe}';
    protected $description = 'Create a new action class with enhanced features';

    public function handle()
    {
        try {
            $this->createSingleAction();
            return true;
        } catch (\Exception $e) {
            $this->error("Error creating action: " . $e->getMessage());
            return false;
        }
    }

    private function createSingleAction()
    {
        $name = $this->argument('name');
        $version = env("APP_VERSION", "V1");
        $force = $this->option('force');

        // Separar el nombre en partes usando '/'
        $parts = explode('/', $name);

        // El último elemento será el nombre de la clase
        $className = array_pop($parts);

        // Validar y formatear el nombre de la clase
        $className = $this->validateClassName($className);

        // Construir el namespace completo
        $subNamespace = count($parts) > 0 ? '\\' . implode('\\', $parts) : '';
        $namespace = 'App\Actions\\' . $version . $subNamespace;

        // Construir la clase usando el método buildClass
        $actionContent = $this->buildClass($className, $namespace, $version);

        // Construir la ruta del archivo
        $basePath = app_path('Actions') . "/{$version}/";
        if (count($parts) > 0) {
            $basePath .= implode('/', $parts) . '/';
        }

        // Crear directorios recursivamente
        if (!File::exists($basePath)) {
            File::makeDirectory($basePath, 0755, true);
        }

        $filePath = $basePath . $className . '.php';

        // Validar archivo existente con opción force
        if (File::exists($filePath)) {
            if (!$force) {
                $this->error("Action [{$className}] already exists!");
                $this->comment("Use --force to overwrite");
                return false;
            }
            $this->warn("Overwriting existing action...");
        }

        File::put($filePath, $actionContent);

        // Feedback mejorado al usuario
        $this->info("Action [{$className}] created successfully.");
        $this->comment("File: {$filePath}");
        $this->line("Usage: app({$namespace}\\{$className}::class)->execute(\$data);");

        return true;
    }

    /**
     * Validar y formatear el nombre de la clase
     */
    private function validateClassName(string $name): string
    {
        $className = Str::studly($name);

        // Auto-agregar 'Action' si no termina con eso
        if (!Str::endsWith($className, 'Action')) {
            $className .= 'Action';
        }

        // Validar que sea un nombre válido de clase PHP
        if (!preg_match('/^[A-Z][a-zA-Z0-9]*Action$/', $className)) {
            throw new \InvalidArgumentException("Invalid action name: {$className}");
        }

        return $className;
    }

    /**
     * Construir la clase Action usando el stub
     */
    private function buildClass(string $name, string $namespace, string $version): string
    {
        $stub = file_get_contents(base_path('stubs/action.stub'));

        $replacements = [
            '{{ actionName }}' => str_replace('Action', '', $name), // Remover 'Action' para el stub
            '{{ namespace }}' => $namespace,
            '{{ version }}' => $version,
        ];

        return str_replace(
            array_keys($replacements),
            array_values($replacements),
            $stub
        );
    }
}
