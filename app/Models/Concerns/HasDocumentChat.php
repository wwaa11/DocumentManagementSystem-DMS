<?php

namespace App\Models\Concerns;

trait HasDocumentChat
{
    public function hasActiveChat(): bool
    {
        return filled($this->assigned_user_id)
            && in_array($this->status, ['process', 'pending'], true);
    }

    public function shouldDisplayChat(): bool
    {
        if ($this->relationLoaded('messages')) {
            return $this->messages->isNotEmpty() || $this->hasActiveChat();
        }

        return $this->messages()->exists() || $this->hasActiveChat();
    }
}
