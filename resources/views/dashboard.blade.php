@extends('layouts.app')

@section('styles')
<!-- CSRF Token -->
<meta name="csrf-token" content="{{ csrf_token() }}">
<!-- FullCalendar CDN -->
<link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.css' rel='stylesheet' />
<style>
    .dashboard-container {
        display: flex;
        gap: 1.5rem;
        min-height: calc(100vh - 100px);
    }

    .calendar-container {
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .widgets-container {
        width: 350px;
        flex-shrink: 0;
    }

    #calendar {
        background: white;
        padding: 2rem;
        border-radius: 1.5rem;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
    }
    
    /* Başlık Stili */
    .fc-toolbar-title {
        font-size: 1.8em !important;
        font-weight: 700 !important;
        background: linear-gradient(135deg, #4158d0, #c850c0);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        text-transform: capitalize;
    }
    
    /* Buton Stilleri */
    .fc-button-primary {
        background: linear-gradient(135deg, #4158d0, #c850c0) !important;
        border: none !important;
        border-radius: 12px !important;
        padding: 0.8rem 1.5rem !important;
        font-weight: 600 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.5px !important;
        box-shadow: 0 4px 15px rgba(65, 88, 208, 0.3) !important;
        transition: all 0.3s ease !important;
    }
    
    .fc-button-primary:hover {
        transform: translateY(-2px) !important;
        box-shadow: 0 8px 20px rgba(65, 88, 208, 0.4) !important;
    }
    
    .fc-button-primary:not(:disabled):active,
    .fc-button-primary:not(:disabled).fc-button-active {
        background: linear-gradient(135deg, #3b5998 0%, #4158d0 100%) !important;
        border: none !important;
    }
    
    /* Tablo Başlık Stili */
    .fc-col-header {
        background-color: #f8f9fa;
    }
    
    .fc-col-header-cell {
        padding: 1rem 0 !important;
    }
    
    .fc-col-header-cell-cushion {
        color: #6c757d !important;
        font-weight: 600 !important;
        text-decoration: none !important;
    }
    
    /* Günler Stili */
    .fc-daygrid-day {
        transition: all 0.2s ease;
    }
    
    .fc-daygrid-day:hover {
        background-color: #f8f9fa;
    }
    
    .fc-daygrid-day-number {
        color: #495057 !important;
        text-decoration: none !important;
        font-weight: 500 !important;
        padding: 0.5rem !important;
    }
    
    /* Bugün Stili */
    .fc-day-today {
        background: linear-gradient(135deg, rgba(65, 88, 208, 0.05), rgba(200, 80, 192, 0.05)) !important;
    }
    
    .fc-day-today .fc-daygrid-day-number {
        background: linear-gradient(135deg, #4158d0, #3b5998);
        color: white !important;
        border-radius: 50%;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0.5rem;
    }
    
    /* Etkinlik ve Görev Ortak Stilleri */
    .fc-event {
        border: none !important;
        padding: 0.5rem !important;
        margin: 2px 0 !important;
        border-radius: 6px !important;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1) !important;
        transition: all 0.2s ease !important;
    }

    .fc-event:hover {
        transform: translateY(-3px) !important;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15) !important;
    }

    /* Takvim Hücre Stilleri */
    .fc-daygrid-day-frame {
        min-height: 120px !important;
        max-height: 120px !important;
        overflow-y: auto !important;
    }

    .fc-daygrid-day-events {
        padding: 2px !important;
    }

    /* Scrollbar Stilleri */
    .fc-daygrid-day-frame::-webkit-scrollbar {
        width: 4px;
    }

    .fc-daygrid-day-frame::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 2px;
    }

    .fc-daygrid-day-frame::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 2px;
    }

    .fc-daygrid-day-frame::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }

    /* Etkinlik Stilleri */
    .calendar-event {
        background: white !important;
        border: 1px solid #4158d0 !important;
        border-radius: 4px !important;
        padding: 2px 6px !important;
        margin: 1px 0 !important;
        box-shadow: 0 1px 3px rgba(65, 88, 208, 0.1) !important;
        transition: all 0.2s ease !important;
        font-size: 0.85em !important;
    }

    .calendar-event .fc-event-title,
    .calendar-event .fc-event-time {
        color: #4158d0 !important;
        font-weight: 500 !important;
    }

    .calendar-event::before {
        content: '';
        display: inline-block;
        width: 6px;
        height: 6px;
        background: #4158d0;
        border-radius: 50%;
        margin-right: 4px;
        vertical-align: middle;
    }

    .calendar-event:hover {
        background: #4158d0 !important;
        border-color: transparent !important;
    }

    .calendar-event:hover .fc-event-title,
    .calendar-event:hover .fc-event-time {
        color: white !important;
    }

    .calendar-event.fc-event-past {
        border-color: #8e9aaf !important;
        opacity: 0.7;
    }

    .calendar-event.fc-event-past::before {
        background: #8e9aaf;
    }

    .calendar-event.fc-event-past .fc-event-title,
    .calendar-event.fc-event-past .fc-event-time {
        color: #8e9aaf !important;
    }

    .calendar-event.fc-event-allday {
        border-color: #00b4db !important;
        background: rgba(0, 180, 219, 0.05) !important;
    }

    .calendar-event.fc-event-allday::before {
        background: #00b4db;
    }

    .calendar-event.fc-event-allday .fc-event-title,
    .calendar-event.fc-event-allday .fc-event-time {
        color: #00b4db !important;
    }

    /* Görev Stilleri */
    .calendar-task {
        border: none !important;
        border-radius: 4px !important;
        padding: 2px 6px !important;
        margin: 1px 0 !important;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1) !important;
        transition: all 0.2s ease !important;
        font-size: 0.85em !important;
    }

    .calendar-task .fc-event-title,
    .calendar-task .fc-event-time {
        color: white !important;
        font-weight: 500 !important;
    }

    .calendar-task::before {
        content: '';
        display: inline-block;
        width: 6px;
        height: 6px;
        background: rgba(255, 255, 255, 0.8);
        border-radius: 50%;
        margin-right: 4px;
        vertical-align: middle;
    }

    /* Görev Öncelik Stilleri */
    .calendar-task.priority-1 {
        background: linear-gradient(to right, #00b09b, #96c93d) !important;
    }

    .calendar-task.priority-2 {
        background: linear-gradient(to right, #f7971e, #ffd200) !important;
    }

    .calendar-task.priority-3 {
        background: linear-gradient(to right, #ff416c, #ff4b2b) !important;
    }

    .calendar-task.fc-event-past {
        background: linear-gradient(to right, #bdc3c7, #2c3e50) !important;
        opacity: 0.6;
    }

    /* Öncelik Etiketleri */
    .calendar-task .priority-badge {
        display: inline-flex;
        align-items: center;
        padding: 1px 4px;
        margin-left: 4px;
        border-radius: 2px;
        font-size: 0.75em;
        background: rgba(255, 255, 255, 0.2);
        color: white;
    }

    /* Hover Efektleri */
    .fc-event:hover {
        transform: translateY(-1px) !important;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1) !important;
    }

    /* Responsive Düzenlemeler */
    @media (max-width: 768px) {
        .fc-daygrid-day-frame {
            min-height: 80px !important;
            max-height: 80px !important;
        }

        .calendar-event, .calendar-task {
            padding: 1px 4px !important;
            font-size: 0.8em !important;
        }

        .calendar-event::before,
        .calendar-task::before {
            width: 4px;
            height: 4px;
            margin-right: 2px;
        }

        .priority-badge {
            display: none !important;
        }
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <!-- Başlık ve Tarih -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Hoş Geldiniz, {{ Auth::user()->name }}</h1>
        <div class="text-muted">{{ Carbon\Carbon::now()->locale('tr')->isoFormat('LL') }}</div>
    </div>

    <div class="dashboard-container">
        <!-- Takvim -->
        <div class="calendar-container">
            <div id="calendar"></div>
        </div>

        <!-- Sağ Bölüm -->
        <div class="widgets-container">
            <!-- İstatistikler -->
            <div class="mb-4">
                <div class="row g-3">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0 me-3">
                                        <div class="bg-primary bg-opacity-10 p-3 rounded">
                                            <i class="bi bi-calendar-event text-primary fs-4"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">Yaklaşan Etkinlikler</h6>
                                        <h3 class="mb-0">{{ $upcomingEvents->count() }}</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0 me-3">
                                        <div class="bg-warning bg-opacity-10 p-3 rounded">
                                            <i class="bi bi-check2-square text-warning fs-4"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <h6 class="mb-1">Bekleyen Görevler</h6>
                                        <h3 class="mb-0">{{ $pendingTasks->count() }}</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Yaklaşan Etkinlikler -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Yaklaşan Etkinlikler</h5>
                    <a href="{{ route('events.create') }}" class="btn btn-sm btn-primary">
                        <i class="bi bi-plus"></i> Yeni Ekle
                    </a>
                </div>
                <div class="card-body">
                    @if($upcomingEvents->isEmpty())
                        <p class="text-muted text-center my-5">Yaklaşan etkinlik bulunmuyor.</p>
                    @else
                        <div class="list-group list-group-flush">
                            @foreach($upcomingEvents as $event)
                                <div class="list-group-item border-0 ps-0">
                                    <div class="d-flex align-items-center">
                                        <div class="flex-shrink-0 me-3">
                                            <div class="bg-primary bg-opacity-10 p-2 rounded text-center" style="min-width: 60px;">
                                                <div class="small text-primary">{{ Carbon\Carbon::parse($event->start_date)->format('M') }}</div>
                                                <div class="fw-bold">{{ Carbon\Carbon::parse($event->start_date)->format('d') }}</div>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">{{ $event->title }}</h6>
                                            <small class="text-muted">
                                                <i class="bi bi-clock me-1"></i>
                                                {{ Carbon\Carbon::parse($event->start_date)->format('H:i') }} - 
                                                {{ Carbon\Carbon::parse($event->end_date)->format('H:i') }}
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <!-- Bekleyen Görevler -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Bekleyen Görevler</h5>
                    <a href="{{ route('tasks.create') }}" class="btn btn-sm btn-primary">
                        <i class="bi bi-plus"></i> Yeni Ekle
                    </a>
                </div>
                <div class="card-body">
                    @if($pendingTasks->isEmpty())
                        <p class="text-muted text-center my-5">Bekleyen görev bulunmuyor.</p>
                    @else
                        <div class="list-group list-group-flush">
                            @foreach($pendingTasks as $task)
                                <div class="list-group-item border-0 ps-0">
                                    <div class="d-flex align-items-center">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="" id="task{{ $task->id }}">
                                        </div>
                                        <div class="ms-3">
                                            <h6 class="mb-1">{{ $task->title }}</h6>
                                            @if($task->due_date)
                                                <small class="text-muted">
                                                    <i class="bi bi-calendar me-1"></i>
                                                    Son Tarih: {{ Carbon\Carbon::parse($task->due_date)->format('d.m.Y') }}
                                                </small>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Etkinlik/Görev Ekleme Modalı -->
<div class="modal fade" id="calendarItemModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="calendarItemModalTitle">Yeni Ekle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="calendarItemForm">
                    <input type="hidden" id="itemId">
                    <input type="hidden" id="itemType">
                    
                    <!-- Tür seçimi - sadece yeni eklemede görünür -->
                    <div class="mb-3" id="typeSelection">
                        <label class="form-label">Tür</label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="type" id="typeEvent" value="event" checked>
                            <label class="btn btn-outline-primary" for="typeEvent">Etkinlik</label>
                            
                            <input type="radio" class="btn-check" name="type" id="typeTask" value="task">
                            <label class="btn btn-outline-primary" for="typeTask">Görev</label>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Başlık</label>
                        <input type="text" class="form-control" id="itemTitle" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Açıklama</label>
                        <textarea class="form-control" id="itemDescription" rows="3"></textarea>
                    </div>
                    
                    <div id="eventFields">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Başlangıç</label>
                                <input type="datetime-local" class="form-control" id="eventStart">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Bitiş</label>
                                <input type="datetime-local" class="form-control" id="eventEnd">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Konum</label>
                            <input type="text" class="form-control" id="eventLocation">
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="eventAllDay">
                                <label class="form-check-label">Tüm Gün</label>
                            </div>
                        </div>
                    </div>
                    
                    <div id="taskFields" style="display: none;">
                        <div class="mb-3">
                            <label class="form-label">Son Tarih</label>
                            <input type="datetime-local" class="form-control" id="taskDueDate">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Öncelik</label>
                            <select class="form-select" id="taskPriority">
                                <option value="1">Düşük</option>
                                <option value="2">Orta</option>
                                <option value="3">Yüksek</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                <button type="button" class="btn btn-danger" id="deleteButton" style="display: none;">Sil</button>
                <button type="button" class="btn btn-primary" id="saveButton">Kaydet</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<!-- FullCalendar CDN -->
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.js'></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // CSRF Token
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    // Modal ve Form Elemanları
    const modal = new bootstrap.Modal(document.getElementById('calendarItemModal'));
    const form = document.getElementById('calendarItemForm');
    const typeEvent = document.getElementById('typeEvent');
    const typeTask = document.getElementById('typeTask');
    const eventFields = document.getElementById('eventFields');
    const taskFields = document.getElementById('taskFields');
    
    // Form alanları
    const itemId = document.getElementById('itemId');
    const itemType = document.getElementById('itemType');
    const typeSelection = document.getElementById('typeSelection');
    const itemTitle = document.getElementById('itemTitle');
    const itemDescription = document.getElementById('itemDescription');
    const eventStart = document.getElementById('eventStart');
    const eventEnd = document.getElementById('eventEnd');
    const eventLocation = document.getElementById('eventLocation');
    const eventAllDay = document.getElementById('eventAllDay');
    const taskDueDate = document.getElementById('taskDueDate');
    const taskPriority = document.getElementById('taskPriority');
    
    // Butonlar
    const saveButton = document.getElementById('saveButton');
    const deleteButton = document.getElementById('deleteButton');
    
    // Tarih formatlama fonksiyonu
    function formatDateTime(date) {
        return date.toISOString().slice(0, 16);
    }

    // Form tipini değiştir
    function toggleFormType(type) {
        if (type === 'event') {
            eventFields.style.display = 'block';
            taskFields.style.display = 'none';
        } else {
            eventFields.style.display = 'none';
            taskFields.style.display = 'block';
        }
    }
    
    typeEvent.addEventListener('change', () => toggleFormType('event'));
    typeTask.addEventListener('change', () => toggleFormType('task'));
    
    // Formu temizle
    function resetForm() {
        form.reset();
        itemId.value = '';
        itemType.value = '';
        typeEvent.checked = true;
        toggleFormType('event');
        deleteButton.style.display = 'none';
        typeSelection.style.display = 'block'; // Tür seçimini göster
        document.getElementById('calendarItemModalTitle').textContent = 'Yeni Ekle';
    }
    
    // Takvimi oluştur
    var calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
        initialView: 'dayGridMonth',
        locale: 'tr',
        height: '100%',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,dayGridWeek'
        },
        buttonText: {
            today: 'Bugün',
            month: 'Ay',
            week: 'Hafta'
        },
        dayMaxEventRows: false, // Tüm etkinlikleri göster
        selectable: true,
        editable: true,
        events: function(info, successCallback, failureCallback) {
            Promise.all([
                // Etkinlikleri getir
                fetch('/api/calendar/events?' + new URLSearchParams({
                    start: info.startStr,
                    end: info.endStr
                }), {
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    }
                }).then(response => response.json()),
                // Görevleri getir
                fetch('/api/calendar/tasks?' + new URLSearchParams({
                    start: info.startStr,
                    end: info.endStr
                }), {
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    }
                }).then(response => response.json())
            ])
            .then(([events, tasks]) => {
                successCallback([...events, ...tasks]);
            })
            .catch(error => {
                console.error('Error fetching calendar items:', error);
                failureCallback(error);
            });
        },
        select: function(info) {
            resetForm();

            // Seçilen tarih aralığını ayarla
            const startDate = info.start;
            const endDate = info.end;

            // Etkinlik için tarih ayarları
            eventStart.value = formatDateTime(startDate);
            eventEnd.value = formatDateTime(new Date(endDate.getTime() - 1)); // Bir gün öncesi

            // Görev için tarih ayarı
            taskDueDate.value = formatDateTime(startDate);

            // Tüm gün etkinliği kontrolü
            if (info.allDay) {
                eventAllDay.checked = true;
            }

            modal.show();
        },
        eventClick: function(info) {
            resetForm();
            
            const event = info.event;
            const type = event.extendedProps.type;
            
            // Modal başlığını güncelle
            document.getElementById('calendarItemModalTitle').textContent = 
                type === 'event' ? 'Etkinliği Düzenle' : 'Görevi Düzenle';
            
            // Tür seçimini gizle
            typeSelection.style.display = 'none';
            
            itemId.value = event.id;
            itemType.value = type;
            itemTitle.value = event.title;
            itemDescription.value = event.extendedProps.description || '';
            
            if (type === 'event') {
                typeEvent.checked = true;
                toggleFormType('event');
                eventStart.value = formatDateTime(event.start);
                eventEnd.value = event.end ? formatDateTime(event.end) : formatDateTime(event.start);
                eventLocation.value = event.extendedProps.location || '';
                eventAllDay.checked = event.allDay;
            } else {
                typeTask.checked = true;
                toggleFormType('task');
                taskDueDate.value = formatDateTime(event.start);
                taskPriority.value = event.extendedProps.priority;
            }
            
            deleteButton.style.display = 'block';
            modal.show();
        },
        eventDrop: function(info) {
            const event = info.event;
            const type = event.extendedProps.type;
            const url = `/api/calendar/${type}s/${event.id}`;
            
            const data = type === 'event' ? {
                start_date: event.start.toISOString(),
                end_date: event.end ? event.end.toISOString() : event.start.toISOString()
            } : {
                due_date: event.start.toISOString()
            };
            
            fetch(url, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    info.revert();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                info.revert();
            });
        },
        eventDidMount: function(info) {
            const event = info.event;
            const type = event.extendedProps.type;
            
            if (type === 'task') {
                const priorityLabels = {
                    1: 'D',
                    2: 'O',
                    3: 'Y'
                };
                
                const title = info.el.querySelector('.fc-event-title');
                const badge = document.createElement('span');
                badge.className = 'priority-badge';
                badge.textContent = priorityLabels[event.extendedProps.priority];
                title.appendChild(badge);
            }
            
            if (event.start < new Date()) {
                info.el.classList.add('fc-event-past');
            }
        }
    });
    
    calendar.render();
    
    // Kaydet butonu
    saveButton.addEventListener('click', function() {
        const type = typeEvent.checked ? 'event' : 'task';
        const id = itemId.value;
        const method = id ? 'PUT' : 'POST';
        const url = `/api/calendar/${type}s${id ? `/${id}` : ''}`;
        
        let data = {
            title: itemTitle.value,
            description: itemDescription.value
        };
        
        if (type === 'event') {
            data = {
                ...data,
                start_date: eventStart.value,
                end_date: eventEnd.value,
                location: eventLocation.value,
                all_day: eventAllDay.checked
            };
        } else {
            data = {
                ...data,
                due_date: taskDueDate.value,
                priority: taskPriority.value
            };
        }
        
        fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            calendar.refetchEvents();
            modal.hide();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Bir hata oluştu.');
        });
    });
    
    // Sil butonu
    deleteButton.addEventListener('click', function() {
        if (confirm('Bu öğeyi silmek istediğinizden emin misiniz?')) {
            const type = itemType.value;
            const id = itemId.value;
            
            fetch(`/api/calendar/${type}s/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                calendar.refetchEvents();
                modal.hide();
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Bir hata oluştu.');
            });
        }
    });
});
</script>
@endsection 