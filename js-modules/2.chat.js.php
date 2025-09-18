// --- 2. CHAT & AUDIO MODULE ---
toggleChatSidebar() {
    if (window.innerWidth < 768) { 
        this.elements.chatHistorySidebar.classList.toggle('translate-x-full');
        if (this.elements.chatSidebarOverlay) this.elements.chatSidebarOverlay.classList.toggle('hidden');
    } else { 
        this.elements.chatHistorySidebar.classList.toggle('md:hidden');
    }
},
bindChatEvents() {
    if(this.elements.sendBtn) this.elements.sendBtn.addEventListener('click', () => {
        if (this.state.ui.isGeneratingResponse) {
            this.handleStopGenerating();
        } else {
            this.handleChatSend();
        }
    });
    if(this.elements.chatInput) this.elements.chatInput.addEventListener('keypress', (e) => { if (e.key === 'Enter' && !this.state.ui.isGeneratingResponse) this.handleChatSend(); });
    if(this.elements.chatSidebarToggle) this.elements.chatSidebarToggle.addEventListener('click', () => this.toggleChatSidebar());
    if(this.elements.chatSidebarOverlay) this.elements.chatSidebarOverlay.addEventListener('click', () => this.toggleChatSidebar());

    if (this.elements.voiceInputBtn) {
        this.elements.voiceInputBtn.addEventListener('click', () => {
            this.handleStartRecording();
        });
    }
    if(this.elements.cancelRecordingBtn) this.elements.cancelRecordingBtn.addEventListener('click', () => this.handleCancelRecording());
    if(this.elements.sendRecordingBtn) this.elements.sendRecordingBtn.addEventListener('click', () => this.handleSendRecording());


    if(this.elements.imageInput) this.elements.imageInput.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = (event) => {
                this.state.ui.chatImageBase64 = event.target.result;
                this.elements.imagePreview.src = event.target.result;
                this.elements.imagePreviewContainer.classList.remove('hidden');
            };
            reader.readAsDataURL(file);
        }
    });

    if(this.elements.removeImageBtn) this.elements.removeImageBtn.addEventListener('click', () => {
        this.state.ui.chatImageBase64 = null;
        this.elements.imageInput.value = '';
        this.elements.imagePreview.src = '';
        this.elements.imagePreviewContainer.classList.add('hidden');
    });
    
    if(this.elements.newChatBtn) this.elements.newChatBtn.addEventListener('click', () => {
        this.state.chat.activeSessionId = null;
        this.state.chat.messages = [];
        this.render.activeChatMessages();
        this.render.chatSessionsList();
    });
    
    if(this.elements.chatHistoryList) this.elements.chatHistoryList.addEventListener('click', (e) => {
        const sessionItem = e.target.closest('.chat-session-item');
        const menuBtn = e.target.closest('.session-menu-btn');
        const renameBtn = e.target.closest('.rename-session-btn');
        const deleteBtn = e.target.closest('.delete-session-btn');

        if (menuBtn) {
            e.stopPropagation();
            const menu = menuBtn.nextElementSibling;
            document.querySelectorAll('.session-menu').forEach(m => {
                if (m !== menu) m.classList.add('hidden');
            });
            menu.classList.toggle('hidden');
            return;
        }

        if (renameBtn) {
            e.stopPropagation();
            const sessionId = renameBtn.dataset.sessionId;
            const session = this.state.chat.sessions.find(s => s.id == sessionId);
            this.modals.showRenameSession(session);
            return;
        }

        if (deleteBtn) {
            e.stopPropagation();
            const sessionId = deleteBtn.dataset.sessionId;
            this.modals.showConfirm('حذف المحادثة', 'هل أنت متأكد من حذف هذه المحادثة بالكامل؟', () => this.deleteChatSession(sessionId));
            return;
        }

        if (sessionItem) {
            const sessionId = sessionItem.dataset.sessionId;
            this.loadChatMessages(sessionId);
            if (window.innerWidth < 768) {
                this.toggleChatSidebar();
            }
        }
    });
    
    this.elements.chatWindow.addEventListener('click', (e) => {
        const editBtn = e.target.closest('.edit-user-msg-btn');
        const copyBtn = e.target.closest('.copy-ai-msg-btn');
        const regenerateBtn = e.target.closest('.regenerate-ai-msg-btn');
        const readAloudBtn = e.target.closest('.read-aloud-btn');

        if(editBtn) {
            const messageIndex = parseInt(editBtn.dataset.messageIndex, 10);
            const message = this.state.chat.messages[messageIndex];
            if (message) {
                this.elements.chatInput.value = message.content;
                this.elements.chatInput.focus();
            }
        }
        if(copyBtn) {
            const messageIndex = parseInt(copyBtn.dataset.messageIndex, 10);
            const message = this.state.chat.messages[messageIndex];
            if (message) {
                navigator.clipboard.writeText(message.content).then(() => {
                    this.utils.showMessageBox('تم نسخ الرد!', 'success');
                });
            }
        }
        if(regenerateBtn) {
            const messageIndex = parseInt(regenerateBtn.dataset.messageIndex, 10);
            const userMessage = this.state.chat.messages[messageIndex - 1];
            if (userMessage && userMessage.role === 'user') {
                this.state.chat.messages.splice(messageIndex);
                this.handleChatSend(userMessage.content, userMessage.image_base64, userMessage.audio_path);
            }
        }
        if(readAloudBtn) {
            const messageIndex = parseInt(readAloudBtn.dataset.messageIndex, 10);
            const message = this.state.chat.messages[messageIndex];
            if (message && message.content) {
               this.handleReadAloud(message.content, readAloudBtn);
            }
        }
    });
},
async handleChatSend(overrideMessage = null, overrideImage = null) {
    const userMessage = overrideMessage ?? this.elements.chatInput.value.trim();
    const imageBase64 = overrideImage ?? this.state.ui.chatImageBase64;

    if ((!userMessage && !imageBase64) || this.state.ui.isGeneratingResponse) {
        return;
    }
    
    let currentSessionId = this.state.chat.activeSessionId;
    if (!currentSessionId) {
        const newSession = await this.startNewChatSession(userMessage || "محادثة جديدة");
        if (newSession) {
            currentSessionId = newSession.id;
        } else {
            this.utils.showMessageBox("لا يمكن بدء محادثة جديدة.", "error");
            return;
        }
    }
    
    if(overrideMessage === null) {
        const userMessageData = { role: 'user', content: userMessage, image_base64: imageBase64 };
        this.state.chat.messages.push(userMessageData);
        this.render.activeChatMessages();
    }
    
    this.elements.chatInput.value = '';
    this.elements.removeImageBtn.click();
    
    this.state.ui.isGeneratingResponse = true;
    this.elements.sendBtn.classList.remove('bg-sky-500', 'hover:bg-sky-600');
    this.elements.sendBtn.classList.add('bg-rose-500', 'hover:bg-rose-600');
    this.elements.sendBtnContent.innerHTML = '<i class="fas fa-stop mr-2"></i> إيقاف';


    const modelMessagePlaceholder = { role: 'model', content: '', isTyping: true };
    this.state.chat.messages.push(modelMessagePlaceholder);
    this.render.activeChatMessages();

    try {
        const result = await this.api('chat', 'POST', { 
            action: 'generate_response',
            session_id: currentSessionId,
            prompt: userMessage, 
            image: imageBase64 
        }, false);

        if(this.state.ui.chatAbortController?.signal.aborted) {
            return; 
        }

        if(result.success && result.data.reply) {
            const aiResponseText = result.data.reply;
            const placeholderIndex = this.state.chat.messages.findIndex(m => m.isTyping);
            if (placeholderIndex !== -1) {
                this.state.chat.messages[placeholderIndex].content = aiResponseText;
                delete this.state.chat.messages[placeholderIndex].isTyping;
                this.render.activeChatMessages();
            }
        } else {
            throw new Error(result.error || 'استجابة غير صالحة من الخادم');
        }

    } catch (error) { 
        if (error.name === 'AbortError') {
            console.log('Fetch aborted by user.');
            const placeholderIndex = this.state.chat.messages.findIndex(m => m.isTyping);
            if(placeholderIndex !== -1) {
                this.state.chat.messages.splice(placeholderIndex, 1);
                this.render.activeChatMessages();
            }
        } else {
            console.error("AI Chat Error:", error);
            const placeholderIndex = this.state.chat.messages.findIndex(m => m.isTyping);
            if (placeholderIndex !== -1) {
                this.state.chat.messages[placeholderIndex].content = 'عذراً، حدث خطأ أثناء التواصل مع المساعد الذكي.';
                delete this.state.chat.messages[placeholderIndex].isTyping;
                this.render.activeChatMessages();
            }
            this.utils.showMessageBox(error.message, 'error');
        }
    } finally { 
        this.state.ui.isGeneratingResponse = false; 
        this.elements.sendBtn.classList.remove('bg-rose-500', 'hover:bg-rose-600');
        this.elements.sendBtn.classList.add('bg-sky-500', 'hover:bg-sky-600');
        this.elements.sendBtnContent.innerHTML = '<i class="fas fa-paper-plane ml-2"></i> إرسال';
    }
},
handleStopGenerating() {
    if(this.state.ui.chatAbortController) {
        this.state.ui.chatAbortController.abort();
        this.utils.showMessageBox("تم إيقاف الرد.", "success");
    }
},
async loadChatSessions() {
    const result = await this.api('chat', 'GET', { action: 'get_sessions' }, false);
    if (result.success) {
        this.state.chat.sessions = result.data;
        this.render.chatSessionsList();
    }
},
 async loadChatMessages(sessionId) {
    if (this.state.chat.activeSessionId === sessionId) return;
    this.state.chat.activeSessionId = sessionId;
    this.render.chatSessionsList();
    this.elements.chatWindow.innerHTML = '';
    if(this.elements.chatWelcome) this.elements.chatWelcome.classList.add('hidden');

    const result = await this.api('chat', 'GET', { action: 'get_messages', session_id: sessionId }, false);
    if (result.success) {
        this.state.chat.messages = result.data.map(m => ({
            role: m.role,
            content: m.content,
            image_path: m.image_path,
            audio_path: m.audio_path
        }));
        this.render.activeChatMessages();
    }
},
async startNewChatSession(title = 'محادثة جديدة') {
    const result = await this.api('chat', 'POST', { action: 'new_session', title: title.substring(0, 40) });
    if(result.success) {
        this.state.chat.activeSessionId = result.data.id;
        this.state.chat.messages = [];
        await this.loadChatSessions();
        this.render.activeChatMessages();
        return result.data;
    }
    return null;
},
async deleteChatSession(sessionId) {
    const result = await this.api('chat', 'POST', { action: 'delete_session', session_id: sessionId });
    if (result.success) {
        await this.loadChatSessions();
        if (this.state.chat.activeSessionId == sessionId) {
            this.state.chat.activeSessionId = null;
            this.state.chat.messages = [];
            this.render.activeChatMessages();
        }
    }
},
async handleStartRecording() {
    if (this.state.ui.isRecording) return;

    try {
        const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
        this.state.ui.mediaRecorder = new MediaRecorder(stream, { mimeType: 'audio/webm' });
        this.state.ui.audioChunks = [];

        this.state.ui.mediaRecorder.ondataavailable = event => {
            this.state.ui.audioChunks.push(event.data);
        };

        this.state.ui.mediaRecorder.onstop = () => {
             stream.getTracks().forEach(track => track.stop());
        };

        this.state.ui.mediaRecorder.start();
        this.state.ui.isRecording = true;
        
        this.elements.chatInputContainer.classList.add('hidden');
        this.elements.recordingContainer.classList.add('flex');
        this.elements.recordingContainer.classList.remove('hidden');

        let seconds = 0;
        this.elements.recordingTimer.textContent = '00:00';
        this.state.ui.recordingTimerInterval = setInterval(() => {
            seconds++;
            const mins = Math.floor(seconds / 60).toString().padStart(2, '0');
            const secs = (seconds % 60).toString().padStart(2, '0');
            this.elements.recordingTimer.textContent = `${mins}:${secs}`;
        }, 1000);

    } catch (err) {
        console.error("Error starting recording:", err);
        this.utils.showMessageBox('لم يتم السماح بالوصول إلى الميكروفون.', 'error');
    }
},
handleStopRecording() {
    if (!this.state.ui.isRecording) return;
    this.state.ui.mediaRecorder.stop();
    this.state.ui.isRecording = false;
    clearInterval(this.state.ui.recordingTimerInterval);
},
handleCancelRecording() {
    this.handleStopRecording();
    this.elements.recordingContainer.classList.add('hidden');
    this.elements.recordingContainer.classList.remove('flex');
    this.elements.chatInputContainer.classList.remove('hidden');
},
async handleSendRecording() {
    this.handleStopRecording();
    
    const audioBlob = new Blob(this.state.ui.audioChunks, { type: 'audio/webm' });
    this.handleCancelRecording(); 

    if (audioBlob.size === 0) return;

    const userMessageData = { 
        role: 'user', 
        content: '[رسالة صوتية]',
        audio_path: URL.createObjectURL(audioBlob) 
    };
    this.state.chat.messages.push(userMessageData);
    this.render.activeChatMessages();

    let currentSessionId = this.state.chat.activeSessionId;
     if (!currentSessionId) {
         const newSession = await this.startNewChatSession("محادثة صوتية");
         if (newSession) {
             currentSessionId = newSession.id;
         } else {
             this.utils.showMessageBox("لا يمكن بدء محادثة جديدة.", "error");
             return;
         }
     }

    const formData = new FormData();
    formData.append('action', 'generate_response');
    formData.append('session_id', currentSessionId);
    formData.append('audio', audioBlob, 'recording.webm');
    
    this.state.ui.isGeneratingResponse = true;
    const modelMessagePlaceholder = { role: 'model', content: '', isTyping: true };
    this.state.chat.messages.push(modelMessagePlaceholder);
    this.render.activeChatMessages();

    try {
        const result = await this.api('chat', 'POST', formData, false);
        if(result.success && result.data.reply) {
            const aiResponseText = result.data.reply;
            const placeholderIndex = this.state.chat.messages.findIndex(m => m.isTyping);
            if (placeholderIndex !== -1) {
                this.state.chat.messages[placeholderIndex].content = aiResponseText;
                delete this.state.chat.messages[placeholderIndex].isTyping;
                this.loadChatMessages(currentSessionId);
            }
        } else {
            throw new Error(result.error || 'استجابة غير صالحة من الخادم');
        }
    } catch (error) {
          console.error("AI Audio Chat Error:", error);
          const placeholderIndex = this.state.chat.messages.findIndex(m => m.isTyping);
          if (placeholderIndex !== -1) {
              this.state.chat.messages[placeholderIndex].content = 'عذراً، حدث خطأ أثناء معالجة الرسالة الصوتية.';
              delete this.state.chat.messages[placeholderIndex].isTyping;
              this.render.activeChatMessages();
          }
    } finally {
         this.state.ui.isGeneratingResponse = false;
    }
},
async handleReadAloud(text, button) {
    const icon = button.querySelector('i');
    const messageIndex = parseInt(button.dataset.messageIndex, 10);
    const message = this.state.chat.messages[messageIndex];

    if (this.state.ui.currentAudio && !this.state.ui.currentAudio.paused) {
        this.state.ui.currentAudio.pause();
        this.state.ui.currentAudio.currentTime = 0;
        if(this.state.ui.currentReadAloudButton) {
           this.state.ui.currentReadAloudButton.querySelector('i').className = 'fas fa-volume-up';
        }
        if (this.state.ui.currentReadAloudButton === button) {
            this.state.ui.currentReadAloudButton = null;
            this.state.ui.currentAudio = null;
            return;
        }
    }
    
    this.state.ui.currentReadAloudButton = button;

    if (message.tts_audio_url) {
        const audio = new Audio(message.tts_audio_url);
        this.state.ui.currentAudio = audio;
        
        audio.onplaying = () => icon.className = 'fas fa-stop-circle text-sky-600 animate-pulse';
        audio.onended = () => {
            icon.className = 'fas fa-volume-up';
            this.state.ui.currentAudio = null;
            this.state.ui.currentReadAloudButton = null;
        };
        audio.onerror = (e) => {
            console.error("Audio playback error:", e);
            this.utils.showMessageBox('حدث خطأ أثناء تشغيل الصوت.', 'error');
            icon.className = 'fas fa-volume-up';
        };

        audio.play();
        return; 
    }

    if (this.state.ui.isTTSLoading) {
        this.utils.showMessageBox('يرجى الانتظار حتى يكتمل الطلب الصوتي الحالي.', 'error');
        return;
    }
    this.state.ui.isTTSLoading = true;
    icon.className = 'fas fa-spinner fa-spin';

    try {
        const result = await this.api('chat', 'POST', {
            action: 'text_to_speech',
            text: text
        }, false);

        if (result.success && result.data.audio_base64) {
            const mimeType = result.data.mime_type;
            const sampleRateMatch = mimeType.match(/rate=(\d+)/);
            const sampleRate = sampleRateMatch ? parseInt(sampleRateMatch[1], 10) : 24000;
            
            const pcmData = this.utils.base64ToArrayBuffer(result.data.audio_base64);
            const pcm16 = new Int16Array(pcmData);
            const wavBlob = this.utils.pcmToWav(pcm16, sampleRate);
            const audioUrl = URL.createObjectURL(wavBlob);
            
            this.state.chat.messages[messageIndex].tts_audio_url = audioUrl;

            const audio = new Audio(audioUrl);
            this.state.ui.currentAudio = audio;

            audio.onplaying = () => {
                icon.className = 'fas fa-stop-circle text-sky-600 animate-pulse';
            };

            audio.onended = () => {
                icon.className = 'fas fa-volume-up';
                this.state.ui.currentAudio = null;
                this.state.ui.currentReadAloudButton = null;
            };
            
            audio.onerror = (e) => {
                console.error("Audio playback error:", e);
                this.utils.showMessageBox('حدث خطأ أثناء تشغيل الصوت.', 'error');
                icon.className = 'fas fa-volume-up';
                this.state.ui.currentAudio = null;
                this.state.ui.currentReadAloudButton = null;
            };

            audio.play();
        } else {
            throw new Error(result.error || "لم يتمكن من تحويل النص إلى كلام.");
        }
    } catch (error) {
        console.error("Text-to-Speech API error:", error);
        this.utils.showMessageBox(error.message, 'error');
        icon.className = 'fas fa-volume-up';
        this.state.ui.currentReadAloudButton = null;
        this.state.ui.currentAudio = null;
    } finally {
        this.state.ui.isTTSLoading = false;
    }
},