<?php

namespace App\Services\IT;

use App\Models\DocumentIT;
use App\Models\DocumentItUser;
use App\Models\DocumentMessage;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;

class DocumentMessageService
{
    /**
     * @return array{document: Model, type: string}|null
     */
    public function resolveDocument(string $type, int|string $documentId): ?array
    {
        return match ($type) {
            'IT' => ($document = DocumentIT::find($documentId)) ? ['document' => $document, 'type' => 'IT'] : null,
            'USER' => ($document = DocumentItUser::find($documentId)) ? ['document' => $document, 'type' => 'USER'] : null,
            default => null,
        };
    }

    public function canAccessChat(Model $document, User $user): bool
    {
        if (! $this->supportsChat($document)) {
            return false;
        }

        $requesterId = $this->requesterId($document);
        $assignedUserId = $document->assigned_user_id;

        if ($this->hasChatHistory($document)) {
            if ($requesterId === $user->userid) {
                return true;
            }

            if ($document->messages()->where('userid', $user->userid)->exists()) {
                return true;
            }

            return in_array($user->role, ['admin', 'it', 'it-hardware', 'it-approve', 'it-hardware-approve'], true);
        }

        if ($requesterId === $user->userid) {
            return filled($assignedUserId);
        }

        if ($assignedUserId === $user->userid) {
            return true;
        }

        return in_array($user->role, ['admin', 'it', 'it-hardware', 'it-approve', 'it-hardware-approve'], true);
    }

    public function canSendMessage(Model $document, User $user): bool
    {
        if (! $this->canAccessChat($document, $user)) {
            return false;
        }

        return in_array($document->status, ['process', 'pending'], true)
            && filled($document->assigned_user_id);
    }

    public function supportsChat(Model $document): bool
    {
        return $document instanceof DocumentIT || $document instanceof DocumentItUser;
    }

    public function getMessages(string $type, int|string $documentId): JsonResponse
    {
        $resolved = $this->resolveDocument($type, $documentId);
        if (! $resolved) {
            return response()->json(['status' => 'error', 'message' => 'ไม่พบเอกสาร!'], 404);
        }

        $document = $resolved['document'];
        if (! $this->canAccessChat($document, auth()->user())) {
            return response()->json(['status' => 'error', 'message' => 'ไม่มีสิทธิ์เข้าถึงข้อความ!'], 403);
        }

        $messages = $document->messages()
            ->with(['user', 'files'])
            ->orderBy('created_at')
            ->get()
            ->map(fn (DocumentMessage $message) => $this->formatMessage($message));

        return response()->json([
            'status' => 'success',
            'messages' => $messages,
            'can_send' => $this->canSendMessage($document, auth()->user()),
            'document_status' => $document->status,
        ]);
    }

    public function storeMessage(Request $request, string $type, int|string $documentId): JsonResponse
    {
        $resolved = $this->resolveDocument($type, $documentId);
        if (! $resolved) {
            return response()->json(['status' => 'error', 'message' => 'ไม่พบเอกสาร!'], 404);
        }

        $document = $resolved['document'];
        $user = auth()->user();

        if (! $this->canSendMessage($document, $user)) {
            return response()->json(['status' => 'error', 'message' => 'ไม่สามารถส่งข้อความได้!'], 403);
        }

        $body = trim((string) $request->input('body', ''));
        $uploadedFiles = $request->file('attachments', []);

        if ($body === '' && empty($uploadedFiles)) {
            return response()->json(['status' => 'error', 'message' => 'กรุณากรอกข้อความหรือแนบไฟล์!'], 422);
        }

        $message = $document->messages()->create([
            'userid' => $user->userid,
            'body' => $body !== '' ? $body : null,
        ]);

        foreach ($uploadedFiles as $file) {
            $this->storeAttachment($message, $file);
        }

        if ($this->requesterId($document) === $user->userid && $document->status === 'pending') {
            $document->status = 'process';
            $document->save();

            $document->logs()->create([
                'userid' => $user->userid,
                'action' => 'message',
                'details' => 'ผู้แจ้งตอบกลับข้อความ',
            ]);
        }

        $message->load(['user', 'files']);

        return response()->json([
            'status' => 'success',
            'message' => 'ส่งข้อความสำเร็จ!',
            'data' => $this->formatMessage($message),
            'document_status' => $document->fresh()->status,
        ]);
    }

