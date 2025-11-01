// KRS JavaScript - Updated untuk User dengan filter semester
document.addEventListener('DOMContentLoaded', function() {
    initializeKRS();
});

function initializeKRS() {
    loadSelectedCourses();
    setupKRSActions();
    setupCourseModal();
}

// Load matakuliah yang sudah dipilih
async function loadSelectedCourses() {
    showLoadingState('Memuat data KRS...');
    
    try {
        const response = await fetch(window.KRS_API.selected);
        const result = await response.json();
        
        if (result.status === 'ok') {
            renderSelectedCourses(result.data, result.total_sks);
        } else {
            console.error('Failed to load KRS:', result);
        }
    } catch (error) {
        console.error('Error loading KRS:', error);
        showErrorMessage('Gagal memuat data KRS');
    } finally {
        hideLoadingState();
    }
}

// Render matakuliah yang dipilih
function renderSelectedCourses(courses, totalSKS) {
    const tbody = document.getElementById('selectedCoursesBody');
    tbody.innerHTML = '';
    
    if (courses.length === 0) {
        tbody.innerHTML = `
            <tr class="no-courses-message">
                <td colspan="8">
                    <div class="empty-state">
                        <i class="fas fa-book-open"></i>
                        <p>Belum ada matakuliah yang dipilih</p>
                    </div>
                </td>
            </tr>
        `;
    } else {
        courses.forEach((course, index) => {
            const tr = document.createElement('tr');
            const tipeBadge = course.tipe_mk === 'P' ? 
                '<span class="type-badge pilihan">Pilihan</span>' : 
                '<span class="type-badge wajib">Wajib</span>';
            
            tr.innerHTML = `
                <td>${index + 1}</td>
                <td><span class="course-code">${escapeHtml(course.kode_mk)}</span></td>
                <td>${escapeHtml(course.nama_mk)}</td>
                <td><span class="sks-badge">${course.sks}</span></td>
                <td><span class="class-badge">${escapeHtml(course.kelas || '-')}</span></td>
                <td>${course.semester_mk || '-'}</td>
                <td>${escapeHtml(course.dosen || '-')}</td>
                <td class="edit-mode-visible">
                    <input type="checkbox" class="course-checkbox" data-krs-id="${course.id}">
                </td>
            `;
            tbody.appendChild(tr);
        });
    }
    
    document.getElementById('totalSKS').textContent = totalSKS || 0;
}

// Setup KRS actions
function setupKRSActions() {
    const editCourseBtn = document.getElementById('editCourseBtn');
    const deleteSelectedBtn = document.getElementById('deleteSelectedBtn');
    const addCourseBtn = document.getElementById('addCourseBtn');
    const selectedCoursesSection = document.querySelector('.selected-courses-section');
    
    let isEditMode = false;
    
    // Toggle edit mode
    editCourseBtn.addEventListener('click', function() {
        isEditMode = !isEditMode;
        
        if (isEditMode) {
            selectedCoursesSection.classList.add('edit-mode');
            editCourseBtn.innerHTML = '<i class="fas fa-times"></i><span>Batal Edit</span>';
            editCourseBtn.classList.remove('btn-secondary');
            editCourseBtn.classList.add('btn-danger');
        } else {
            selectedCoursesSection.classList.remove('edit-mode');
            editCourseBtn.innerHTML = '<i class="fas fa-edit"></i><span>Edit Matakuliah</span>';
            editCourseBtn.classList.remove('btn-danger');
            editCourseBtn.classList.add('btn-secondary');
            
            // Uncheck all
            document.querySelectorAll('.course-checkbox').forEach(cb => cb.checked = false);
        }
    });
    
    // Delete selected courses
    deleteSelectedBtn.addEventListener('click', async function() {
        const checkboxes = document.querySelectorAll('.course-checkbox:checked');
        
        if (checkboxes.length === 0) {
            showWarningMessage('Pilih matakuliah yang akan dihapus terlebih dahulu.');
            return;
        }
        
        if (!confirm(`Apakah Anda yakin ingin menghapus ${checkboxes.length} matakuliah terpilih?`)) {
            return;
        }
        
        showLoadingState('Menghapus matakuliah...');
        
        try {
            for (const checkbox of checkboxes) {
                const krsId = checkbox.dataset.krsId;
                
                const formData = new FormData();
                formData.append('krs_id', krsId);
                
                await fetch(window.KRS_API.delete, {
                    method: 'POST',
                    body: formData
                });
            }
            
            hideLoadingState();
            showSuccessMessage('Matakuliah berhasil dihapus');
            loadSelectedCourses();
            
            // Exit edit mode
            isEditMode = false;
            selectedCoursesSection.classList.remove('edit-mode');
            editCourseBtn.innerHTML = '<i class="fas fa-edit"></i><span>Edit Matakuliah</span>';
            editCourseBtn.classList.remove('btn-danger');
            editCourseBtn.classList.add('btn-secondary');
        } catch (error) {
            hideLoadingState();
            showErrorMessage('Gagal menghapus matakuliah');
            console.error(error);
        }
    });
    
    // Add course button
    addCourseBtn.addEventListener('click', function() {
        loadAvailableCourses();
        document.getElementById('addCourseModal').classList.add('show');
    });
}

