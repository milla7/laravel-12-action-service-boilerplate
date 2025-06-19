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
        
        $user = $result->toLivewire($this);
        // Automáticamente maneja dispatch de eventos y errores
        
        if ($result->isSuccess()) {
            $this->reset('form');
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
$result->toLivewire($component) // Maneja Livewire automáticamente
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

### **Livewire con Validación:**

```php
// En Livewire Component
public function save()
{
    $result = $this->createUserAction->execute($this->form);
    
    if ($result->isError()) {
        // Los errores se agregan automáticamente con toLivewire()
        $result->toLivewire($this);
        return;
    }
    
    $user = $result->getData();
    $this->dispatch('user-created', userId: $user->id);
    $this->reset('form');
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

## ✅ Ventajas

1. **Una sola Action** para API y Livewire
2. **Respuestas consistentes** en toda la aplicación
3. **Manejo unificado de errores**
4. **Fácil testing** con `toArray()`
5. **Códigos HTTP apropiados** automáticamente
6. **Menos duplicación** de código

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
