# Action Result Pattern - Documentación

## 🎯 Objetivo

El **ActionResult Pattern** permite que las mismas Actions sirvan tanto para **APIs** como para **Livewire**, manteniendo consistencia y reduciendo duplicación de código.

## 📁 Estructura

```
app/
├── Support/
│   └── ActionResult.php          # Clase principal
├── Actions/V1/
│   ├── Action.php               # Clase base con helpers
│   └── ExampleAction.php        # Action de ejemplo
├── Http/Controllers/Api/
│   └── ExampleController.php    # Uso en API
└── Livewire/
    └── ExampleComponent.php     # Uso en Livewire
```

## 🚀 Uso Básico

### **En Actions:**

```php
<?php

class CreateUserAction extends Action
{
    public function execute($data): ActionResult
    {
        $this->validatePermissions(['users.create']);
        
        $validated = $this->validateData($data, [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
        ]);

        return DB::transaction(function () use ($validated) {
            $user = User::create($validated);

            return $this->successResult(
                data: $user,
                message: 'Usuario creado exitosamente',
                statusCode: 201
            );
        });
    }
}
```

### **En Controllers API:**

```php
<?php

class UserController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $result = $this->createUserAction->execute($request->all());
        
        return $result->toApiResponse();
        // Retorna: {"success": true, "data": {...}, "message": "...", "errors": []}
    }
}
```

### **En Livewire:**

```php
<?php

class CreateUserComponent extends Component
{
    public function save()
    {
        $result = $this->createUserAction->execute($this->form);
        
        // Manejo manual con control total
        if ($result->isSuccess()) {
            $this->dispatch('success', $result->getMessage());
            $this->reset('form');
            $user = $result->getData();
        } else {
            $this->dispatch('error', $result->getMessage());
            foreach ($result->getErrors() as $field => $messages) {
                $this->addError($field, is_array($messages) ? $messages[0] : $messages);
            }
        }
    }
}
```

## 🔧 ActionResult API

### **Métodos Estáticos:**

```php
// Éxito
ActionResult::success($data, $message, $statusCode)

// Error general
ActionResult::error($message, $errors, $statusCode, $data)

// Error de validación
ActionResult::validationError($errors, $message)
```

### **Métodos de Conversión:**

```php
$result->toApiResponse()      // JsonResponse para APIs
$result->toLivewireData()     // Array para componentes Livewire
$result->toFlashData()        // Array para session flash
$result->toArray()            // Array simple (testing)
```

### **Métodos de Verificación:**

```php
$result->isSuccess()     // bool
$result->isError()       // bool
$result->getData()       // mixed
$result->getMessage()    // string
$result->getErrors()     // array
$result->getStatusCode() // int
```

## 📝 Ejemplos Avanzados

### **Manejo de Errores:**

```php
public function execute($data): ActionResult
{
    try {
        // ... lógica
        return $this->successResult($result);
        
    } catch (ValidationException $e) {
        return $this->validationErrorResult($e->errors());
        
    } catch (\Exception $e) {
        Log::error('Error en CreateUserAction', [
            'error' => $e->getMessage(),
            'data' => $data
        ]);
        
        return $this->errorResult(
            message: 'Error interno del servidor',
            statusCode: 500
        );
    }
}
```

### **API con Status Personalizados:**

```php
// En Action
return $this->successResult(
    data: $user,
    message: 'Usuario creado',
    statusCode: 201
);

// En Controller
public function store(Request $request): JsonResponse
{
    $result = $this->action->execute($request->all());
    
    // Respuesta automática con status correcto
    return $result->toApiResponse();
    // HTTP 201 + JSON response
}
```

### **Livewire con Trait:**

```php
// En Livewire Component
use App\Livewire\Concerns\HandlesActionResults;

class CreateUserComponent extends Component
{
    use HandlesActionResults;
    
    public function save()
    {
        $result = $this->createUserAction->execute($this->form);
        
        // Manejo simple con trait
        $user = $this->handleActionResultSimple($result);
        
        if ($user) {
            $this->dispatch('user-created', userId: $user->id);
        }
    }
    
    public function saveAdvanced()
    {
        $result = $this->createUserAction->execute($this->form);
        
        $user = $this->handleActionResult($result, [
            'reset_form' => true,
            'on_success' => function ($user) {
                $this->dispatch('user-created', userId: $user->id);
            }
        ]);
    }
}
```

## 🎨 Helpers en Action Base

La clase base `Action` incluye helpers para simplificar el uso:

```php
// En lugar de ActionResult::success()
return $this->successResult($data, $message, $statusCode);

// En lugar de ActionResult::error()
return $this->errorResult($message, $errors, $statusCode);

// En lugar de ActionResult::validationError()
return $this->validationErrorResult($errors, $message);
```

## 🎨 Trait HandlesActionResults

Para evitar repetir código en componentes Livewire, usa el trait:

```php
<?php

namespace App\Livewire\Concerns;

trait HandlesActionResults
{
    // Manejo simple
    protected function handleActionResultSimple(ActionResult $result, bool $resetForm = true): mixed

    // Manejo avanzado con callbacks
    protected function handleActionResult(ActionResult $result, array $options = []): mixed
}
```

### **Opciones disponibles:**

- `reset_form` - Resetear formulario en éxito
- `form_property` - Nombre de la propiedad del formulario
- `on_success` - Callback ejecutado en éxito
- `on_error` - Callback ejecutado en error

## ✅ Ventajas

1. **Una sola Action** para API y Livewire
2. **Respuestas consistentes** en toda la aplicación
3. **Manejo unificado de errores**
4. **Sin acoplamiento** entre ActionResult y Livewire
5. **Fácil testing** con `toArray()`
6. **Códigos HTTP apropiados** automáticamente
7. **Menos duplicación** de código
8. **Control total** del componente sobre el manejo

## 🧪 Testing

```php
public function test_create_user_success()
{
    $result = $this->action->execute([
        'name' => 'John Doe',
        'email' => 'john@example.com'
    ]);
    
    $this->assertTrue($result->isSuccess());
    $this->assertEquals(201, $result->getStatusCode());
    $this->assertArrayHasKey('id', $result->getData());
}

public function test_create_user_validation_error()
{
    $result = $this->action->execute([]);
    
    $this->assertTrue($result->isError());
    $this->assertEquals(422, $result->getStatusCode());
    $this->assertArrayHasKey('name', $result->getErrors());
}
``` 
