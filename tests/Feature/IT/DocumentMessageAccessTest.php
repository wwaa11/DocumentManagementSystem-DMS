<?php

namespace Tests\Feature\IT;

use App\Models\DocumentIT;
use App\Models\User;
use App\Services\IT\DocumentMessageService;
use Tests\TestCase;

class DocumentMessageAccessTest extends TestCase
{
    private function makeUser(array $attributes = []): User
    {
        return new User(array_merge([
            'userid' => '650099',
            'name' => 'Viewer',
            'position' => 'Staff',
            'department' => 'แผนก A',
            'division' => 'A',
            'email' => 'viewer@example.com',
            'role' => 'user',
        ], $attributes));
    }

    public function test_department_viewer_can_read_chat_when_they_can_access_document(): void
    {
        $viewer = $this->makeUser([
            'userid' => '650017',
            'can_view_department_documents' => true,
            'view_departments' => ['แผนก B'],
        ]);

        $document = new DocumentIT([
            'requester' => '570075',
            'assigned_user_id' => '440106',
            'status' => 'process',
        ]);
        $document->setRelation('creator', new User([
            'userid' => '570075',
            'department' => 'แผนก B',
        ]));

        $service = new DocumentMessageService;

        $this->assertTrue($viewer->canAccessDocument($document));
        $this->assertTrue($service->canAccessChat($document, $viewer));
        $this->assertTrue($service->canSendMessage($document, $viewer));
    }

    public function test_department_viewer_cannot_send_when_document_is_complete(): void
    {
        $viewer = $this->makeUser([
            'userid' => '650017',
            'can_view_department_documents' => true,
            'view_departments' => ['แผนก B'],
        ]);

        $document = new DocumentIT([
            'requester' => '570075',
            'assigned_user_id' => '440106',
            'status' => 'complete',
        ]);
        $document->setRelation('creator', new User([
            'userid' => '570075',
            'department' => 'แผนก B',
        ]));

        $service = new DocumentMessageService;

        $this->assertTrue($service->canAccessChat($document, $viewer));
        $this->assertFalse($service->canSendMessage($document, $viewer));
    }

    public function test_stranger_cannot_read_chat(): void
    {
        $stranger = $this->makeUser(['userid' => '999999']);

        $document = new DocumentIT([
            'requester' => '570075',
            'assigned_user_id' => '440106',
            'status' => 'process',
        ]);

        $service = new DocumentMessageService;

        $this->assertFalse($stranger->canAccessDocument($document));
        $this->assertFalse($service->canAccessChat($document, $stranger));
    }

    public function test_assigned_technician_can_still_send_messages(): void
    {
        $technician = $this->makeUser(['userid' => '440106']);

        $document = new DocumentIT([
            'requester' => '570075',
            'assigned_user_id' => '440106',
            'status' => 'process',
        ]);

        $service = new DocumentMessageService;

        $this->assertTrue($service->canAccessChat($document, $technician));
        $this->assertTrue($service->canSendMessage($document, $technician));
    }
}
