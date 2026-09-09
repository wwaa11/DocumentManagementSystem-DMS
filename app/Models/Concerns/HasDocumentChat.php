<?php

namespace App\Models\Concerns;

trait HasDocumentChat
{
    public function hasActiveChat(): bool
    {
        return filled($this->assigned_user_id)
            && in_array($this->status, ['process', 'pending'], true);
    }

    public function hasChatMessages(): bool
    {
        if ($this->relationLoaded('messages')) {
            return $this->messages->isNotEmpty();
        }

        if (array_key_exists('messages_count', $this->getAttributes())) {
            return (int) $this->messages_count > 0;
        }

        return $this->messages()->exists();
    }

    public function shouldDisplayChat(): bool
    {
        return $this->hasChatMessages() || $this->hasActiveChat();
    }
}
