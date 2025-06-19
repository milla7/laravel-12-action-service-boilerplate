<?php

declare(strict_types=1);

namespace App\Livewire\Concerns;

use App\Support\ActionResult;

trait HandlesActionResults
{
    /**
     * Handle ActionResult in Livewire component
     */
    protected function handleActionResult(ActionResult $result, array $options = []): mixed
    {
        if ($result->isSuccess()) {
            // Dispatch success event
            $this->dispatch('success', $result->getMessage());

            // Reset form if specified
            if ($options['reset_form'] ?? false) {
                $this->reset($options['form_property'] ?? 'form');
            }

            // Execute success callback if provided
            if (isset($options['on_success']) && is_callable($options['on_success'])) {
                $options['on_success']($result->getData());
            }

            return $result->getData();
        } else {
            // Dispatch error event
            $this->dispatch('error', $result->getMessage());

            // Add validation errors
            foreach ($result->getErrors() as $field => $messages) {
                $this->addError($field, is_array($messages) ? $messages[0] : $messages);
            }

            // Execute error callback if provided
            if (isset($options['on_error']) && is_callable($options['on_error'])) {
                $options['on_error']($result);
            }

            return null;
        }
    }

    /**
     * Simple success/error handling without callbacks
     */
    protected function handleActionResultSimple(ActionResult $result, bool $resetForm = true): mixed
    {
        return $this->handleActionResult($result, [
            'reset_form' => $resetForm,
            'form_property' => 'form'
        ]);
    }
}
