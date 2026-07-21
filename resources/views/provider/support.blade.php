@extends('provider.layout.app')

@section('title', 'Assistance Chat - ')

@section('styles')
<style>
    .chat-container {
        max-width: 700px;
        margin: 80px auto 20px;
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        display: flex;
        flex-direction: column;
        height: 70vh;
        border: 1px solid #eef0f5;
        overflow: hidden;
    }
    .chat-header {
        background: #111111;
        color: #ffffff;
        padding: 15px 20px;
        display: flex;
        align-items: center;
        gap: 12px;
        border-bottom: 1px solid #222;
    }
    .chat-header h4 {
        margin: 0;
        font-weight: 600;
        font-size: 16px;
    }
    .chat-messages {
        flex: 1;
        padding: 20px;
        overflow-y: auto;
        background: #f8f9fa;
        display: flex;
        flex-direction: column;
        gap: 15px;
    }
    .message-bubble {
        max-width: 75%;
        padding: 12px 16px;
        border-radius: 16px;
        font-size: 14px;
        line-height: 1.4;
        word-wrap: break-word;
        position: relative;
    }
    .message-bubble.driver {
        background: #2ecc71;
        color: #ffffff;
        align-self: flex-end;
        border-bottom-right-radius: 2px;
    }
    .message-bubble.support {
        background: #ffffff;
        color: #333333;
        align-self: flex-start;
        border-bottom-left-radius: 2px;
        border: 1px solid #eef0f5;
        box-shadow: 0 2px 5px rgba(0,0,0,0.02);
    }
    .message-time {
        font-size: 10px;
        opacity: 0.7;
        margin-top: 5px;
        text-align: right;
    }
    .chat-input-area {
        padding: 15px;
        background: #ffffff;
        border-top: 1px solid #eef0f5;
        display: flex;
        gap: 10px;
    }
    .chat-input {
        flex: 1;
        border: 1px solid #ced4da;
        border-radius: 30px;
        padding: 10px 20px;
        outline: none;
        font-size: 14px;
        transition: border 0.2s;
    }
    .chat-input:focus {
        border-color: #2ecc71;
    }
    .chat-send-btn {
        background: #2ecc71;
        color: #ffffff;
        border: none;
        border-radius: 50%;
        width: 42px;
        height: 42px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: background 0.2s;
    }
    .chat-send-btn:hover {
        background: #27ae60;
    }
</style>
@endsection

@section('content')
<div class="chat-container">
    <div class="chat-header">
        <div style="background: #2ecc71; width: 10px; height: 10px; border-radius: 50%;"></div>
        <h4>Assistance Picme (IA Support)</h4>
    </div>
    
    <div class="chat-messages" id="chat-box">
        <div class="message-bubble support">
            Bonjour ! Comment puis-je vous aider aujourd'hui ?
            <div class="message-time">A l'instant</div>
        </div>
    </div>
    
    <div class="chat-input-area">
        <input type="text" id="message-input" class="chat-input" placeholder="Écrivez un message..." autocomplete="off">
        <button id="send-btn" class="chat-send-btn">
            <i class="fa fa-paper-plane"></i>
        </button>
    </div>
</div>
@endsection

@section('scripts')
<script>
    var lastMsgCount = 0;
    
    function loadMessages() {
        $.ajax({
            url: "{{ route('provider.support.chat.history') }}",
            type: "GET",
            dataType: "json",
            success: function(response) {
                if (response.status === 'success' && response.data) {
                    var html = '';
                    html += '<div class="message-bubble support">Bonjour ! Comment puis-je vous aider aujourd'hui ?<div class="message-time">System</div></div>';
                    
                    response.data.forEach(function(msg) {
                        var isDriver = msg.sender === 'driver';
                        var senderClass = isDriver ? 'driver' : 'support';
                        var dateStr = new Date(msg.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                        
                        html += '<div class="message-bubble ' + senderClass + '">';
                        html += msg.message;
                        html += '<div class="message-time">' + dateStr + '</div>';
                        html += '</div>';
                    });
                    
                    $('#chat-box').html(html);
                    
                    if (response.data.length > lastMsgCount) {
                        lastMsgCount = response.data.length;
                        var box = document.getElementById('chat-box');
                        box.scrollTop = box.scrollHeight;
                    }
                }
            }
        });
    }

    $(document).ready(function() {
        loadMessages();
        setInterval(loadMessages, 4000);
        
        $('#send-btn').click(sendMessage);
        $('#message-input').keypress(function(e) {
            if(e.which == 13) {
                sendMessage();
            }
        });
    });

    function sendMessage() {
        var input = $('#message-input');
        var msg = input.val().trim();
        if (msg === '') return;
        
        input.val('');
        
        var tempHtml = '<div class="message-bubble driver">' + msg + '<div class="message-time">Envoi...</div></div>';
        $('#chat-box').append(tempHtml);
        var box = document.getElementById('chat-box');
        box.scrollTop = box.scrollHeight;
        
        $.ajax({
            url: "{{ route('provider.support.chat.send') }}",
            type: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                message: msg
            },
            dataType: "json",
            success: function(response) {
                loadMessages();
            },
            error: function() {
                alert('Erreur lors de l'envoi du message.');
            }
        });
    }
</script>
@endsection
