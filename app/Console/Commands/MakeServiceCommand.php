<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeServiceCommand extends Command
{
    protected $signature = 'make:service {name} {--model=} {--force : Sobrescribir el archivo si existe}';
    protected $description = 'Create a new service class';

    public function handle()
    {
        try {
            $name = $this->argument('name');
            $model = $this->option('model');
            $version = env("APP_VERSION", "V1");
            $force = $this->option('force');

            // Separar el nombre en partes usando '/'
            $parts = explode('/', $name);

            // El último elemento será el nombre de la clase
            $className = array_pop($parts);

            // Asegurar que el nombre de la clase termine en 'Service'
            $className = Str::studly($className);


            // Si se proporciona un modelo, asegurarse de que tenga el formato correcto
            if ($model) {
                $model = Str::studly($model);
                // Si el modelo no comienza con \, añadir el namespace completo
                if (!Str::startsWith($model, '\\')) {
                    $model = '\\App\\Models\\' . $model;
                }
            }

            // Construir el namespace completo
            $subNamespace = count($parts) > 0 ? '\\' . implode('\\', $parts) : '';
            $namespace = 'App\Services\\' . $version . $subNamespace;

            // Reemplazar en la plantilla
            $serviceTemplate = $this->buildClass($className, $namespace, $model);

            // Construir la ruta del archivo
            $basePath = app_path('Services') . "/{$version}/";
            if (count($parts) > 0) {
                $basePath .= implode('/', $parts) . '/';
            }

            // Crear directorios recursivamente
            if (!File::exists($basePath)) {
                File::makeDirectory($basePath, 0755, true);
            }

            $filePath = $basePath . $className . 'Service.php';

            if (File::exists($filePath) && !$force) {
                $this->error("Service [{$className}] already exists!");
                return false;
            }

            File::put($filePath, $serviceTemplate);

            $this->info("Service [{$className}] created successfully.");

            // Mostrar la ruta del archivo creado
            $this->comment("Created Service: {$filePath}");

            // Si se creó con un modelo, mostrar información adicional
            if ($model) {
                $this->line("Created with model: {$model}");
            }

            return true;

        } catch (\Exception $e) {
            $this->error("Error creating service: " . $e->getMessage());
            return false;
        }
    }

    private function buildClass($name, $namespace, $model = null)
    {
        $stub = file_get_contents($this->getStub());

        $replacements = [
            '{{ namespace }}' => $namespace,
            '{{ serviceName }}' => $name,
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
}
