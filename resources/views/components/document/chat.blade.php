@props([
    'documentType',
    'documentId',
    'title' => null,
    'messagesUrl',
    'storeUrl',
    'showPendingAction' => false,
    'pendingUrl' => null,
])

@php
    $chatId = 'document-chat-' . $documentType . '-' . $documentId;
@endphp

<div {{ $attributes->merge(['class' => 'border-base-200 rounded-xl border']) }} id="{{ $chatId }}" data-document-chat>
  <div class="border-base-200 flex items-center justify-between gap-2 border-b px-4 py-3">
    <div class="flex items-center gap-2">
      <span class="bg-primary/10 text-primary flex h-8 w-8 items-center justify-center rounded-lg">
        <i class="fas fa-comments"></i>
      </span>
      <div>
        <p class="text-sm font-bold">{{ $title ?? 'สนทนากับผู้แจ้ง' }}</p>
        <p class="text-base-content/55 text-xs">แชทและแนบไฟล์ระหว่างผู้แจ้งกับผู้ดำเนินการ</p>
      </div>
    </div>
    @if ($showPendingAction)
      <button class="btn btn-warning btn-sm" id="{{ $chatId }}-pending-btn" onclick="setDocumentPending{{ $documentId }}()" type="button">
        รอข้อมูลจากผู้แจ้ง
      </button>
    @endif
  </div>

  <div class="bg-base-200/30 max-h-80 min-h-48 overflow-y-auto p-4" id="{{ $chatId }}-messages">
    <p class="text-base-content/50 text-center text-sm">กำลังโหลดข้อความ...</p>
  </div>

  <div class="border-base-200 border-t p-4 hidden" id="{{ $chatId }}-readonly">
    <p class="text-base-content/55 text-center text-sm">แชทปิดแล้ว — อ่านประวัติการสนทนาได้อย่างเดียว</p>
  </div>

  <div class="border-base-200 border-t p-4" id="{{ $chatId }}-composer">
    <form class="flex flex-col gap-3" id="{{ $chatId }}-form" onsubmit="return sendDocumentMessage{{ $documentId }}(event)">
      <textarea
        class="textarea textarea-bordered w-full"
        id="{{ $chatId }}-body"
        name="body"
        placeholder="พิมพ์ข้อความ..."
        rows="3"
      ></textarea>
      <input class="file-input file-input-bordered file-input-sm w-full" id="{{ $chatId }}-files" name="attachments[]" type="file" multiple>
      <p class="text-base-content/50 text-xs">แนบไฟล์ได้ไม่เกิน 5 ไฟล์ ขนาดไม่เกิน 10 MB ต่อไฟล์</p>
      <button class="btn btn-primary btn-sm" id="{{ $chatId }}-submit" type="submit">
        <i class="fas fa-paper-plane me-1"></i> ส่งข้อความ
      </button>
    </form>
  </div>
</div>

