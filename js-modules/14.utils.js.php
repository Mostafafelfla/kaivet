// --- UTILITY FUNCTIONS ---
showLoader(show) { app.elements.loadingOverlay.classList.toggle('hidden', !show); },
showMessageBox(message, type = 'success') { if (!message) return; const box = app.elements.messageBox; box.textContent = message; box.className = `fixed top-4 right-4 p-4 rounded-lg text-center w-full max-w-sm z-[9999] shadow-lg text-white ${type === 'success' ? 'bg-emerald-500' : 'bg-rose-500'} animate__animated animate__fadeInRight`; box.classList.remove('hidden'); setTimeout(() => { box.classList.add('animate__animated', 'animate__fadeOutRight'); setTimeout(() => { box.classList.add('hidden'); box.classList.remove('animate__animated', 'animate__fadeOutRight'); }, 500); }, 3000); },
formatCurrency(value) { return Number(value || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); },
formatDateForInput(dateString) {
    if (!dateString) return '';
    try {
        const date = new Date(dateString);
        if (isNaN(date.getTime())) return '';
        return date.toISOString().split('T')[0];
    } catch (e) {
        console.error("Invalid date for input:", dateString, e);
        return '';
    }
},
base64ToArrayBuffer(base64) {
    const binaryString = window.atob(base64);
    const len = binaryString.length;
    const bytes = new Uint8Array(len);
    for (let i = 0; i < len; i++) {
        bytes[i] = binaryString.charCodeAt(i);
    }
    return bytes.buffer;
},
pcmToWav(pcmData, sampleRate) {
    const numChannels = 1;
    const bitsPerSample = 16;
    const blockAlign = (numChannels * bitsPerSample) / 8;
    const byteRate = sampleRate * blockAlign;
    const dataSize = pcmData.length * (bitsPerSample / 8);
    const buffer = new ArrayBuffer(44 + dataSize);
    const view = new DataView(buffer);

    view.setUint32(0, 0x52494646, false); // "RIFF"
    view.setUint32(4, 36 + dataSize, true);
    view.setUint32(8, 0x57415645, false); // "WAVE"
    view.setUint32(12, 0x666d7420, false); // "fmt "
    view.setUint32(16, 16, true); 
    view.setUint16(20, 1, true); 
    view.setUint16(22, numChannels, true);
    view.setUint32(24, sampleRate, true);
    view.setUint32(28, byteRate, true);
    view.setUint16(32, blockAlign, true);
    view.setUint16(34, bitsPerSample, true);
    view.setUint32(36, 0x64617461, false); // "data"
    view.setUint32(40, dataSize, true);

    for (let i = 0; i < pcmData.length; i++) {
        view.setInt16(44 + i * 2, pcmData[i], true);
    }

    return new Blob([view], { type: 'audio/wav' });
}