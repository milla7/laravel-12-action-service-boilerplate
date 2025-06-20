# Laravel 12 Action-Service Boilerplate

<p align="center">
<img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo">
</p>

<p align="center">
<a href="https://github.com/milla7/laravel-12-action-service-boilerplate"><img src="https://img.shields.io/github/stars/milla7/laravel-12-action-service-boilerplate" alt="GitHub Stars"></a>
<a href="https://github.com/milla7/laravel-12-action-service-boilerplate/fork"><img src="https://img.shields.io/github/forks/milla7/laravel-12-action-service-boilerplate" alt="GitHub Forks"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Laravel Version"></a>
<a href="https://opensource.org/licenses/MIT"><img src="https://img.shields.io/badge/License-MIT-yellow.svg" alt="License"></a>
</p>

## 🚀 Acerca de este Boilerplate

Este es un **boilerplate de Laravel 12** que implementa el **patrón Action-Service** con manejo centralizado de excepciones, diseñado para desarrollar aplicaciones web robustas y escalables siguiendo principios de arquitectura limpia.

### ✨ Características Principales

- **🏗️ Arquitectura Action-Service**: Separación clara entre lógica de negocio (Actions) y operaciones de dominio (Services)
- **🛡️ Manejo Centralizado de Excepciones**: Template Method Pattern implementado en Actions base
- **🔧 Comandos Artisan Mejorados**: Generadores avanzados con opciones --force y validaciones
- **📱 Livewire 3 Integrado**: Componentes reactivos con manejo de ActionResult
- **🔐 Laravel Sanctum**: Autenticación API lista para usar
- **🧪 Testing Setup**: Estructura de testing para Actions con ejemplos
- **📋 Validación Avanzada**: Manejo de errores de validación personalizado

## 🏛️ Arquitectura

### Action Pattern
```php
// Las Actions encapsulan casos de uso específicos
class CreateUserAction extends Action
{
    public function handle($data): ActionResult
    {
        $this->validatePermissions(['users.create']);
        
        $validated = $this->validateData($data, [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
        ]);

        return DB::transaction(function () use ($validated) {
            $user = User::create($validated);
            return $this->successResult($user, 'Usuario creado exitosamente');
        });
    }
}
```

### Service Pattern
```php
// Los Services manejan operaciones de dominio
class UserService
{
    public function getPaginated(array $filters = []): LengthAwarePaginator
    {
        return User::query()
            ->when($filters['search'], fn($q, $search) => 
                $q->where('name', 'like', "%{$search}%")
            )
            ->paginate();
    }
}
```

### ActionResult Pattern
```php
// Respuestas consistentes en toda la aplicación
$result = app(CreateUserAction::class)->execute($data);

if ($result->success) {
    return response()->json($result->toArray(), $result->statusCode);
}

return response()->json($result->toArray(), $result->statusCode);
```

## 🛠️ Instalación

1. **Clonar el repositorio**
```bash
git clone https://github.com/milla7/laravel-12-action-service-boilerplate.git
cd laravel-12-action-service-boilerplate
```

2. **Instalar dependencias**
```bash
composer install
npm install
```

3. **Configurar entorno**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Configurar base de datos**
```bash
# Editar .env con tu configuración de BD
php artisan migrate
php artisan db:seed
```

5. **Compilar assets**
```bash
npm run dev
```

## 🎯 Uso

### Crear una Nueva Action
```bash
# Comando básico
php artisan make:action CreateProduct

# Comando mejorado con opciones avanzadas
php artisan make:action-enhanced CreateProduct --force

# Con subdirectorios
php artisan make:action-enhanced Product/CreateProduct
```

### Crear un Nuevo Service
```bash
# Service básico
php artisan make:service ProductService

# Service con modelo asociado
php artisan make:service ProductService --model=Product

# Forzar sobrescritura
php artisan make:service ProductService --force
```

### Usar Actions en Controladores
```php
class ProductController extends Controller
{
    public function store(Request $request)
    {
        $result = app(CreateProductAction::class)->execute($request->all());
        
        return response()->json($result->toArray(), $result->statusCode);
    }
}
```

### Usar Actions en Livewire
```php
class ProductForm extends Component
{
    use HandlesActionResults;
    
    public function save()
    {
        $result = app(CreateProductAction::class)->execute($this->form);
        
        $this->handleActionResult($result, 
            successMessage: 'Producto creado exitosamente'
        );
    }
}
```

## 📁 Estructura del Proyecto

```
app/
├── Actions/V1/              # Actions organizadas por versión
│   ├── Action.php          # Clase base con manejo centralizado
│   └── ExampleAction.php   # Ejemplo de implementación
├── Services/V1/            # Services por versión
├── Support/               # Clases de soporte
│   └── ActionResult.php   # Clase para respuestas consistentes
├── Livewire/
│   └── Concerns/          # Traits para Livewire
└── Console/Commands/      # Comandos Artisan personalizados
```

## 🧪 Testing

```bash
# Ejecutar todos los tests
php artisan test

# Test específico de una Action
php artisan test --filter=ExampleActionTest
```

## 📝 Características Técnicas

- **Laravel 12**: Última versión estable
- **PHP 8.2+**: Características modernas de PHP
- **Livewire 3.6**: Componentes reactivos
- **Laravel Sanctum**: Autenticación API
- **Template Method Pattern**: Manejo centralizado de excepciones
- **ActionResult Pattern**: Respuestas consistentes
- **Command Enhancement**: Generadores mejorados

## 🤝 Contribuir

Las contribuciones son bienvenidas. Por favor:

1. Fork el proyecto
2. Crea una branch para tu feature (`git checkout -b feature/nueva-caracteristica`)
3. Commit tus cambios (`git commit -m 'feat: agregar nueva característica'`)
4. Push a la branch (`git push origin feature/nueva-caracteristica`)
5. Abre un Pull Request

## 📄 Licencia

Este proyecto está bajo la licencia MIT. Ver el archivo [LICENSE](LICENSE) para más detalles.

## 🙏 Créditos

- Construido sobre [Laravel](https://laravel.com)
- Inspirado en principios de Clean Architecture
- Patrón Action-Service para separación de responsabilidades

---

<p align="center">
Hecho con ❤️ para la comunidad Laravel
</p>