// Load available courses untuk modal
async function loadAvailableCourses() {
    showLoadingState('Memuat daftar matakuliah...');
    
    try {
        const response = await fetch(window.KRS_API.available);
        const result = await response.json();
        
        if (result.status === 'ok') {
            renderAvailableCourses(result.data);
        } else {
            showErrorMessage(result.message || 'Gagal memuat daftar matakuliah');
        }
    } catch (error) {
        console.error('Error loading available courses:', error);
        showErrorMessage('Gagal memuat daftar matakuliah');
    } finally {
        hideLoadingState();
    }
}

// Render available courses di modal
function renderAvailableCourses(courses) {
    const list = document.getElementById('availableCoursesList');
    list.innerHTML = '';
    
    if (courses.length === 0) {
        list.innerHTML = `
            <div style="text-align:center;padding:2rem;color:var(--text-muted);">
                <i class="fas fa-info-circle" style="font-size:2rem;display:block;margin-bottom:1rem;"></i>
                <p>Semua matakuliah untuk semester ini sudah ditambahkan.</p>
            </div>
        `;
        return;
    }
    
    courses.forEach(course => {
        const item = document.createElement('div');
        item.className = 'course-item';
        
        const tipeBadge = course.tipe_mk === 'P' ? 
            '<span class="type-badge pilihan">Pilihan</span>' : 
            '<span class="type-badge wajib">Wajib</span>';
        
        item.innerHTML = `
            <div class="course-info">
                <h4>${escapeHtml(course.nama_mk)} ${tipeBadge}</h4>
                <p><strong>Kode:</strong> ${escapeHtml(course.kode_mk)} | <strong>SKS:</strong> ${course.sks} | <strong>Semester:</strong> ${course.semester_mk}</p>
                <p><strong>Kelas:</strong> ${escapeHtml(course.kelas || '-')} | <strong>Dosen:</strong> ${escapeHtml(course.dosen || 'Belum ditentukan')}</p>
            </div>
            <button class="btn btn-sm btn-primary add-course-btn" data-kode="${escapeHtml(course.kode_mk)}">
                <i class="fas fa-plus"></i>
                Tambah
            </button>
        `;
        list.appendChild(item);
    });
    
    // Add event listeners
    list.querySelectorAll('.add-course-btn').forEach(btn => {
        btn.addEventListener('click', async function() {
            const kodeMk = this.dataset.kode;
            await addCourseToKRS(kodeMk);
        });
    });
}

// Add course to KRS
async function addCourseToKRS(kodeMk) {
    showLoadingState('Menambahkan matakuliah...');
    
    try {
        const formData = new FormData();
        formData.append('kode_mk', kodeMk);
        
        const response = await fetch(window.KRS_API.add, {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.status === 'ok') {
            hideLoadingState();
            showSuccessMessage('Matakuliah berhasil ditambahkan');
            document.getElementById('addCourseModal').classList.remove('show');
            loadSelectedCourses();
        } else {
            hideLoadingState();
            showErrorMessage(result.message || 'Gagal menambahkan matakuliah');
        }
    } catch (error) {
        hideLoadingState();
        showErrorMessage('Gagal menambahkan matakuliah');
        console.error(error);
    }
}

// Setup course modal
function setupCourseModal() {
    const modal = document.getElementById('addCourseModal');
    const closeBtn = document.getElementById('closeAddCourseModal');
    const searchInput = document.getElementById('courseSearch');
    
    // Close modal
    closeBtn.addEventListener('click', function() {
        modal.classList.remove('show');
    });
    
    // Close when clicking outside
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.classList.remove('show');
        }
    });
    
    // Search functionality
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const courseItems = document.querySelectorAll('.course-item');
        
        courseItems.forEach(item => {
            const text = item.textContent.toLowerCase();
            item.style.display = text.includes(searchTerm) ? 'flex' : 'none';
        });
    });
}

// Helper function to escape HTML
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text || '';
    return div.innerHTML;
}

// Add CSS for type badges
const style = document.createElement('style');
style.textContent = `
    .type-badge {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 600;
        margin-left: 8px;
    }
    .type-badge.wajib {
        background: #22c55e;
        color: white;
    }
    .type-badge.pilihan {
        background: #3b82f6;
        color: white;
    }
`;
document.head.appendChild(style);