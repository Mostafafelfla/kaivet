<?php // js-modules/10.settings.js.php (الإصدار النهائي مع البحث التلقائي) ?>

// --- 10. SETTINGS & MAP MODULE ---
bindSettingsEvents() {
    const form = document.getElementById('settings-profile-view').querySelector('form');
    
    if (form) {
         form.addEventListener('submit', async (e) => {
            e.preventDefault();
            await app.saveSettings(form); 
         });
    }

    document.getElementById('profilePicInput').addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = e => { document.getElementById('profilePicPreview').src = e.target.result; };
            reader.readAsDataURL(this.files[0]);
        }
    });
},

async saveSettings(formElement) {
    const formData = new FormData(formElement);
    const picInput = document.getElementById('profilePicInput');
     if (picInput.files[0]) {
        formData.append('profile_pic', picInput.files[0]);
     }
     
    formData.append('clinic_lat', document.getElementById('clinic_lat').value);
    formData.append('clinic_lng', document.getElementById('clinic_lng').value);

    const result = await app.api('settings', 'POST', formData);
    if (result.success) {
        await app.loadAllData(); 
    }
},

initMap() {
    if (app.state.ui.mapInitialized || !document.getElementById('map') || typeof google === 'undefined') return;
    
    // دالة Debounce لمنع إرسال طلبات كثيرة أثناء الكتابة
    let debounceTimeout;
    const debounce = (func, delay) => {
        return (...args) => {
            clearTimeout(debounceTimeout);
            debounceTimeout = setTimeout(() => {
                func.apply(app, args);
            }, delay);
        };
    };
    
    // إنشاء نسخة "مؤجلة" من دالة البحث (تعمل بعد 800ms من توقف الكتابة)
    const debouncedGeocode = debounce(app.geocodeAddress, 800);

    const savedLat = parseFloat(app.state.settings.clinic_lat) || 30.0444; 
    const savedLng = parseFloat(app.state.settings.clinic_lng) || 31.2357;
    const initialLatLng = { lat: savedLat, lng: savedLng };

    app.geocoder = new google.maps.Geocoder();
    app.map = new google.maps.Map(document.getElementById("map"), { center: initialLatLng, zoom: 15 });
    app.marker = new google.maps.Marker({ map: app.map, position: initialLatLng, draggable: true });

    app.marker.addListener('dragend', (event) => {
        const pos = event.latLng;
        document.getElementById('clinic_lat').value = pos.lat();
        document.getElementById('clinic_lng').value = pos.lng();
        app.geocodePosition(pos); 
    });

    // مراقبة الكتابة في حقل العنوان
    document.getElementById('clinicAddressInput').addEventListener('input', (e) => {
        debouncedGeocode(e.target.value);
    });
    
    app.state.ui.mapInitialized = true;
},

geocodeAddress(address) {
    if (!app.geocoder || !address) return;
    app.geocoder.geocode({ 'address': address }, (results, status) => {
        if (status == 'OK') {
            const pos = results[0].geometry.location;
            app.map.setCenter(pos);
            app.marker.setPosition(pos);
            document.getElementById('clinic_lat').value = pos.lat();
            document.getElementById('clinic_lng').value = pos.lng();
        }
    });
},

geocodePosition(pos) {
    app.geocoder.geocode({ location: pos }, (results, status) => {
        if (status === 'OK' && results[0]) {
            document.getElementById('clinicAddressInput').value = results[0].formatted_address;
        }
    });
},