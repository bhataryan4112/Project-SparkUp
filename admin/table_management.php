<?php
session_start();
// Debug current session status
error_log("Session ID at table management: " . session_id());
error_log("Admin logged in status: " . (isset($_SESSION['admin_logged_in']) ? 'true' : 'false'));

if (!isset($_SESSION['admin_logged_in'])) {
    // Store the requested page before redirecting
    $_SESSION['requested_page'] = 'table_management.php';
    header('Location: login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SparkUp - Table Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f5f5f5;
            padding-top: 20px;
        }
        .classroom-container {
            margin-bottom: 30px;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .classroom-title {
            margin-bottom: 20px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        .tables-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 15px;
        }
        .table-card {
            border: 2px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
            position: relative;
            cursor: pointer;
            transition: all 0.2s;
            height: 100%;
        }
        .table-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .table-number {
            font-size: 1.4rem;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .table-status {
            padding: 3px 8px;
            border-radius: 20px;
            font-size: 0.8rem;
            display: inline-block;
        }
        .table-card.available {
            border-color: #28a745;
        }
        .table-card.occupied {
            border-color: #dc3545;
        }
        .table-card.reserved {
            border-color: #ffc107;
        }
        .status-available {
            background-color: #d4edda;
            color: #155724;
        }
        .status-occupied {
            background-color: #f8d7da;
            color: #721c24;
        }
        .status-reserved {
            background-color: #fff3cd;
            color: #856404;
        }
        .table-details {
            margin-top: 10px;
            font-size: 0.85rem;
            color: #666;
        }
        .table-users {
            margin-top: 10px;
            font-size: 0.85rem;
        }
        .timer {
            margin-top: 10px;
            font-weight: bold;
            color: #dc3545;
        }
        .modal-body {
            max-height: 70vh;
            overflow-y: auto;
        }
        .nav-tabs {
            margin-bottom: 20px;
        }
        .time-slots {
            margin: 20px 0;
            text-align: center;
        }
        .time-slot-btn {
            margin: 5px;
        }
        .active-conversations {
            margin-top: 30px;
        }
        .spinner-border {
            width: 1rem;
            height: 1rem;
            border-width: 0.15em;
            margin-right: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>SparkUp Table Management</h1>
            <div>
                <button class="btn btn-primary" id="refreshBtn">
                    <span class="spinner-border d-none" id="refreshSpinner"></span>
                    Refresh Data
                </button>
                <a href="index.php" class="btn btn-secondary">Back to Admin</a>
            </div>
        </div>
        
        <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="classrooms-tab" data-bs-toggle="tab" data-bs-target="#classrooms" type="button" role="tab">Classrooms & Tables</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="active-tab" data-bs-toggle="tab" data-bs-target="#active" type="button" role="tab">Active Conversations</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="upcoming-tab" data-bs-toggle="tab" data-bs-target="#upcoming" type="button" role="tab">Upcoming Matches</button>
            </li>
        </ul>
        
        <div class="tab-content" id="myTabContent">
            <div class="tab-pane fade show active" id="classrooms" role="tabpanel">
                <div class="time-slots">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-outline-primary time-slot-btn active" data-time-slot="all">All Time Slots</button>
                        <button type="button" class="btn btn-outline-primary time-slot-btn" data-time-slot="09:00-09:30">9:00-9:30</button>
                        <button type="button" class="btn btn-outline-primary time-slot-btn" data-time-slot="09:30-10:00">9:30-10:00</button>
                        <button type="button" class="btn btn-outline-primary time-slot-btn" data-time-slot="10:00-10:30">10:00-10:30</button>
                        <button type="button" class="btn btn-outline-primary time-slot-btn" data-time-slot="10:30-11:00">10:30-11:00</button>
                    </div>
                </div>
                
                <div id="classroomsContainer">
                    <!-- Classrooms will be loaded here -->
                    <div class="text-center py-5">
                        <div class="spinner-border" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading classroom data...</p>
                    </div>
                </div>
            </div>
            
            <div class="tab-pane fade" id="active" role="tabpanel"></div>
                <div class="row">
                    <div class="col-12">
                        <div class="card"></div>
                            <div class="card-header">
                                <h5 class="card-title mb-0">Active Conversations</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive"></div>
                                    <table class="table table-hover" id="activeConversationsTable">
                                        <thead>
                                            <tr>
                                                <th>Table</th>
                                                <th>Participants</th>
                                                <th>Started At</th>
                                                <th>Time Remaining</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Active conversations will be loaded here -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="tab-pane fade" id="upcoming" role="tabpanel">
                <div class="row"></div>
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header"></div>
                                <h5 class="card-title mb-0">Upcoming Matches</h5>
                            </div>
                            <div class="card-body"></div>
                                <div class="table-responsive">
                                    <table class="table table-hover" id="upcomingMatchesTable">
                                        <thead>
                                            <tr></tr>
                                                <th>Time Slot</th>
                                                <th>Table</th>
                                                <th>Participants</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Upcoming matches will be loaded here -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal for Table Details -->
    <div class="modal fade" id="tableModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered"></div>
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Table Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="tableModalBody">
                    <!-- Table details will be loaded here -->
                </div>
                <div class="modal-footer"></div>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="saveTableChanges">Save Changes</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modal for Conversation Details -->
    <div class="modal fade" id="conversationModal" tabindex="-1" aria-hidden="true"></div>
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header"></div>
                    <h5 class="modal-title">Conversation Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="conversationModalBody">
                    <!-- Conversation details will be loaded here -->
                </div>
                <div class="modal-footer"></div>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-danger" id="endConversationBtn">End Conversation</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap and other scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Global variables
        let allTables = [];
        let activeTimeSlot = 'all';
        let classrooms = ['1001', '1002', 'ground', '2001'];
        let refreshInterval;
        const apiBase = '../api/table_management.php';
        
        // DOM ready function
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize the page
            loadAllData();
            
            // Set up refresh button
            document.getElementById('refreshBtn').addEventListener('click', function() {
                this.disabled = true;
                document.getElementById('refreshSpinner').classList.remove('d-none');
                loadAllData().finally(() => {
                    this.disabled = false;
                    document.getElementById('refreshSpinner').classList.add('d-none');
                });
            });
            
            // Set up time slot filtering
            document.querySelectorAll('.time-slot-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    document.querySelectorAll('.time-slot-btn').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    activeTimeSlot = this.dataset.timeSlot;
                    renderClassrooms();
                });
            });
            
            // Set up modal handlers
            document.getElementById('saveTableChanges').addEventListener('click', saveTableChanges);
            document.getElementById('endConversationBtn').addEventListener('click', endConversation);
            
            // Set up tab change handlers
            document.querySelectorAll('button[data-bs-toggle="tab"]').forEach(tab => {
                tab.addEventListener('shown.bs.tab', function(event) {
                    const targetId = event.target.getAttribute('data-bs-target').substring(1);
                    if (targetId === 'active') {
                        // Start automatic refresh when active tab is shown
                        startAutoRefresh();
                    } else {
                        // Stop automatic refresh when switching away
                        stopAutoRefresh();
                    }
                });
            });
            
            // Initial setup for active tab
            if (document.querySelector('#active-tab').classList.contains('active')) {
                startAutoRefresh();
            }
        });
        
        // Function to start auto-refresh for active conversations
        function startAutoRefresh() {
            stopAutoRefresh(); // Clear any existing interval
            refreshInterval = setInterval(() => {
                loadActiveConversations();
            }, 5000); // Refresh every 5 seconds
        }
        
        // Function to stop auto-refresh
        function stopAutoRefresh() {
            if (refreshInterval) {
                clearInterval(refreshInterval);
            }
        }
        
        // Load all data
        async function loadAllData() {
            try {
                await loadTables();
                await loadActiveConversations();
                await loadUpcomingMatches();
            } catch (error) {
                console.error('Error loading data:', error);
                showAlert('danger', 'Error loading data. Please try again later.');
            }
        }
        
        // Load tables data
        async function loadTables() {
            try {
                const response = await fetch(`${apiBase}?action=get_all_tables`);
                if (!response.ok) throw new Error('Network response was not ok');
                
                const data = await response.json();
                allTables = data;
                renderClassrooms();
                return data;
            } catch (error) {
                console.error('Error loading tables:', error);
                throw error;
            }
        }
        
        // Render classrooms and tables
        function renderClassrooms() {
            const container = document.getElementById('classroomsContainer');
            container.innerHTML = '';
            
            classrooms.forEach(classroom => {
                const classroomTables = allTables.filter(table => table.classroom === classroom);
                
                // Filter by time slot if needed
                let filteredTables = classroomTables;
                if (activeTimeSlot !== 'all') {
                    filteredTables = classroomTables.filter(table => {
                        return !table.time_slot || table.time_slot === activeTimeSlot;
                    });
                }
                
                if (filteredTables.length === 0) return; // Skip empty classrooms
                
                const classroomDiv = document.createElement('div');
                classroomDiv.className = 'classroom-container';
                classroomDiv.innerHTML = `
                    <h3 class="classroom-title">Classroom ${classroom}</h3>
                    <div class="tables-grid" id="classroom-${classroom}"></div>
                `;
                container.appendChild(classroomDiv);
                
                const tablesGrid = classroomDiv.querySelector('.tables-grid');
                filteredTables.sort((a, b) => a.table_number - b.table_number);
                
                filteredTables.forEach(table => {
                    const tableCard = document.createElement('div');
                    tableCard.className = `table-card ${table.status}`;
                    tableCard.dataset.tableId = table.id;
                    tableCard.dataset.tableNumber = table.table_number;
                    tableCard.dataset.classroom = table.classroom;
                    
                    let statusText = '';
                    let userInfo = '';
                    let timeInfo = '';
                    
                    switch (table.status) {
                        case 'available':
                            statusText = '<span class="table-status status-available">Available</span>';
                            break;
                        case 'occupied':
                            statusText = '<span class="table-status status-occupied">Occupied</span>';
                            if (table.user_email_1 && table.user_email_2) {
                                userInfo = `
                                    <div class="table-users">
                                        <div>${getUsernameFromEmail(table.user_email_1)}</div>
                                        <div>${getUsernameFromEmail(table.user_email_2)}</div>
                                    </div>
                                `;
                            }
                            if (table.start_time) {
                                const remainingTime = getRemainingTime(table.start_time);
                                if (remainingTime > 0) {
                                    timeInfo = `
                                        <div class="timer">
                                            ${formatTime(remainingTime)} remaining
                                        </div>
                                    `;
                                }
                            }
                            break;
                        case 'reserved':
                            statusText = '<span class="table-status status-reserved">Reserved</span>';
                            if (table.user_email_1 && table.user_email_2) {
                                userInfo = `
                                    <div class="table-users">
                                        <div>${getUsernameFromEmail(table.user_email_1)}</div>
                                        <div>${getUsernameFromEmail(table.user_email_2)}</div>
                                    </div>
                                `;
                            }
                            if (table.time_slot) {
                                timeInfo = `
                                    <div class="table-details">
                                        <div>Time: ${table.time_slot}</div>
                                    </div>
                                `;
                            }
                            break;
                    }
                    
                    tableCard.innerHTML = `
                        <div class="table-number">Table ${table.table_number}</div>
                        ${statusText}
                        ${userInfo}
                        ${timeInfo}
                    `;
                    
                    tableCard.addEventListener('click', () => showTableModal(table));
                    tablesGrid.appendChild(tableCard);
                });
            });
        }
        
        // Load active conversations
        async function loadActiveConversations() {
            try {
                const response = await fetch(`${apiBase}?action=get_active_conversations`);
                if (!response.ok) throw new Error('Network response was not ok');
                
                const data = await response.json();
                renderActiveConversations(data);
                return data;
            } catch (error) {
                console.error('Error loading active conversations:', error);
                throw error;
            }
        }
        
        // Load upcoming matches
        async function loadUpcomingMatches() {
            try {
                const response = await fetch(`${apiBase}?action=get_upcoming_conversations`);
                if (!response.ok) throw new Error('Network response was not ok');
                
                const data = await response.json();
                renderUpcomingMatches(data);
                return data;
            } catch (error) {
                console.error('Error loading upcoming matches:', error);
                throw error;
            }
        }
        
        // Render active conversations table
        function renderActiveConversations(conversations) {
            const tbody = document.querySelector('#activeConversationsTable tbody');
            tbody.innerHTML = '';
            
            if (conversations.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="5" class="text-center py-4">No active conversations</td>
                    </tr>
                `;
                return;
            }
            
            conversations.forEach(conv => {
                const user1Name = conv.user1_first_name ? `${conv.user1_first_name} ${conv.user1_last_name}` : getUsernameFromEmail(conv.user_email_1);
                const user2Name = conv.user2_first_name ? `${conv.user2_first_name} ${conv.user2_last_name}` : getUsernameFromEmail(conv.user_email_2);
                const startTime = new Date(conv.start_time).toLocaleTimeString();
                const remainingSeconds = conv.remaining_seconds > 0 ? conv.remaining_seconds : 0;
                
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>Room ${conv.classroom}, Table ${conv.table_number}</td>
                    <td>${user1Name}<br>${user2Name}</td>
                    <td>${startTime}</td>
                    <td><span class="badge ${remainingSeconds < 60 ? 'bg-danger' : 'bg-success'}">${formatTime(remainingSeconds)}</span></td>
                    <td>
                        <button class="btn btn-sm btn-primary view-conversation" data-id="${conv.id}">View</button>
                        <button class="btn btn-sm btn-danger end-conversation" data-id="${conv.id}" 
                        data-user1="${conv.user_email_1}" data-user2="${conv.user_email_2}">End</button>
                    </td>
                `;
                
                row.querySelector('.view-conversation').addEventListener('click', () => {
                    showConversationModal(conv);
                });
                
                row.querySelector('.end-conversation').addEventListener('click', () => {
                    if (confirm(`Are you sure you want to end the conversation between ${user1Name} and ${user2Name}?`)) {
                        endConversationByAdmin(conv.user_email_1, conv.user_email_2);
                    }
                });
                
                tbody.appendChild(row);
            });
        }
        
        // Render upcoming matches table
        function renderUpcomingMatches(matches) {
            const tbody = document.querySelector('#upcomingMatchesTable tbody');
            tbody.innerHTML = '';
            
            if (matches.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="4" class="text-center py-4">No upcoming matches</td>
                    </tr>
                `;
                return;
            }
            
            matches.forEach(match => {
                const user1Name = match.user1_first_name ? `${match.user1_first_name} ${match.user1_last_name}` : getUsernameFromEmail(match.user_email_1);
                const user2Name = match.user2_first_name ? `${match.user2_first_name} ${match.user2_last_name}` : getUsernameFromEmail(match.user_email_2);
                
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${match.time_slot}</td>
                    <td>Room ${match.classroom}, Table ${match.table_number}</td>
                    <td>${user1Name}<br>${user2Name}</td>
                    <td>
                        <button class="btn btn-sm btn-secondary change-table" data-id="${match.id}">Change Table</button>
                    </td>
                `;
                
                row.querySelector('.change-table').addEventListener('click', () => {
                    showChangeTableModal(match);
                });
                
                tbody.appendChild(row);
            });
        }
        
        // Show table details modal
        function showTableModal(table) {
            const modal = new bootstrap.Modal(document.getElementById('tableModal'));
            const modalBody = document.getElementById('tableModalBody');
            
            modalBody.innerHTML = `
                <h4>Table ${table.table_number} in Classroom ${table.classroom}</h4>
                <p>Current Status: <strong>${table.status}</strong></p>
                
                <div class="mb-3">
                    <label class="form-label">Change Status:</label>
                    <select class="form-select" id="tableStatusSelect">
                        <option value="available" ${table.status === 'available' ? 'selected' : ''}>Available</option>
                        <option value="occupied" ${table.status === 'occupied' ? 'selected' : ''}>Occupied</option>
                        <option value="reserved" ${table.status === 'reserved' ? 'selected' : ''}>Reserved</option>
                    </select>
                </div>
                
                <input type="hidden" id="modalTableId" value="${table.id}">
                
                ${table.user_email_1 && table.user_email_2 ? `
                    <div class="alert alert-info">
                        <strong>Assigned to:</strong>
                        <ul>
                            <li>${getUsernameFromEmail(table.user_email_1)}</li>
                            <li>${getUsernameFromEmail(table.user_email_2)}</li>
                        </ul>
                        ${table.time_slot ? `<p>Time Slot: ${table.time_slot}</p>` : ''}
                    </div>
                ` : ''}
            `;
            
            modal.show();
        }
        
        // Show conversation modal
        function showConversationModal(conversation) {
            const modal = new bootstrap.Modal(document.getElementById('conversationModal'));
            const modalBody = document.getElementById('conversationModalBody');
            
            const user1Name = conversation.user1_first_name ? `${conversation.user1_first_name} ${conversation.user1_last_name}` : getUsernameFromEmail(conversation.user_email_1);
            const user2Name = conversation.user2_first_name ? `${conversation.user2_first_name} ${conversation.user2_last_name}` : getUsernameFromEmail(conversation.user_email_2);
            const startTime = new Date(conversation.start_time).toLocaleString();
            const remainingSeconds = conversation.remaining_seconds > 0 ? conversation.remaining_seconds : 0;
            
            modalBody.innerHTML = `
                <h4>Conversation Details</h4>
                <p><strong>Location:</strong> Room ${conversation.classroom}, Table ${conversation.table_number}</p>
                <p><strong>Participants:</strong></p>
                <ul>
                    <li>${user1Name}</li>
                    <li>${user2Name}</li>
                </ul>
                <p><strong>Started:</strong> ${startTime}</p>
                <p><strong>Time Remaining:</strong> <span class="badge bg-${remainingSeconds < 60 ? 'danger' : 'success'}">${formatTime(remainingSeconds)}</span></p>
                
                <input type="hidden" id="conversationId" value="${conversation.id}">
                <input type="hidden" id="conversationUser1" value="${conversation.user_email_1}">
                <input type="hidden" id="conversationUser2" value="${conversation.user_email_2}">
            `;
            
            modal.show();
        }
        
        // Show change table modal
        function showChangeTableModal(match) {
            const modal = new bootstrap.Modal(document.getElementById('tableModal'));
            const modalBody = document.getElementById('tableModalBody');
            
            const user1Name = match.user1_first_name ? `${match.user1_first_name} ${match.user1_last_name}` : getUsernameFromEmail(match.user_email_1);
            const user2Name = match.user2_first_name ? `${match.user2_first_name} ${match.user2_last_name}` : getUsernameFromEmail(match.user_email_2);
            
            modalBody.innerHTML = `
                <h4>Change Table for Match</h4>
                <p><strong>Current Location:</strong> Room ${match.classroom}, Table ${match.table_number}</p>
                <p><strong>Time Slot:</strong> ${match.time_slot}</p>
                <p><strong>Participants:</strong></p>
                <ul>
                    <li>${user1Name}</li>
                    <li>${user2Name}</li>
                </ul>
                
                <div class="mb-3">
                    <label class="form-label">Select New Table:</label>
                    <select class="form-select" id="newTableSelect">
                        <option value="">Loading tables...</option>
                    </select>
                </div>
                
                <input type="hidden" id="assignmentId" value="${match.id}">
            `;
            
            modal.show();
            
            // Load available tables for this classroom
            loadAvailableTables(match.classroom).then(tables => {
                const select = document.getElementById('newTableSelect');
                select.innerHTML = '';
                
                if (tables.length === 0) {
                    select.innerHTML = '<option value="">No available tables</option>';
                    return;
                }
                
                tables.forEach(table => {
                    const option = document.createElement('option');
                    option.value = table.id;
                    option.textContent = `Table ${table.table_number}`;
                    if (table.id === match.table_id) {
                        option.selected = true;
                    }
                    select.appendChild(option);
                });
            });
        }
        
        // Save table changes
        async function saveTableChanges() {
            const tableId = document.getElementById('modalTableId').value;
            const statusSelect = document.getElementById('tableStatusSelect');
            const status = statusSelect.value;
            
            const assignmentId = document.getElementById('assignmentId')?.value;
            const newTableId = document.getElementById('newTableSelect')?.value;
            
            try {
                if (assignmentId && newTableId) {
                    // Change table assignment
                    const response = await fetch(`${apiBase}?action=change_table`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            assignment_id: assignmentId,
                            new_table_id: newTableId
                        })
                    });
                    
                    if (!response.ok) throw new Error('Network response was not ok');
                    
                    showAlert('success', 'Table assignment changed successfully');
                } else {
                    // Update table status
                    const response = await fetch(`${apiBase}?action=update_table_status`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            table_id: tableId,
                            status: status
                        })
                    });
                    
                    if (!response.ok) throw new Error('Network response was not ok');
                    
                    showAlert('success', 'Table status updated successfully');
                }
                
                // Close modal and refresh data
                bootstrap.Modal.getInstance(document.getElementById('tableModal')).hide();
                loadAllData();
                
            } catch (error) {
                console.error('Error saving changes:', error);
                showAlert('danger', 'Error saving changes. Please try again.');
            }
        }
        
        // End conversation from modal
        async function endConversation() {
            const user1 = document.getElementById('conversationUser1').value;
            const user2 = document.getElementById('conversationUser2').value;
            
            try {
                await endConversationByAdmin(user1, user2);
                bootstrap.Modal.getInstance(document.getElementById('conversationModal')).hide();
            } catch (error) {
                console.error('Error ending conversation:', error);
                showAlert('danger', 'Error ending conversation. Please try again.');
            }
        }
        
        // End conversation by admin
        async function endConversationByAdmin(user1, user2) {
            try {
                const response = await fetch(`${apiBase}?action=end_conversation`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        user_email: user1,
                        match_email: user2
                    })
                });
                
                if (!response.ok) throw new Error('Network response was not ok');
                
                showAlert('success', 'Conversation ended successfully');
                loadAllData();
                
                return true;
            } catch (error) {
                console.error('Error ending conversation:', error);
                showAlert('danger', 'Error ending conversation. Please try again.');
                throw error;
            }
        }
        
        // Load available tables for changing table assignment
        async function loadAvailableTables(classroom) {
            try {
                const response = await fetch(`${apiBase}?action=get_classroom_tables&classroom=${classroom}`);
                if (!response.ok) throw new Error('Network response was not ok');
                
                const data = await response.json();
                return data;
            } catch (error) {
                console.error('Error loading tables:', error);
                showAlert('danger', 'Error loading available tables.');
                return [];
            }
        }
        
        // Helper functions
        function formatTime(seconds) {
            const minutes = Math.floor(seconds / 60);
            const secs = seconds % 60;
            return `${minutes}:${secs.toString().padStart(2, '0')}`;
        }
        
        function getRemainingTime(startTime) {
            const start = new Date(startTime);
            const now = new Date();
            const elapsedSeconds = Math.floor((now - start) / 1000);
            // 5 minutes (300 seconds) is the conversation duration
            return Math.max(0, 300 - elapsedSeconds);
        }
        
        function getUsernameFromEmail(email) {
            return email.split('@')[0];
        }
        
        // Show alert message
        function showAlert(type, message) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
            alertDiv.role = 'alert';
            alertDiv.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;
            
            const container = document.querySelector('.container-fluid');
            container.insertBefore(alertDiv, container.firstChild);
            
            // Auto dismiss after 5 seconds
            setTimeout(() => {
                alertDiv.classList.remove('show');
                setTimeout(() => alertDiv.remove(), 150);
            }, 5000);
        }
    </script>
</body>
</html>