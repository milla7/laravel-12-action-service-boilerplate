<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Actions\V1\ExampleAction;
use Livewire\Component;

class ExampleComponent extends Component
{
    use HandlesActionResults;
    public array $form = [];
    public bool $loading = false;

    public function __construct(
        private ExampleAction $exampleAction
    ) {}

    /**
     * Handle form submission using Action
     */
    public function save()
    {
        $this->loading = true;

        $result = $this->exampleAction->execute($this->form);

        // Handle the result manually with full control
        if ($result->isSuccess()) {
            $this->dispatch('success', $result->getMessage());
            $this->reset('form');
            // Handle success data
            $data = $result->getData();
        } else {
            $this->dispatch('error', $result->getMessage());
            // Handle validation errors
            foreach ($result->getErrors() as $field => $messages) {
                $this->addError($field, is_array($messages) ? $messages[0] : $messages);
            }
        }

        $this->loading = false;
    }

    /**
     * Alternative approach - manual handling
     */
    public function saveManual()
    {
        $this->loading = true;

        $result = $this->exampleAction->execute($this->form);

        if ($result->isSuccess()) {
            $this->dispatch('success', $result->getMessage());
            $this->reset('form');
            // Handle success data
            $data = $result->getData();
        } else {
            $this->dispatch('error', $result->getMessage());
            // Handle validation errors
            foreach ($result->getErrors() as $field => $messages) {
                $this->addError($field, is_array($messages) ? $messages[0] : $messages);
            }
        }

        $this->loading = false;
    }

    /**
     * Example using the trait - simple version
     */
    public function saveWithTrait()
    {
        $this->loading = true;

        $result = $this->exampleAction->execute($this->form);

        // Simple handling with trait
        $data = $this->handleActionResultSimple($result);

        $this->loading = false;
    }

    /**
     * Example using the trait - advanced version with callbacks
     */
    public function saveAdvanced()
    {
        $this->loading = true;

        $result = $this->exampleAction->execute($this->form);

        $data = $this->handleActionResult($result, [
            'reset_form' => true,
            'form_property' => 'form',
            'on_success' => function ($data) {
                // Custom success logic
                $this->dispatch('model-updated', modelId: $data->id ?? null);
            },
            'on_error' => function ($result) {
                // Custom error handling
                logger()->error('Action failed', $result->toArray());
            }
        ]);

        $this->loading = false;
    }

    public function render()
    {
        return view('livewire.example-component');
    }
}
