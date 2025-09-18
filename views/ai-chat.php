<div id="ai-chat-view" class="view hidden h-full max-h-[calc(100vh-4rem)] md:max-h-[calc(100vh-6rem)]">
    <div class="flex h-full bg-white rounded-2xl shadow-sm border border-slate-200 relative overflow-hidden">
        <div id="chat-history-sidebar" class="bg-slate-50 w-80 md:w-1/3 border-l rtl:border-r rtl:border-l-0 border-slate-200 flex flex-col h-full absolute md:static inset-y-0 right-0 z-20 transform md:transform-none translate-x-full md:hidden transition-transform duration-300 ease-in-out">
            <div class="p-4 border-b border-slate-200">
                <button id="new-chat-btn" class="w-full bg-sky-500 text-white py-2 px-4 rounded-lg font-semibold hover:bg-sky-600 shadow-sm flex items-center justify-center gap-2 transition-colors">
                    <i class="fas fa-plus"></i> محادثة جديدة
                </button>
            </div>
            <div id="chat-history-list" class="flex-grow overflow-y-auto custom-scrollbar p-2 space-y-1">
                </div>
        </div>

        <div id="chat-sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-10 hidden"></div>

        <div class="flex-1 flex flex-col">
                <div class="p-3 border-b border-slate-200 flex items-center">
                <button id="chat-sidebar-toggle" class="text-xl text-slate-600 p-2 rounded-md hover:bg-slate-100">
                    <i class="fas fa-bars"></i>
                </button>
                <h2 class="text-lg font-semibold text-slate-700 mx-auto">المساعد الذكي</h2>
            </div>

            <div id="chat-window" class="flex-grow overflow-y-auto p-4 space-y-4 bg-slate-50 custom-scrollbar">
                <div id="chat-welcome" class="flex flex-col items-center justify-center h-full text-center text-slate-500">
                    <i class="fas fa-robot text-6xl mb-4 text-sky-400"></i>
                    <h2 class="text-2xl font-bold text-slate-700">المساعد الذكي البيطري "نور"</h2>
                    <p>ابدأ محادثة جديدة أو اختر واحدة من القائمة.</p>
                </div>
                </div>
             <div id="image-preview-container" class="hidden p-2 border-t border-slate-200">
                 <div class="relative inline-block">
                     <img id="image-preview" src="" alt="Image preview" class="max-h-24 rounded-lg border border-slate-200">
                     <button id="remove-image-btn" class="absolute top-0 right-0 -mt-2 -mr-2 bg-rose-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs shadow-md">&times;</button>
                 </div>
             </div>
            <div class="p-4 border-t border-slate-200 bg-white">
                <div id="chat-input-container" class="flex items-center space-x-2 rtl:space-x-reverse">
                    <label for="image-input" class="cursor-pointer text-slate-500 hover:text-sky-600 p-3 rounded-full hover:bg-slate-100">
                        <i class="fas fa-paperclip text-xl"></i>
                    </label>
                    <input type="file" id="image-input" class="hidden" accept="image/*">
                    <input type="text" id="chat-input" placeholder="اكتب سؤالك هنا..." class="w-full p-3 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-400 text-right" />
                    <button id="voice-input-btn" class="text-slate-500 hover:text-sky-600 p-3 rounded-full hover:bg-slate-100"><i class="fas fa-microphone text-xl"></i></button>
                    <button id="send-btn" class="bg-sky-500 text-white p-3 rounded-lg font-semibold hover:bg-sky-600 transition-colors duration-200 flex items-center justify-center w-28">
                        <span id="send-btn-content"><i class="fas fa-paper-plane ml-1"></i></span>
                    </button>
                </div>
                <div id="recording-container" class="hidden items-center w-full bg-white p-2 rounded-lg border border-slate-300">
                    <button id="cancel-recording-btn" class="text-rose-500 hover:text-rose-700 p-3"><i class="fas fa-trash text-xl"></i></button>
                    <div class="flex-grow flex items-center justify-center gap-2">
                        <div class="recording-indicator"></div>
                        <span id="recording-timer" class="text-slate-700 font-mono">00:00</span>
                    </div>
                    <button id="send-recording-btn" class="bg-emerald-500 text-white rounded-full w-12 h-12 flex items-center justify-center text-xl hover:bg-emerald-600"><i class="fas fa-paper-plane"></i></button>
                   </div>
            </div>
        </div>
    </div>
</div>