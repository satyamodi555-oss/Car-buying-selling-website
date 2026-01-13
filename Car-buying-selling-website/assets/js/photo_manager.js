// Photo Manager JavaScript

const uploadArea = document.getElementById('uploadArea');
const fileInput = document.getElementById('fileInput');
const photoGrid = document.getElementById('photoGrid');
const photoCountSpan = document.getElementById('photoCount');

// Click to upload
uploadArea.addEventListener('click', () => fileInput.click());

// Drag & Drop handlers
uploadArea.addEventListener('dragover', (e) => {
    e.preventDefault();
    uploadArea.classList.add('dragover');
    uploadArea.classList.add('border-primary');
});

uploadArea.addEventListener('dragleave', () => {
    uploadArea.classList.remove('dragover');
    uploadArea.classList.remove('border-primary');
});

uploadArea.addEventListener('drop', (e) => {
    e.preventDefault();
    uploadArea.classList.remove('dragover');
    uploadArea.classList.remove('border-primary');
    handleFiles(e.dataTransfer.files);
});

// File input change
fileInput.addEventListener('change', (e) => {
    handleFiles(e.target.files);
    // Reset input so same files can be selected again if needed
    fileInput.value = '';
});

// Handle file uploads
function handleFiles(files) {
    const formData = new FormData();
    formData.append('car_id', CAR_ID);

    // Validate and append files
    let validFiles = 0;
    for (let file of files) {
        if (file.size > 5 * 1024 * 1024) {
            alert(`${file.name} is too large. Max size is 5MB.`);
            continue;
        }

        if (!file.type.match('image.*')) {
            alert(`${file.name} is not an image.`);
            continue;
        }

        formData.append('photos[]', file);
        validFiles++;
    }

    if (validFiles === 0) return;

    // Show loading state
    const originalText = uploadArea.innerHTML;
    uploadArea.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin text-4xl text-primary mb-2"></i><p>Uploading...</p></div>';
    uploadArea.style.pointerEvents = 'none';

    // Upload
    fetch('../api/upload_photos.php', {
        method: 'POST',
        body: formData
    })
        .then(res => res.json())
        .then(data => {
            if (data.success && data.photos) {
                // Ensure grid container exists
                let grid = document.getElementById('photoGrid');
                if (!grid) {
                    const parent = document.getElementById('photosContainer');
                    if (parent) {
                        parent.innerHTML = ''; // Clear "No photos" text
                        grid = document.createElement('div');
                        grid.id = 'photoGrid';
                        grid.className = 'photo-grid grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4';
                        parent.appendChild(grid);
                    }
                }

                if (grid) {
                    data.photos.forEach(photo => {
                        addPhotoToGrid(photo);
                    });
                    updatePhotoCount(data.uploaded);
                } else {
                    console.error('Could not find or create photo grid');
                    alert('Photos uploaded but UI could not be updated. Please refresh.');
                }

            } else {
                alert('Upload failed: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(err => {
            console.error(err);
            alert('Upload failed: ' + err.message);
        })
        .finally(() => {
            uploadArea.innerHTML = originalText;
            uploadArea.style.pointerEvents = 'auto';
        });
}

function createGridContainerIfNeeded() {
    // Deprecated in favor of inline logic above, but kept empty/simple to avoid breakage if called elsewhere
    // Logic moved inside handleFiles for better flow
}

function addPhotoToGrid(photo) {
    const grid = document.getElementById('photoGrid');
    const div = document.createElement('div');

    // Check if main to style appropriately
    const isMainClass = photo.is_main ? 'ring-4 ring-green-500' : '';
    const mainBadge = photo.is_main ?
        `<div class="main-badge absolute top-2 left-2 bg-green-500 text-white px-3 py-1 rounded-full text-xs font-semibold z-10">
            <i class="fas fa-star mr-1"></i>Main Photo
        </div>` : '';

    // Only show Set Main button if NOT main
    const setMainBtn = !photo.is_main ?
        `<button class="flex-1 bg-green-500 hover:bg-green-600 text-white px-3 py-2 rounded text-sm transition btn-set-main" onclick="setMainPhoto(${photo.id})">
            <i class="fas fa-star mr-1"></i>Set Main
        </button>` : '';

    div.className = `photo-item relative group ${isMainClass} rounded-lg overflow-hidden shadow-lg transform transition duration-500 ease-in-out opacity-0 translate-y-4`;
    div.dataset.photoId = photo.id;
    div.innerHTML = `
        ${mainBadge}
        <img src="${photo.url}" alt="Car Photo" class="w-full h-48 object-cover">
        <div class="photo-actions absolute bottom-0 left-0 right-0 bg-black bg-opacity-75 p-3 opacity-0 group-hover:opacity-100 transition-opacity flex gap-2">
            ${setMainBtn}
            <button class="flex-1 bg-red-500 hover:bg-red-600 text-white px-3 py-2 rounded text-sm transition" onclick="deletePhoto(${photo.id})">
                <i class="fas fa-trash mr-1"></i>Delete
            </button>
        </div>
    `;

    grid.appendChild(div);

    // Trigger animation
    requestAnimationFrame(() => {
        div.classList.remove('opacity-0', 'translate-y-4');
    });
}

function updatePhotoCount(change) {
    const current = parseInt(photoCountSpan.textContent || '0');
    photoCountSpan.textContent = Math.max(0, current + change);
}

// Set main photo
function setMainPhoto(photoId) {
    // Optimistic UI update could be done, but let's wait for server to be safe
    // or adding a spinner to the button?
    const btn = document.querySelector(`div[data-photo-id="${photoId}"] .btn-set-main`);
    const originalContent = btn ? btn.innerHTML : '';
    if (btn) btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

    fetch('../api/set_main_photo.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ photo_id: photoId, car_id: CAR_ID })
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                updateMainPhotoUI(photoId);
            } else {
                alert('Failed: ' + data.message);
                if (btn) btn.innerHTML = originalContent;
            }
        })
        .catch(err => {
            console.error(err);
            alert('Failed. Please try again.');
            if (btn) btn.innerHTML = originalContent;
        });
}

