/**
 * Gallery Manager - CSP Compliant
 * Gestisce tutti gli eventi della galleria senza inline onclick
 */

document.addEventListener('DOMContentLoaded', function() {
    // ==================== BUTTON EVENT LISTENERS ====================

    // Upload buttons
    const uploadButton = document.getElementById('uploadButton');
    const uploadButtonEmpty = document.getElementById('uploadButtonEmpty');

    if (uploadButton) {
        uploadButton.addEventListener('click', openUploadModal);
    }
    if (uploadButtonEmpty) {
        uploadButtonEmpty.addEventListener('click', openUploadModal);
    }

    // Link buttons
    const linkButton = document.getElementById('linkButton');
    const linkButtonEmpty = document.getElementById('linkButtonEmpty');

    if (linkButton) {
        linkButton.addEventListener('click', openLinkModal);
    }
    if (linkButtonEmpty) {
        linkButtonEmpty.addEventListener('click', openLinkModal);
    }

    // Close buttons
    const closeUploadBtn = document.getElementById('closeUploadModal');
    const closeLinkBtn = document.getElementById('closeLinkModal');
    const closeEditBtn = document.getElementById('closeEditModal');
    const closeLightboxBtn = document.getElementById('closeLightbox');

    if (closeUploadBtn) {
        closeUploadBtn.addEventListener('click', closeUploadModal);
    }
    if (closeLinkBtn) {
        closeLinkBtn.addEventListener('click', closeLinkModal);
    }
    if (closeEditBtn) {
        closeEditBtn.addEventListener('click', closeEditModal);
    }
    if (closeLightboxBtn) {
        closeLightboxBtn.addEventListener('click', closeLightbox);
    }

    // ==================== MEDIA GRID EVENT DELEGATION ====================

    // Event delegation per click su media items
    const mediaGrid = document.getElementById('mediaGrid');
    if (mediaGrid) {
        mediaGrid.addEventListener('click', function(e) {
            const target = e.target;

            // Media click (immagini, video)
            if (target.hasAttribute('data-media-url')) {
                const url = target.getAttribute('data-media-url');
                const title = target.getAttribute('data-media-title');
                const type = target.getAttribute('data-media-type');
                openLightbox(url, title, type);
                return;
            }

            // External link click
            if (target.hasAttribute('data-external-url')) {
                const url = target.getAttribute('data-external-url');
                window.open(url, '_blank');
                return;
            }

            // Edit button
            if (target.closest('[data-edit-media]')) {
                const btn = target.closest('[data-edit-media]');
                const mediaId = btn.getAttribute('data-edit-media');
                editMedia(mediaId);
                return;
            }

            // Delete button
            if (target.closest('[data-delete-media]')) {
                const btn = target.closest('[data-delete-media]');
                const mediaId = btn.getAttribute('data-delete-media');
                deleteMedia(mediaId);
                return;
            }
        });
    }

    // ==================== FORM HANDLERS ====================

    // Upload form
    const uploadForm = document.getElementById('uploadForm');
    if (uploadForm) {
        uploadForm.addEventListener('submit', handleUploadSubmit);
    }

    // Link form
    const linkForm = document.getElementById('linkForm');
    if (linkForm) {
        linkForm.addEventListener('submit', handleLinkSubmit);
    }
});

// ==================== MODAL FUNCTIONS ====================

function openUploadModal() {
    const modal = document.getElementById('uploadModal');
    if (modal) {
        modal.classList.remove('hidden');
    }
}

function closeUploadModal() {
    const modal = document.getElementById('uploadModal');
    if (modal) {
        modal.classList.add('hidden');
    }
    const form = document.getElementById('uploadForm');
    if (form) {
        form.reset();
    }
}

function openLinkModal() {
    const modal = document.getElementById('linkModal');
    if (modal) {
        modal.classList.remove('hidden');
    }
}

function closeLinkModal() {
    const modal = document.getElementById('linkModal');
    if (modal) {
        modal.classList.add('hidden');
    }
    const form = document.getElementById('linkForm');
    if (form) {
        form.reset();
    }
}

function closeEditModal() {
    const modal = document.getElementById('editModal');
    if (modal) {
        modal.classList.add('hidden');
    }
}

function closeLightbox() {
    const lightbox = document.getElementById('lightbox');
    if (lightbox) {
        lightbox.classList.add('hidden');
    }
}

// ==================== LIGHTBOX ====================

function openLightbox(url, title, type) {
    const lightbox = document.getElementById('lightbox');
    const content = document.getElementById('lightboxContent');

    if (!lightbox || !content) return;

    if (type === 'image') {
        content.innerHTML = `<img src="${url}" alt="${title}" class="max-w-full max-h-full object-contain">`;
    } else if (type === 'youtube' || type === 'vimeo') {
        content.innerHTML = `<iframe src="${url}" class="w-full h-96" frameborder="0" allowfullscreen></iframe>`;
    } else if (type === 'video') {
        content.innerHTML = `<video controls class="max-w-full max-h-full"><source src="${url}" type="video/mp4"></video>`;
    }

    lightbox.classList.remove('hidden');
}

// ==================== MEDIA ACTIONS ====================

function editMedia(id) {
    // Implementa edit media
    console.log('Edit media:', id);
    // TODO: Open edit modal
}

function deleteMedia(id) {
    if (!confirm('Sei sicuro di voler eliminare questo elemento?')) {
        return;
    }

    // Get CSRF token
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    fetch(`/admin/galleries/media/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': token,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Errore: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Errore durante l\'eliminazione');
    });
}

// ==================== FORM SUBMIT HANDLERS ====================

function handleUploadSubmit(e) {
    e.preventDefault();

    const form = e.target;
    const formData = new FormData(form);
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;

    submitBtn.innerHTML = '<svg class="w-4 h-4 animate-spin inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>Caricamento...';
    submitBtn.disabled = true;

    // Get action URL from form
    const actionUrl = form.getAttribute('action');

    fetch(actionUrl, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeUploadModal();
            location.reload();
        } else {
            alert('Errore: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Errore durante il caricamento');
    })
    .finally(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}

function handleLinkSubmit(e) {
    e.preventDefault();

    const form = e.target;
    const formData = new FormData(form);
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;

    submitBtn.innerHTML = '<svg class="w-4 h-4 animate-spin inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>Aggiunta...';
    submitBtn.disabled = true;

    // Get action URL from form
    const actionUrl = form.getAttribute('action');

    fetch(actionUrl, {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            closeLinkModal();
            location.reload();
        } else {
            alert('Errore: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Full error:', error);
        alert('Errore durante l\'aggiunta del link: ' + error.message);
    })
    .finally(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
}
