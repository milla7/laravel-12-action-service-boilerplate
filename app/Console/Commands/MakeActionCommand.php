<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeActionCommand extends Command
{
    protected $signature = 'make:action {name}';
    protected $description = 'Create a new action class';

    public function handle()
    {
        $this->createSingleAction();
    }

    private function createSingleAction()
    {
        $name = $this->argument('name');
        $version = env("APP_VERSION", "V1");

        // Separar el nombre en partes usando '/'
        $parts = explode('/', $name);

        // El último elemento será el nombre de la clase
        $className = array_pop($parts);

        // Construir el namespace completo
        $subNamespace = count($parts) > 0 ? '\\' . implode('\\', $parts) : '';
        $namespace = 'App\Actions\\' . $version . $subNamespace;
        // Reemplazar en la plantilla
        $actionTemplate = file_get_contents(base_path('stubs/action.stub'));
        $actionTemplate = str_replace(
            ['{{ actionName }}', '{{ namespace }}', '{{ version }}'],
            [$className, $namespace, $version],
            $actionTemplate
        );

        // Construir la ruta del archivo
        $basePath = app_path('Actions') . "/{$version}/";
        if (count($parts) > 0) {
            $basePath .= implode('/', $parts) . '/';
        }

        // Crear directorios recursivamente
        if (!File::exists($basePath)) {
            File::makeDirectory($basePath, 0755, true);
        }

        $filePath = $basePath . $className . 'Action.php';

        if (File::exists($filePath)) {
            $this->error('Action already exists!');
            return;
        }

        File::put($filePath, $actionTemplate);
        $this->info('Action created successfully!');
    }
}