function updateMainPhotoUI(newMainId) {
    // Remove main styling from old main
    const oldMain = document.querySelector('.photo-item.ring-4');
    if (oldMain) {
        oldMain.classList.remove('ring-4', 'ring-green-500');
        const badge = oldMain.querySelector('.main-badge');
        if (badge) badge.remove();

        // Add "Set Main" button back to old main
        const actions = oldMain.querySelector('.photo-actions');
        const deleteBtn = actions.querySelector('button.bg-red-500'); // Find delete button to insert before

        const setMainBtn = document.createElement('button');
        setMainBtn.className = 'flex-1 bg-green-500 hover:bg-green-600 text-white px-3 py-2 rounded text-sm transition btn-set-main';
        setMainBtn.innerHTML = '<i class="fas fa-star mr-1"></i>Set Main';
        setMainBtn.onclick = () => setMainPhoto(parseInt(oldMain.dataset.photoId));

        if (deleteBtn) {
            actions.insertBefore(setMainBtn, deleteBtn);
        } else {
            actions.appendChild(setMainBtn);
        }
    }

    // Add main styling to new main
    const newMain = document.querySelector(`div[data-photo-id="${newMainId}"]`);
    if (newMain) {
        newMain.classList.add('ring-4', 'ring-green-500');

        // Add badge
        const badge = document.createElement('div');
        badge.className = 'main-badge absolute top-2 left-2 bg-green-500 text-white px-3 py-1 rounded-full text-xs font-semibold z-10';
        badge.innerHTML = '<i class="fas fa-star mr-1"></i>Main Photo';
        newMain.prepend(badge);

        // Remove "Set Main" button
        const setMainBtn = newMain.querySelector('.btn-set-main');
        if (setMainBtn) setMainBtn.remove();
    }
}

// Delete photo
function deletePhoto(photoId) {
    if (!confirm('Delete this photo?')) return;

    fetch('../api/delete_photo.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ photo_id: photoId })
    })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const photoDiv = document.querySelector(`div[data-photo-id="${photoId}"]`);
                if (photoDiv) {
                    photoDiv.style.transition = 'all 0.5s ease';
                    photoDiv.style.opacity = '0';
                    photoDiv.style.transform = 'scale(0.8)';
                    setTimeout(() => {
                        photoDiv.remove();
                        updatePhotoCount(-1);

                        // Check if grid is empty
                        const grid = document.getElementById('photoGrid');
                        if (grid && grid.children.length === 0) {
                            const parent = document.getElementById('photosContainer');
                            if (parent) {
                                parent.innerHTML = '<p class="text-center text-gray-500 py-12">No photos uploaded yet. Add some photos to make your listing stand out!</p>';
                            }
                        }
                    }, 500);
                }

                // If backend assigned a new main photo automatically
                if (data.new_main_id) {
                    updateMainPhotoUI(data.new_main_id);
                }

            } else {
                alert('Failed: ' + data.message);
            }
        })
        .catch(err => {
            console.error(err);
            alert('Failed. Please try again.');
        });
}
