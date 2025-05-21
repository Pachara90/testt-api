$(document).ready(function() {
    // Initialize toasts
    const successToast = new bootstrap.Toast(document.getElementById('successToast'));
    const errorToast = new bootstrap.Toast(document.getElementById('errorToast'));
    
    // API Endpoints
    const API = {
        events: 'http://localhost/seminar/api/events.php',
        participants: 'http://localhost/seminar/api/participants.php'
    };
    
    // Global variables
    let events = [];
    let participants = [];
    let currentView = 'dashboard';
    
    // ======================
    // Event Management Functions
    // ======================
    
    // Fetch all events
    async function fetchEvents() {
        showLoading(true);
        try {
            const response = await $.ajax({
                url: API.events,
                type: 'GET',
                dataType: 'json'
            });
            
            events = response;
            return events;
        } catch (error) {
            showError('เกิดข้อผิดพลาดในการโหลดข้อมูลกิจกรรม');
            console.error('Error fetching events:', error);
            return [];
        } finally {
            showLoading(false);
        }
    }
    
    // Create a new event
    async function createEvent(eventData) {
        showLoading(true);
        try {
            const response = await $.ajax({
                url: API.events,
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(eventData)
            });
            
            if (response.success) {
                showSuccess('สร้างกิจกรรมใหม่เรียบร้อยแล้ว');
                return true;
            } else {
                showError(response.error || 'ไม่สามารถสร้างกิจกรรมได้');
                return false;
            }
        } catch (error) {
            const errorMsg = error.responseJSON?.error || 'เกิดข้อผิดพลาดในการสร้างกิจกรรม';
            showError(errorMsg);
            console.error('Error creating event:', error);
            return false;
        } finally {
            showLoading(false);
        }
    }
    
    // Update an event
    async function updateEvent(eventId, eventData) {
        showLoading(true);
        try {
            const response = await $.ajax({
                url: `${API.events}?id=${eventId}`,
                type: 'PUT',
                contentType: 'application/json',
                data: JSON.stringify(eventData)
            });
            
            if (response.success) {
                showSuccess('อัปเดตกิจกรรมเรียบร้อยแล้ว');
                return true;
            } else {
                showError(response.error || 'ไม่สามารถอัปเดตกิจกรรมได้');
                return false;
            }
        } catch (error) {
            const errorMsg = error.responseJSON?.error || 'เกิดข้อผิดพลาดในการอัปเดตกิจกรรม';
            showError(errorMsg);
            console.error('Error updating event:', error);
            return false;
        } finally {
            showLoading(false);
        }
    }
    
    // Delete an event
    async function deleteEvent(eventId) {
        showLoading(true);
        try {
            const response = await $.ajax({
                url: `${API.events}?id=${eventId}`,
                type: 'DELETE'
            });
            
            if (response.success) {
                showSuccess('ลบกิจกรรมเรียบร้อยแล้ว');
                return true;
            } else {
                showError(response.error || 'ไม่สามารถลบกิจกรรมได้');
                return false;
            }
        } catch (error) {
            const errorMsg = error.responseJSON?.error || 'เกิดข้อผิดพลาดในการลบกิจกรรม';
            showError(errorMsg);
            console.error('Error deleting event:', error);
            return false;
        } finally {
            showLoading(false);
        }
    }
    
    // Render events table
    async function renderEventsTable() {
        showLoading(true);
        try {
            await fetchEvents();
            const tbody = $('#events-table-body');
            tbody.empty();
            
            if (events.length === 0) {
                tbody.append('<tr><td colspan="5" class="text-center">ไม่พบข้อมูลกิจกรรม</td></tr>');
                return;
            }
            
            // Get current participants count for each event
            const participantsCount = {};
            const participantsResponse = await $.ajax({
                url: API.participants,
                type: 'GET',
                dataType: 'json'
            });
            
            participantsResponse.forEach(p => {
                participantsCount[p.event_id] = (participantsCount[p.event_id] || 0) + 1;
            });
            
            events.forEach(event => {
                const eventDate = new Date(event.date).toLocaleDateString('th-TH');
                const currentParticipants = participantsCount[event.id] || 0;
                const status = currentParticipants >= event.max_participants ? 'เต็ม' : 'ว่าง';
                const statusClass = currentParticipants >= event.max_participants ? 'status-full' : 'status-available';
                
                tbody.append(`
                    <tr>
                        <td>${event.title}</td>
                        <td>${event.description.substring(0, 50)}${event.description.length > 50 ? '...' : ''}</td>
                        <td>${eventDate}</td>
                        <td>${currentParticipants} / ${event.max_participants}</td>
                        <td>
                            <span class="status-badge ${statusClass}">${status}</span>
                            <button class="btn btn-sm btn-primary edit-event-btn" data-id="${event.id}">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger delete-event-btn" data-id="${event.id}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `);
            });
            
            // Attach event handlers
            $('.edit-event-btn').click(function() {
                const eventId = $(this).data('id');
                loadEventForEdit(eventId);
            });
            
            $('.delete-event-btn').click(function() {
                const eventId = $(this).data('id');
                showDeleteConfirmation(eventId, 'event');
            });
            
        } catch (error) {
            showError('เกิดข้อผิดพลาดในการโหลดข้อมูลกิจกรรม');
            console.error('Error rendering events:', error);
        } finally {
            showLoading(false);
        }
    }
    
    // Load event data for editing
    async function loadEventForEdit(eventId) {
        showLoading(true);
        try {
            const event = await $.ajax({
                url: `${API.events}?id=${eventId}`,
                type: 'GET',
                dataType: 'json'
            });
            
            if (event) {
                $('#event-id').val(event.id);
                $('#event-title').val(event.title);
                $('#event-description').val(event.description);
                $('#event-date').val(event.date.split(' ')[0]);
                $('#event-max-participants').val(event.max_participants);
                
                switchView('edit-event');
            }
        } catch (error) {
            showError('เกิดข้อผิดพลาดในการโหลดข้อมูลกิจกรรม');
            console.error('Error loading event:', error);
        } finally {
            showLoading(false);
        }
    }
    
    // ======================
    // Participant Management Functions
    // ======================
    
    // Fetch all participants
    async function fetchParticipants(eventId = '') {
        showLoading(true);
        try {
            let url = API.participants;
            if (eventId) {
                url += `?event_id=${eventId}`;
            }
            
            const response = await $.ajax({
                url: url,
                type: 'GET',
                dataType: 'json'
            });
            
            participants = response;
            return participants;
        } catch (error) {
            showError('เกิดข้อผิดพลาดในการโหลดข้อมูลผู้เข้าร่วม');
            console.error('Error fetching participants:', error);
            return [];
        } finally {
            showLoading(false);
        }
    }
    
    // Create a new participant
    async function createParticipant(participantData) {
        showLoading(true);
        try {
            const response = await $.ajax({
                url: API.participants,
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(participantData)
            });
            
            if (response.success) {
                showSuccess('เพิ่มผู้เข้าร่วมงานเรียบร้อยแล้ว');
                return true;
            } else {
                showError(response.error || 'ไม่สามารถเพิ่มผู้เข้าร่วมได้');
                return false;
            }
        } catch (error) {
            const errorMsg = error.responseJSON?.error || 'เกิดข้อผิดพลาดในการเพิ่มผู้เข้าร่วม';
            showError(errorMsg);
            console.error('Error creating participant:', error);
            return false;
        } finally {
            showLoading(false);
        }
    }
    
    // Delete a participant
    async function deleteParticipant(participantId) {
        showLoading(true);
        try {
            const response = await $.ajax({
                url: `${API.participants}?id=${participantId}`,
                type: 'DELETE'
            });
            
            if (response.success) {
                showSuccess('ลบผู้เข้าร่วมเรียบร้อยแล้ว');
                return true;
            } else {
                showError(response.error || 'ไม่สามารถลบผู้เข้าร่วมได้');
                return false;
            }
        } catch (error) {
            const errorMsg = error.responseJSON?.error || 'เกิดข้อผิดพลาดในการลบผู้เข้าร่วม';
            showError(errorMsg);
            console.error('Error deleting participant:', error);
            return false;
        } finally {
            showLoading(false);
        }
    }
    
    // Render participants table
    async function renderParticipantsTable(eventId = '') {
        showLoading(true);
        try {
            await fetchParticipants(eventId);
            await fetchEvents(); // Ensure we have events data
            
            const tbody = $('#participants-table-body');
            tbody.empty();
            
            if (participants.length === 0) {
                tbody.append('<tr><td colspan="5" class="text-center">ไม่พบข้อมูลผู้เข้าร่วม</td></tr>');
                return;
            }
            
            participants.forEach(participant => {
                const event = events.find(e => e.id == participant.event_id) || { title: 'ไม่ทราบ' };
                
                tbody.append(`
                    <tr>
                        <td>${participant.fullname}</td>
                        <td>${participant.email}</td>
                        <td>${participant.phone || '-'}</td>
                        <td>${event.title}</td>
                        <td>
                            <button class="btn btn-sm btn-danger delete-participant-btn" data-id="${participant.id}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `);
            });
            
            // Attach delete handlers
            $('.delete-participant-btn').click(function() {
                const participantId = $(this).data('id');
                showDeleteConfirmation(participantId, 'participant');
            });
            
        } catch (error) {
            showError('เกิดข้อผิดพลาดในการโหลดข้อมูลผู้เข้าร่วม');
            console.error('Error rendering participants:', error);
        } finally {
            showLoading(false);
        }
    }
    
    // Populate event dropdowns
    async function populateEventDropdowns() {
        try {
            await fetchEvents();
            
            // For participant form
            const eventDropdown = $('#event-id');
            eventDropdown.empty();
            eventDropdown.append('<option value="" selected disabled>เลือกงานสัมมนา</option>');
            
            // For participant filter
            const eventFilter = $('#event-filter');
            eventFilter.empty();
            eventFilter.append('<option value="">ทั้งหมด</option>');
            
            events.forEach(event => {
                eventDropdown.append(`<option value="${event.id}">${event.title}</option>`);
                eventFilter.append(`<option value="${event.id}">${event.title}</option>`);
            });
            
        } catch (error) {
            showError('เกิดข้อผิดพลาดในการโหลดข้อมูลกิจกรรม');
            console.error('Error populating event dropdowns:', error);
        }
    }
    
    // ======================
    // Dashboard Functions
    // ======================
    
    // Update dashboard statistics
    async function updateDashboardStats() {
        showLoading(true);
        try {
            await fetchEvents();
            await fetchParticipants();
            
            // Total participants
            $('#total-participants').text(participants.length);
            
            // Total events
            $('#total-events').text(events.length);
            
            // Events that are full or nearly full
            const participantsCount = {};
            participants.forEach(p => {
                participantsCount[p.event_id] = (participantsCount[p.event_id] || 0) + 1;
            });
            
            let fullEvents = 0;
            events.forEach(event => {
                const currentParticipants = participantsCount[event.id] || 0;
                if (currentParticipants >= event.max_participants) {
                    fullEvents++;
                }
            });
            
            $('#full-events').text(fullEvents);
            
            // Recent events (last 5)
            const recentEvents = events.slice(0, 5);
            const recentEventsTable = $('#recent-events');
            recentEventsTable.empty();
            
            recentEvents.forEach(event => {
                const eventDate = new Date(event.date).toLocaleDateString('th-TH');
                const currentParticipants = participantsCount[event.id] || 0;
                const status = currentParticipants >= event.max_participants ? 'เต็ม' : 'ว่าง';
                const statusClass = currentParticipants >= event.max_participants ? 'status-full' : 'status-available';
                
                recentEventsTable.append(`
                    <tr>
                        <td>${event.title}</td>
                        <td>${eventDate}</td>
                        <td><span class="status-badge ${statusClass}">${status}</span></td>
                    </tr>
                `);
            });
            
            // Recent participants (last 5)
            const recentParticipants = participants.slice(0, 5);
            const recentParticipantsTable = $('#recent-participants');
            recentParticipantsTable.empty();
            
            recentParticipants.forEach(participant => {
                const event = events.find(e => e.id == participant.event_id) || { title: 'ไม่ทราบ' };
                recentParticipantsTable.append(`
                    <tr>
                        <td>${participant.fullname}</td>
                        <td>${event.title}</td>
                        <td>${new Date().toLocaleDateString('th-TH')}</td>
                    </tr>
                `);
            });
            
        } catch (error) {
            showError('เกิดข้อผิดพลาดในการโหลดข้อมูลแดชบอร์ด');
            console.error('Error updating dashboard stats:', error);
        } finally {
            showLoading(false);
        }
    }
    
    // ======================
    // UI Functions
    // ======================
    
    // Show loading indicator
    function showLoading(show) {
        if (show) {
            $('#loading-indicator').show();
        } else {
            $('#loading-indicator').hide();
        }
    }
    
    // Show success message
    function showSuccess(message) {
        $('#success-message').text(message);
        successToast.show();
    }
    
    // Show error message
    function showError(message) {
        $('#error-message').text(message);
        errorToast.show();
    }
    
    // Show delete confirmation
    function showDeleteConfirmation(id, type) {
        $('#deleteModal').data('id', id);
        $('#deleteModal').data('type', type);
        
        let message = '';
        if (type === 'event') {
            const event = events.find(e => e.id == id);
            message = `คุณแน่ใจหรือไม่ว่าต้องการลบงานสัมมนา: <strong>${event.title}</strong>`;
        } else {
            const participant = participants.find(p => p.id == id);
            message = `คุณแน่ใจหรือไม่ว่าต้องการลบผู้เข้าร่วม: <strong>${participant.fullname}</strong>`;
        }
        
        $('#deleteModal .modal-body').html(message);
        $('#deleteModal').modal('show');
    }
    
    // Switch between views
    function switchView(view) {
        currentView = view;
        $('.nav-link').removeClass('active');
        
        // Hide all content sections
        $('#dashboard-content, #events-content, #participants-content, #add-event-content, #add-participant-content, #edit-event-content').hide();
        
        switch(view) {
            case 'dashboard':
                $('#dashboard-link').addClass('active');
                $('#dashboard-content').show();
                $('#page-title').text('แดชบอร์ด');
                updateDashboardStats();
                break;
                
            case 'events':
                $('#events-link').addClass('active');
                $('#events-content').show();
                $('#page-title').text('งานสัมมนาทั้งหมด');
                renderEventsTable();
                break;
                
            case 'participants':
                $('#participants-link').addClass('active');
                $('#participants-content').show();
                $('#page-title').text('ผู้เข้าร่วมทั้งหมด');
                renderParticipantsTable();
                break;
                
            case 'add-event':
                $('#add-event-link').addClass('active');
                $('#add-event-content').show();
                $('#page-title').text('เพิ่มงานสัมมนาใหม่');
                $('#event-form')[0].reset();
                $('#event-id').val('');
                break;
                
            case 'add-participant':
                $('#add-participant-link').addClass('active');
                $('#add-participant-content').show();
                $('#page-title').text('เพิ่มผู้เข้าร่วมงาน');
                $('#participant-form')[0].reset();
                break;
                
            case 'edit-event':
                $('#events-link').addClass('active');
                $('#edit-event-content').show();
                $('#page-title').text('แก้ไขงานสัมมนา');
                break;
        }
    }
    
    // ======================
    // Event Handlers
    // ======================
    
    // Navigation handlers
    $('#dashboard-link').click(() => switchView('dashboard'));
    $('#events-link').click(() => switchView('events'));
    $('#participants-link').click(() => switchView('participants'));
    $('#add-event-link').click(() => switchView('add-event'));
    $('#add-participant-link').click(() => switchView('add-participant'));
    
    // Event filter change
    $('#event-filter').change(function() {
        const eventId = $(this).val();
        renderParticipantsTable(eventId);
    });
    
    // Search participant
    $('#search-participant').keyup(function() {
        const searchTerm = $(this).val().toLowerCase();
        $('#participants-table-body tr').filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(searchTerm) > -1);
        });
    });
    
    // Event form submission
    $('#event-form').submit(async function(e) {
        e.preventDefault();
        
        const eventData = {
            title: $('#event-title').val().trim(),
            description: $('#event-description').val().trim(),
            date: $('#event-date').val(),
            max_participants: $('#event-max-participants').val()
        };
        
        // Validate form
        if (!eventData.title || !eventData.description || !eventData.date || !eventData.max_participants) {
            showError('กรุณากรอกข้อมูลให้ครบถ้วน');
            return;
        }
        
        if (eventData.max_participants <= 0) {
            showError('จำนวนผู้เข้าร่วมต้องมากกว่า 0');
            return;
        }
                const eventId = $('#event-id').val();
        let success = false;
        
        if (eventId) {
            // Update existing event
            success = await updateEvent(eventId, eventData);
        } else {
            // Create new event
            success = await createEvent(eventData);
        }
        
        if (success) {
            $('#event-form')[0].reset();
            $('#event-id').val('');
            switchView('events');
        }
    });
    
    // Participant form submission
    $('#participant-form').submit(async function(e) {
        e.preventDefault();
        
        const participantData = {
            event_id: $('#event-id').val(),
            fullname: $('#fullname').val().trim(),
            email: $('#email').val().trim(),
            phone: $('#phone').val().trim()
        };
        
        // Validate form
        if (!participantData.event_id) {
            showError('กรุณาเลือกงานสัมมนา');
            return;
        }
        
        if (!participantData.fullname) {
            showError('กรุณากรอกชื่อ-สกุล');
            return;
        }
        
        if (!participantData.email) {
            showError('กรุณากรอกอีเมล');
            return;
        }
        
        // Simple email validation
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(participantData.email)) {
            showError('รูปแบบอีเมลไม่ถูกต้อง');
            return;
        }
        
        const success = await createParticipant(participantData);
        if (success) {
            $('#participant-form')[0].reset();
            switchView('participants');
        }
    });
    
    // Delete confirmation handler
    $('#confirm-delete').click(async function() {
        const id = $('#deleteModal').data('id');
        const type = $('#deleteModal').data('type');
        let success = false;
        
        if (type === 'event') {
            success = await deleteEvent(id);
            if (success) {
                switchView('events');
            }
        } else {
            success = await deleteParticipant(id);
            if (success) {
                renderParticipantsTable($('#event-filter').val());
            }
        }
        
        $('#deleteModal').modal('hide');
    });
    
    // ======================
    // Initialization
    // ======================
    
    async function init() {
        showLoading(true);
        
        try {
            // Load initial data
            await populateEventDropdowns();
            await updateDashboardStats();
            
            // Set default view
            switchView(currentView);
            
        } catch (error) {
            showError('เกิดข้อผิดพลาดในการโหลดข้อมูลเริ่มต้น');
            console.error('Initialization error:', error);
        } finally {
            showLoading(false);
        }
    }
    
    // Initialize the application
    init();
});