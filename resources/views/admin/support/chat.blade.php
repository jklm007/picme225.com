@extends('admin.layout.base')

@section('title', 'Console Support Chauffeurs ')

@section('content')
<style>
    /* Design Premium & Moderne pour le Support Desk */
    .support-container {
        display: flex;
        height: calc(100vh - 150px);
        background: #fbfcfe;
        border-radius: 16px;
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.04);
        border: 1px solid #eef2f6;
        overflow: hidden;
    }

    .support-sidebar {
        width: 320px;
        background: #ffffff;
        border-right: 1px solid #eef2f6;
        display: flex;
        flex-direction: column;
    }

    .sidebar-header {
        padding: 20px;
        border-bottom: 1px solid #eef2f6;
        font-weight: 700;
        font-size: 1.15rem;
        color: #1a1f36;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .sidebar-header i {
        color: #4CAF50;
    }

    .room-list {
        flex: 1;
        overflow-y: auto;
        padding: 10px;
    }

    .room-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px;
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.2s ease-in-out;
        margin-bottom: 6px;
        border: 1px solid transparent;
    }

    .room-item:hover {
        background: #f4f7fa;
    }

    .room-item.active {
        background: #ebf3fc;
        border-color: #3a77ce;
    }

    .room-avatar {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        background: #eef2f6;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        color: #3a77ce;
        overflow: hidden;
        font-size: 1.1rem;
        flex-shrink: 0;
    }

    .room-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .room-info {
        flex: 1;
        min-width: 0;
    }

    .room-name {
        font-weight: 600;
        font-size: 0.95rem;
        color: #1a1f36;
        margin-bottom: 4px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .room-last-msg {
        font-size: 0.8rem;
        color: #697386;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .support-main {
        flex: 1;
        display: flex;
        flex-direction: column;
        background: #fafbfe;
    }

    .chat-header {
        padding: 20px;
        background: #ffffff;
        border-bottom: 1px solid #eef2f6;
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .chat-header-name {
        font-weight: 700;
        font-size: 1.1rem;
        color: #1a1f36;
    }

    .chat-header-status {
        font-size: 0.8rem;
        color: #4CAF50;
        display: flex;
        align-items: center;
        gap: 5px;
        font-weight: 500;
    }

    .chat-header-status::before {
        content: '';
        display: inline-block;
        width: 8px;
        height: 8px;
        background: #4CAF50;
        border-radius: 50%;
    }

    .message-pane {
        flex: 1;
        overflow-y: auto;
        padding: 24px;
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .message-bubble {
        max-width: 65%;
        padding: 12px 16px;
        border-radius: 16px;
        font-size: 0.95rem;
        line-height: 1.5;
        position: relative;
        word-wrap: break-word;
    }

    .message-bubble.driver {
        background: #ffffff;
        color: #1a1f36;
        align-self: flex-start;
        border-bottom-left-radius: 4px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
        border: 1px solid #eef2f6;
    }

    .message-bubble.agent {
        background: #3a77ce;
        color: #ffffff;
        align-self: flex-end;
        border-bottom-right-radius: 4px;
    }

    .message-bubble.ai {
        background: #eef8ff;
        color: #0b2240;
        align-self: flex-end;
        border-bottom-right-radius: 4px;
        border: 1px solid #cce8ff;
    }

    .msg-time {
        display: block;
        font-size: 0.75rem;
        margin-top: 4px;
        text-align: right;
    }

    .message-bubble.driver .msg-time {
        color: #8e8e93;
    }

    .message-bubble.agent .msg-time {
        color: #cce3ff;
    }

    .message-bubble.ai .msg-time {
        color: #697386;
    }

    .chat-input-area {
        padding: 20px;
        background: #ffffff;
        border-top: 1px solid #eef2f6;
        display: flex;
        gap: 12px;
        align-items: center;
    }

    .chat-input {
        flex: 1;
        border: 1px solid #e2e8f0;
        border-radius: 24px;
        padding: 12px 20px;
        font-size: 0.95rem;
        outline: none;
        transition: border-color 0.2s;
    }

    .chat-input:focus {
        border-color: #3a77ce;
    }

    .btn-send {
        background: #3a77ce;
        color: #ffffff;
        border: none;
        border-radius: 50%;
        width: 46px;
        height: 46px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: background 0.2s;
        box-shadow: 0 4px 10px rgba(58, 119, 206, 0.2);
    }

    .btn-send:hover {
        background: #2b61ad;
    }

    .empty-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height: 100%;
        color: #697386;
        gap: 16px;
    }

    .empty-state i {
        font-size: 3.5rem;
        color: #cbd5e1;
    }
</style>

<div class="content-area py-1">
    <div class="container-fluid">
        <div class="box box-block bg-white" style="border-radius: 16px; padding: 24px; border: none;">
            <div class="row mb-1">
                <div class="col-md-12">
                    <h5 class="mb-1">🎧 Console Support Chauffeurs (PicMe AI)</h5>
                    <p class="text-muted">Répondez aux demandes des chauffeurs en direct. Vos réponses écrivent en temps réel sur Firebase et s'affichent sur leurs applications.</p>
                </div>
            </div>

            <div class="support-container">
                <!-- Sidebar Salons -->
                <div class="support-sidebar">
                    <div class="sidebar-header">
                        <i class="fa fa-comments"></i> Discussions actives
                    </div>
                    <div class="room-list" id="room-list">
                        <div class="text-center p-3 text-muted">
                            <i class="fa fa-spinner fa-spin"></i> Chargement des canaux...
                        </div>
                    </div>
                </div>

                <!-- Chat Main Panel -->
                <div class="support-main" id="support-main">
                    <div class="empty-state">
                        <i class="fa fa-commenting-o"></i>
                        <h5>Sélectionnez une discussion pour commencer</h5>
                        <p>Les messages d'assistance des chauffeurs s'afficheront ici en temps réel.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://js.pusher.com/7.0/pusher.min.js"></script>
<script>
    let activeRoomId = null;
    let currentProviderId = null;
    let pusher = null;
    let chatChannel = null;

    // Charger les salons au démarrage
    document.addEventListener("DOMContentLoaded", () => {
        // Initialiser Pusher (Soketi)
        pusher = new Pusher('{{ env("PUSHER_APP_KEY", "app-key") }}', {
            wsHost: window.location.hostname,
            wsPort: {{ env("PUSHER_PORT", 6001) }},
            forceTLS: false,
            disableStats: true,
            cluster: '{{ env("PUSHER_APP_CLUSTER", "mt1") }}'
        });

        fetchRooms();
        setInterval(fetchRooms, 5000); // Rafraîchir les salons toutes les 5s
    });

    function fetchRooms() {
        fetch("{{ route('admin.support.chat.rooms') }}")
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    renderRooms(data.rooms);
                }
            })
            .catch(err => console.error("Error fetching rooms:", err));
    }

    function renderRooms(rooms) {
        const listContainer = document.getElementById("room-list");
        if (rooms.length === 0) {
            listContainer.innerHTML = '<div class="text-center p-3 text-muted">Aucun ticket de support actif.</div>';
            return;
        }

        let html = '';
        rooms.forEach(room => {
            const isActive = room.room_id === activeRoomId ? 'active' : '';
            const initial = room.provider_name.charAt(0).toUpperCase();
            const avatarHtml = room.provider_avatar 
                ? `<img src="${room.provider_avatar}" alt="${room.provider_name}">`
                : initial;
            
            html += `
                <div class="room-item ${isActive}" onclick="selectRoom('${room.room_id}', '${room.provider_name.replace(/'/g, "\\'")}')">
                    <div class="room-avatar">${avatarHtml}</div>
                    <div class="room-info">
                        <div class="room-name">${room.provider_name}</div>
                        <div class="room-last-msg">${room.last_message || 'Nouveau ticket support...'}</div>
                    </div>
                </div>
            `;
        });
        listContainer.innerHTML = html;
    }

    function selectRoom(roomId, providerName) {
        activeRoomId = roomId;
        currentProviderId = roomId.replace('support_driver_', '');
        
        // Mettre à jour l'état visuel actif
        document.querySelectorAll(".room-item").forEach(item => item.classList.remove("active"));
        event.currentTarget.classList.add("active");

        // Construire le panneau de chat
        const mainPanel = document.getElementById("support-main");
        mainPanel.innerHTML = `
            <div class="chat-header">
                <div>
                    <div class="chat-header-name">Assistance : ${providerName}</div>
                    <div class="chat-header-status">Chauffeur en ligne</div>
                </div>
            </div>
            <div class="message-pane" id="message-pane">
                <div class="text-center text-muted"><i class="fa fa-spinner fa-spin"></i> Chargement de la conversation...</div>
            </div>
            <div class="chat-input-area">
                <input type="text" id="chat-message-input" class="chat-input" placeholder="Écrivez votre réponse support ici..." onkeydown="handleInputKey(event)">
                <button class="btn-send" onclick="sendReply()"><i class="fa fa-paper-plane"></i></button>
            </div>
        `;

        // Charger immédiatement les messages
        fetchMessages();

        // Connecter le WebSocket au lieu du polling
        if (chatChannel) {
            pusher.unsubscribe(chatChannel.name);
        }
        chatChannel = pusher.subscribe(`support-chat.${currentProviderId}`);
        chatChannel.bind('NewSupportMessage', function(data) {
            // L'événement broadcast la propriété "message" contenant le modèle SupportMessage
            const msgData = data.message;
            let senderId = "driver_" + currentProviderId;
            if (msgData.sender === 'agent_picme_ai') senderId = 'agent_picme_ai';
            if (msgData.sender === 'agent_admin') senderId = 'agent_admin';

            appendMessage({
                senderId: senderId,
                message: msgData.message,
                timestamp: new Date(msgData.created_at).getTime() || Date.now()
            });
        });
    }

    function appendMessage(msg) {
        const pane = document.getElementById("message-pane");
        if (!pane) return;
        
        // Retirer l'état vide si présent
        const emptyState = pane.querySelector('.text-muted');
        if (emptyState && emptyState.innerText.includes('Aucun message')) {
            emptyState.remove();
        }

        const sender = msg.senderId;
        let bubbleClass = 'driver';
        let senderName = 'Chauffeur';

        if (sender === 'agent_picme_ai') {
            bubbleClass = 'ai';
            senderName = '🤖 PicMe AI';
        } else if (sender.startsWith('agent_') || sender === 'agent_admin') {
            bubbleClass = 'agent';
            senderName = 'Support';
        }

        const timeStr = new Date(msg.timestamp).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

        const html = `
            <div class="message-bubble ${bubbleClass}">
                <div style="font-size: 0.75rem; font-weight: 700; margin-bottom: 2px; opacity: 0.85;">${senderName}</div>
                <div>${msg.message}</div>
                <span class="msg-time">${timeStr}</span>
            </div>
        `;
        
        pane.insertAdjacentHTML('beforeend', html);
        pane.scrollTop = pane.scrollHeight;
    }

    function fetchMessages() {
        if (!activeRoomId) return;
        
        fetch(`{{ url('admin/support/chat/messages') }}/${activeRoomId}`)
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    renderMessages(data.messages);
                }
            })
            .catch(err => console.error("Error fetching messages:", err));
    }

    function renderMessages(messages) {
        const pane = document.getElementById("message-pane");
        if (!pane) return;

        if (messages.length === 0) {
            pane.innerHTML = '<div class="text-center p-3 text-muted">Aucun message dans cette discussion.</div>';
            return;
        }

        const isAtBottom = pane.scrollHeight - pane.scrollTop === pane.clientHeight;

        let html = '';
        messages.forEach(msg => {
            const sender = msg.senderId;
            let bubbleClass = 'driver';
            let senderName = 'Chauffeur';

            if (sender === 'agent_picme_ai') {
                bubbleClass = 'ai';
                senderName = '🤖 PicMe AI';
            } else if (sender.startsWith('agent_') || sender === 'agent_admin') {
                bubbleClass = 'agent';
                senderName = 'Support';
            }

            const timeStr = new Date(msg.timestamp).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

            html += `
                <div class="message-bubble ${bubbleClass}">
                    <div style="font-size: 0.75rem; font-weight: 700; margin-bottom: 2px; opacity: 0.85;">${senderName}</div>
                    <div>${msg.message}</div>
                    <span class="msg-time">${timeStr}</span>
                </div>
            `;
        });

        pane.innerHTML = html;

        // Scroller vers le bas si l'utilisateur était déjà en bas ou s'il s'agit du premier chargement
        pane.scrollTop = pane.scrollHeight;
    }

    function handleInputKey(e) {
        if (e.key === "Enter") {
            sendReply();
        }
    }

    function sendReply() {
        const input = document.getElementById("chat-message-input");
        if (!input) return;

        const text = input.value.trim();
        if (!text || !activeRoomId) return;

        input.value = ''; // Effacer immédiatement
        
        fetch(`{{ url('admin/support/chat/reply') }}/${activeRoomId}`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({ message: text })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // Pusher va recevoir le message, pas besoin de fetchMessages()
                // fetchMessages(); 
            } else {
                alert("Erreur d'envoi du message : " + data.error);
                input.value = text; // Restaurer en cas d'erreur
            }
        })
        .catch(err => {
            console.error("Error sending reply:", err);
            alert("Erreur réseau");
            input.value = text;
        });
    }
</script>
@endsection