@push('scripts')
  <script>
    (function() {
      const chatId = @json($chatId);
      const messagesUrl = @json($messagesUrl);
      const storeUrl = @json($storeUrl);
      let messagesEl = null;
      let composerEl = null;
      let readonlyEl = null;
      let bodyEl = null;
      let filesEl = null;
      let pendingBtn = null;
      let refreshTimer = null;

      function resolveElements() {
        messagesEl = document.getElementById(chatId + '-messages');
        composerEl = document.getElementById(chatId + '-composer');
        readonlyEl = document.getElementById(chatId + '-readonly');
        bodyEl = document.getElementById(chatId + '-body');
        filesEl = document.getElementById(chatId + '-files');
        pendingBtn = document.getElementById(chatId + '-pending-btn');

        return Boolean(messagesEl && composerEl && bodyEl && filesEl);
      }

      function escapeHtml(value) {
        return String(value ?? '')
          .replaceAll('&', '&amp;')
          .replaceAll('<', '&lt;')
          .replaceAll('>', '&gt;')
          .replaceAll('"', '&quot;')
          .replaceAll("'", '&#039;');
      }

      function renderMessages(messages) {
        if (!messages.length) {
          messagesEl.innerHTML = '<p class="text-base-content/50 text-center text-sm">ยังไม่มีข้อความ</p>';
          return;
        }

        messagesEl.innerHTML = messages.map((message) => {
          const alignment = message.is_mine ? 'items-end text-right' : 'items-start text-left';
          const bubbleClass = message.is_mine ? 'bg-primary text-primary-content' : 'bg-base-100 border-base-200 border';
          const filesHtml = (message.files || []).map((file) => {
            const viewButton = file.is_viewable
              ? `<button type="button" class="btn btn-xs btn-ghost document-chat-preview" data-url="${escapeHtml(file.show_url)}" data-name="${escapeHtml(file.name)}" data-viewable="${file.is_viewable ? '1' : '0'}">ดู</button>`
              : '';
            return `<div class="mt-1 flex flex-wrap items-center gap-2 text-xs">
              <span>${escapeHtml(file.name)}</span>
              ${viewButton}
              <a class="link link-hover" href="${file.download_url}">ดาวน์โหลด</a>
            </div>`;
          }).join('');

          return `<div class="mb-3 flex flex-col ${alignment}">
            <div class="max-w-[85%] rounded-2xl px-3 py-2 text-sm ${bubbleClass}">
              ${message.body ? `<p class="whitespace-pre-wrap">${escapeHtml(message.body)}</p>` : ''}
              ${filesHtml}
            </div>
            <p class="text-base-content/50 mt-1 text-xs">${escapeHtml(message.user_name)} · ${escapeHtml(message.created_at)}</p>
          </div>`;
        }).join('');

        messagesEl.scrollTop = messagesEl.scrollHeight;

        messagesEl.querySelectorAll('.document-chat-preview').forEach((button) => {
          button.addEventListener('click', () => {
            const url = button.dataset.url;
            const name = button.dataset.name;
            const isPdf = name.toLowerCase().endsWith('.pdf');
            const content = isPdf
              ? `<iframe src="${url}" title="${name}" class="h-[70vh] w-full rounded-md border-0"></iframe>`
              : `<img src="${url}" alt="${name}" class="mx-auto max-h-[70vh] max-w-full rounded-md object-contain" />`;

            Swal.fire({
              title: name,
              html: content,
              width: isPdf ? '80%' : 'auto',
              showCloseButton: true,
              showConfirmButton: false,
            });
          });
        });
      }

      async function loadMessages() {
        if (!messagesEl || !composerEl) {
          return;
        }

        try {
          const response = await window.axios.get(messagesUrl);
          if (response.data.status !== 'success') {
            messagesEl.innerHTML = '<p class="text-error text-center text-sm">ไม่สามารถโหลดข้อความได้</p>';
            return;
          }

          renderMessages(response.data.messages || []);
          const canSend = Boolean(response.data.can_send);
          composerEl.classList.toggle('hidden', !canSend);
          if (readonlyEl) {
            readonlyEl.classList.toggle('hidden', canSend);
          }

          if (pendingBtn) {
            pendingBtn.classList.toggle('hidden', response.data.document_status !== 'process');
          }
        } catch (error) {
          messagesEl.innerHTML = '<p class="text-error text-center text-sm">ไม่สามารถโหลดข้อความได้</p>';
        }
      }

      window['sendDocumentMessage{{ $documentId }}'] = async function(event) {
        event.preventDefault();

        const formData = new FormData();
        const body = bodyEl.value.trim();
        if (body) {
          formData.append('body', body);
        }

        Array.from(filesEl.files || []).forEach((file) => {
          formData.append('attachments[]', file);
        });

        if (!body && (!filesEl.files || filesEl.files.length === 0)) {
          Swal.fire({
            icon: 'warning',
            text: 'กรุณากรอกข้อความหรือแนบไฟล์',
            timer: 2000,
            showConfirmButton: false,
          });
          return false;
        }

        try {
          const response = await window.axios.post(storeUrl, formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
          });

          if (response.data.status === 'success') {
            bodyEl.value = '';
            filesEl.value = '';
            await loadMessages();
          } else {
            Swal.fire({ icon: 'error', text: response.data.message || 'ส่งข้อความไม่สำเร็จ' });
          }
        } catch (error) {
          Swal.fire({
            icon: 'error',
            text: error.response?.data?.message || 'ส่งข้อความไม่สำเร็จ',
          });
        }

        return false;
      };

      @if ($showPendingAction && $pendingUrl)
      window['setDocumentPending{{ $documentId }}'] = async function() {
        const result = await Swal.fire({
          title: 'เปลี่ยนสถานะเป็นรอข้อมูลจากผู้แจ้ง?',
          text: 'เอกสารจะอยู่ในงานของคุณและรอผู้แจ้งตอบกลับผ่านแชท',
          icon: 'question',
          showCancelButton: true,
          confirmButtonText: 'ยืนยัน',
          cancelButtonText: 'ยกเลิก',
          buttonsStyling: false,
          customClass: {
            confirmButton: 'btn btn-warning me-2',
            cancelButton: 'btn btn-ghost',
          },
        });

        if (!result.isConfirmed) {
          return;
        }

        try {
          const response = await window.axios.post(@json($pendingUrl), {
            id: @json($documentId),
            type: @json($documentType),
          });

          if (response.data.status === 'success') {
            Swal.fire({
              icon: 'success',
              text: response.data.message,
              timer: 1500,
              showConfirmButton: false,
            });
            await loadMessages();
          } else {
            Swal.fire({ icon: 'error', text: response.data.message || 'เปลี่ยนสถานะไม่สำเร็จ' });
          }
        } catch (error) {
          Swal.fire({
            icon: 'error',
            text: error.response?.data?.message || 'เปลี่ยนสถานะไม่สำเร็จ',
          });
        }
      };
      @endif

      function startChat() {
        if (!window.axios || !resolveElements()) {
          window.setTimeout(startChat, 50);
          return;
        }

        loadMessages();

        if (refreshTimer) {
          window.clearInterval(refreshTimer);
        }

        refreshTimer = window.setInterval(loadMessages, 15000);
      }

      if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', startChat);
      } else {
        startChat();
      }
    })();
  </script>
@endpush