    public function setPending(Request $request): JsonResponse
    {
        $resolved = $this->resolveDocument($request->string('type')->toString(), $request->input('id'));
        if (! $resolved) {
            return response()->json(['status' => 'error', 'message' => 'ไม่พบเอกสาร!'], 404);
        }

        $document = $resolved['document'];
        $user = auth()->user();

        if ($document->status !== 'process' || $document->assigned_user_id !== $user->userid) {
            return response()->json([
                'status' => 'error',
                'message' => 'เอกสารนี้ไม่สามารถเปลี่ยนสถานะได้!',
            ], 422);
        }

        $document->status = 'pending';
        $document->save();

        $document->logs()->create([
            'userid' => $user->userid,
            'action' => 'pending',
            'details' => 'รอข้อมูลเพิ่มเติมจากผู้แจ้ง',
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'เปลี่ยนสถานะเป็นรอข้อมูลจากผู้แจ้งแล้ว!',
            'document_status' => $document->status,
        ]);
    }

    /**
     * @return Collection<int, Model>
     */
    public function chatEnabledDocuments(Model $document, string $type): Collection
    {
        if ($type === 'IT' && $document instanceof DocumentIT) {
            return $this->isChatVisible($document) ? collect([$document]) : collect();
        }

        if ($type === 'USER' && method_exists($document, 'getAllDocuments')) {
            return $document->getAllDocuments()->filter(fn (Model $subDocument) => $this->isChatVisible($subDocument));
        }

        if ($this->isChatVisible($document)) {
            return collect([$document]);
        }

        return collect();
    }

    public function isChatVisible(Model $document): bool
    {
        if (! $this->supportsChat($document)) {
            return false;
        }

        if (method_exists($document, 'shouldDisplayChat')) {
            return $document->shouldDisplayChat();
        }

        return $this->hasChatHistory($document)
            || (filled($document->assigned_user_id) && in_array($document->status, ['process', 'pending'], true));
    }

    private function hasChatHistory(Model $document): bool
    {
        if ($document->relationLoaded('messages')) {
            return $document->messages->isNotEmpty();
        }

        return $document->messages()->exists();
    }

    /**
     * @return array<string, mixed>
     */
    private function formatMessage(DocumentMessage $message): array
    {
        return [
            'id' => $message->id,
            'body' => $message->body,
            'userid' => $message->userid,
            'user_name' => $message->user->name ?? $message->userid,
            'created_at' => $message->created_at?->format('d/m/Y H:i'),
            'is_mine' => $message->userid === auth()->user()->userid,
            'files' => $message->files->map(fn ($file) => [
                'id' => $file->id,
                'name' => $file->original_filename,
                'size' => $file->size,
                'is_viewable' => $file->isViewable(),
                'show_url' => route('document.files.show', $file->id),
                'download_url' => route('document.files.download', $file->id),
            ])->values()->all(),
        ];
    }

    private function storeAttachment(DocumentMessage $message, UploadedFile $file): void
    {
        $storedPath = $file->store('uploads/messages', 'public');

        $message->files()->create([
            'original_filename' => $file->getClientOriginalName(),
            'stored_path' => $storedPath,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
        ]);
    }

    private function requesterId(Model $document): ?string
    {
        if ($document instanceof DocumentIT) {
            return $document->requester;
        }

        if ($document instanceof DocumentItUser) {
            return $document->documentUser?->requester;
        }

        return null;
    }
}
