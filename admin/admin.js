document.addEventListener('DOMContentLoaded', function() {
    // Auto-dismiss alerts after 5 seconds
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            if (alert) {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            }
        }, 5000);
    });

    // Handle click on close button
    document.querySelectorAll('.alert-close').forEach(closeBtn => {
        closeBtn.addEventListener('click', function() {
            const alert = this.parentElement;
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 500);
        });
    });
});

const editModal = document.getElementById('editModal');
const confirmModal = document.getElementById('confirmModal');
const closeBtns = document.getElementsByClassName('close');
let originalData = {};

function openEditModal(record) {
    originalData = record;
    editModal.style.display = 'block';
    
    // Populate all fields
    document.getElementById('edit_id').value = record.id;
    document.getElementById('edit_name').value = record.name;
    document.getElementById('edit_age').value = record.age;
    document.getElementById('edit_gender').value = record.gender;
    document.getElementById('edit_campus').value = record.campus;
    document.getElementById('edit_email').value = record.email;
    document.getElementById('edit_category1').value = record.category1_score;
    document.getElementById('edit_category2').value = record.category2_score;
    document.getElementById('edit_category3').value = record.category3_score;
    document.getElementById('edit_category4').value = record.category4_score;
    document.getElementById('edit_category5').value = record.category5_score;
}

function previewChanges() {
    const changesGrid = document.querySelector('.changes-grid');
    changesGrid.innerHTML = '';
    let hasChanges = false;

    const fields = [
        { id: 'edit_name', label: 'Name', original: 'name' },
        { id: 'edit_age', label: 'Age', original: 'age' },
        { id: 'edit_gender', label: 'Gender', original: 'gender' },
        { id: 'edit_campus', label: 'Campus', original: 'campus' },
        { id: 'edit_email', label: 'Email', original: 'email' },
        { id: 'edit_category1', label: 'Category 1', original: 'category1_score' },
        { id: 'edit_category2', label: 'Category 2', original: 'category2_score' },
        { id: 'edit_category3', label: 'Category 3', original: 'category3_score' },
        { id: 'edit_category4', label: 'Category 4', original: 'category4_score' },
        { id: 'edit_category5', label: 'Category 5', original: 'category5_score' }
    ];

    fields.forEach(field => {
        const newValue = document.getElementById(field.id).value;
        const oldValue = originalData[field.original];
        
        if (String(newValue) !== String(oldValue)) {  // Convert to string for comparison
            hasChanges = true;
            changesGrid.innerHTML += `
                <div class="change-row">
                    <div class="field-name">${field.label}</div>
                    <div class="old-value">${oldValue}</div>
                    <div class="arrow">→</div>
                    <div class="new-value">${newValue}</div>
                </div>
            `;
        }
    });

    if (!hasChanges) {
        changesGrid.innerHTML = '<p class="no-changes">No changes detected</p>';
    }

    editModal.style.display = 'none';
    confirmModal.style.display = 'block';
}

function confirmChanges() {
    document.getElementById('editForm').onsubmit = null; 
    document.getElementById('editForm').submit();
}

function closeConfirmModal() {
    confirmModal.style.display = 'none';
    editModal.style.display = 'block';
}

// Close button functionality
Array.from(closeBtns).forEach(btn => {
    btn.onclick = function() {
        editModal.style.display = 'none';
        confirmModal.style.display = 'none';
    }
});

// Click outside modal to close
window.onclick = function(event) {
    if (event.target == editModal) {
        editModal.style.display = 'none';
    }
    if (event.target == confirmModal) {
        confirmModal.style.display = 'none';
    }
}
