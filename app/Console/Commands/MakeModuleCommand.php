<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MakeModuleCommand extends Command
{
    protected $signature = 'make:module {name} {--force : Sobrescribir los archivos si existen}';
    protected $description = 'Crea un nuevo módulo con Servicio, Acciones CRUD, Modelo, Migración, Controlador y Seeder';

    public function handle()
    {
        $name = $this->argument('name');
        $force = $this->option('force');

        // Analizar el nombre para obtener partes
        $parts = explode('/', $name);
        $modelName = Str::studly(array_pop($parts));
        $subNamespace = implode('/', $parts);

        $this->info("Creando módulo para {$modelName}...");

        $forceOptions = $force ? ['--force' => true] : [];

        // 1. Crear Modelo y Migración
        $this->callArtisan('make:model', $modelName, array_merge(['-m' => true], $forceOptions));

        // 2. Crear Servicio
        $serviceName = "{$modelName}Service";
        $this->callArtisan('make:service', $serviceName, array_merge(['model' => $modelName], $forceOptions));

        // 3. Crear Acciones CRUD
        $actions = [
            "Get{$modelName}",
            "List{$modelName}",
            "Create{$modelName}",
            "Update{$modelName}",
            "Delete{$modelName}",
        ];

        foreach ($actions as $action) {
            $actionName = !empty($subNamespace) ? "{$subNamespace}/{$modelName}/{$action}" : "{$modelName}/{$action}";
            $this->callArtisan('make:action', $actionName, $forceOptions);
        }

        // 4. Crear Controlador
        $controllerName = !empty($subNamespace) ? "Api/{$subNamespace}/{$modelName}Controller" : "Api/{$modelName}Controller";
        $version = env("APP_VERSION", "V1");
        $controllerName = "{$version}/{$controllerName}";
        $this->callArtisan('make:controller', $controllerName, array_merge(['--api' => true], $forceOptions));

        // 5. Crear Seeder
        $seederName = "{$modelName}Seeder";
        $seederPath = database_path("seeders/{$seederName}.php");

        if (File::exists($seederPath)) {
            if ($force) {
                $this->warn("Sobrescribiendo seeder existente: {$seederName}");
                File::delete($seederPath);
                $this->callArtisan('make:seeder', $seederName);
            } else {
                $this->warn("Seeder [{$seederName}] ya existe. Saltando creación.");
            }
        } else {
            $this->callArtisan('make:seeder', $seederName);
        }

        $this->info("Módulo {$modelName} creado exitosamente.");

        return 0;
    }

    private function callArtisan($command, $name, $options = [])
    {
        $params = array_merge(['name' => $name], $options);

        $optionsString = collect($options)->map(function ($value, $key) {
            if ($value === true) {
                return $key;
            }
            if (!is_bool($value)) {
                return is_numeric($key) ? $value : "{$key}={$value}";
            }
            return '';
        })->filter()->implode(' ');

        $this->line("-> Ejecutando: php artisan {$command} {$name} {$optionsString}");

        Artisan::call($command, $params, $this->getOutput());
    }
}
