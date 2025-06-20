<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeServiceCommand extends Command
{
    protected $signature = 'make:service {name} {model : El modelo asociado al servicio} {--force : Sobrescribir el archivo si existe}';
    protected $description = 'Create a new service class';

    public function handle()
    {
        try {
            $name = $this->argument('name');
            $model = $this->argument('model'); // Ahora es obligatorio
            $version = env("APP_VERSION", "V1");
            $force = $this->option('force');

            // Validar que el modelo fue proporcionado
            if (empty($model)) {
                $this->error('El modelo es obligatorio para crear un servicio.');
                $this->comment('Uso: php artisan make:service {name} {model}');
                return false;
            }

            // Separar el nombre en partes usando '/'
            $parts = explode('/', $name);

            // El último elemento será el nombre de la clase
            $className = array_pop($parts);

            // Asegurar que el nombre de la clase termine en 'Service'
            $className = $this->validateClassName($className);

            // Si se proporciona un modelo, asegurarse de que tenga el formato correcto
            $model = Str::studly($model);
            // Si el modelo no comienza con \, añadir el namespace completo
            if (!Str::startsWith($model, '\\')) {
                $model = '\\App\\Models\\' . $model;
            }

            // Validar que el modelo existe
            $modelClass = ltrim($model, '\\');
            if (!class_exists($modelClass)) {
                $this->warn("El modelo {$modelClass} no existe.");
                if (!$this->confirm('¿Continuar de todos modos?')) {
                    return false;
                }
            }

            // Construir el namespace completo
            $subNamespace = count($parts) > 0 ? '\\' . implode('\\', $parts) : '';
            $namespace = 'App\Services\\' . $version . $subNamespace;

            // Crear el directorio Services/V1 si no existe
            $servicesBasePath = app_path('Services') . "/{$version}/";
            if (!File::exists($servicesBasePath)) {
                File::makeDirectory($servicesBasePath, 0755, true);
            }

            // Reemplazar en la plantilla
            $serviceTemplate = $this->buildClass($className, $namespace, $model, $version);

            // Construir la ruta del archivo
            $basePath = $servicesBasePath;
            if (count($parts) > 0) {
                $basePath .= implode('/', $parts) . '/';
            }

            // Crear directorios recursivamente
            if (!File::exists($basePath)) {
                File::makeDirectory($basePath, 0755, true);
            }

            $filePath = $basePath . $className . '.php';

            if (File::exists($filePath) && !$force) {
                $this->error("Service [{$className}] already exists!");
                $this->comment("Use --force to overwrite");
                return false;
            }

            File::put($filePath, $serviceTemplate);

            $this->info("Service [{$className}] created successfully.");
            $this->comment("File: {$filePath}");
            $this->line("Model: {$model}");
            $this->line("Usage: app({$namespace}\\{$className}::class)->findById(\$id);");

            return true;

        } catch (\Exception $e) {
            $this->error("Error creating service: " . $e->getMessage());
            return false;
        }
    }

    private function buildClass($name, $namespace, $model = null, $version = 'V1')
    {
        $stub = file_get_contents($this->getStub());

        $replacements = [
            '{{ namespace }}' => $namespace,
            '{{ serviceName }}' => str_replace('Service', '', $name), // Remover 'Service' para evitar duplicación
            '{{ version }}' => $version,
            '{{ modelName }}' => $model ?: '',
            '{{ modelImport }}' => $model ? "use {$model};" : '',
            '{{ modelClass }}' => $model ? class_basename($model) : '',
        ];

        return str_replace(
            array_keys($replacements),
            array_values($replacements),
            $stub
        );
    }

    protected function getStub()
    {
        return base_path('stubs/service.stub');
    }

    /**
     * Validate and format the class name
     */
    private function validateClassName(string $name): string
    {
        $className = Str::studly($name);

        // Auto-agregar 'Service' si no termina con eso
        if (!Str::endsWith($className, 'Service')) {
            $className .= 'Service';
        }

        // Validar que sea un nombre válido de clase PHP
        if (!preg_match('/^[A-Z][a-zA-Z0-9]*Service$/', $className)) {
            throw new \InvalidArgumentException("Invalid service name: {$className}");
        }

        return $className;
    }
}
