<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Actions\V1\ExampleAction;
use Livewire\Component;

class ExampleComponent extends Component
{
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

        // Use the helper method to handle Livewire response
        $data = $result->toLivewire($this);

        if ($result->isSuccess()) {
            $this->reset('form');
            // Additional success handling
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

    public function render()
    {
        return view('livewire.example-component');
    }
}
