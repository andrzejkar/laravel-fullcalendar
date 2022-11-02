<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>FullCalendar and Laravel</title>

        <!-- jQuery -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>

        <!-- Moment.js -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>

        <!-- FulCalendar -->
        <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.0.0-beta.1/main.min.js"></script>

        <!-- Bootstrap CSS + icons -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css" rel="stylesheet">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.1/font/bootstrap-icons.css">

        <!-- Bootstrap JavaScript Bundle with Popper -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/js/bootstrap.bundle.min.js"></script>

        <script>
            $(document).ready(function () {
                const eventModal = new bootstrap.Modal(document.getElementById('eventModal'));

                const calendarEl = document.getElementById('calendar');

                let calendar = new FullCalendar.Calendar(calendarEl, {
                    firstDay: 1,
                    editable: true,
                    events: '{{ route('calendar.ajax') }}',
                    navLinks: true,
                    weekends: true,
                    headerToolbar: {left: 'prev,next today', center: 'title', right: 'timeGridDay timeGridWeek dayGridMonth'},
                    selectable: true,
                    selectHelper: true,
                    themeSystem: 'bootstrap5',
                    select: function (info) {
                        let allDay = 0;
                        const start = moment(info.start).format("YYYY-MM-DD HH:mm:ss");
                        const end = moment(info.end).format("YYYY-MM-DD HH:mm:ss");
                        const diff = moment(end).diff(start, 'days')
                        if (diff > 0) {
                            allDay = 1;
                        }
                        const name = prompt('New event title:');
                        if (name) {
                            $.ajax({
                                url: "{{ route('calendar.ajax') }}",
                                data: {
                                    title: name,
                                    start: start,
                                    end: end,
                                    allDay: allDay,
                                    type: 'add',
                                    _token: '{{ csrf_token() }}'
                                },
                                type: "POST",
                                success: function (response) {
                                    calendar.refetchEvents();
                                }
                            });
                        }
                    },
                    eventDrop: function (info) {
                        let start = moment(info.event.start).format("YYYY-MM-DD HH:mm:ss");
                        let end = moment(info.event.end).format("YYYY-MM-DD HH:mm:ss");

                        $.ajax({
                            url: "{{ route('calendar.ajax') }}",
                            data: {
                                id: info.event.id,
                                title: info.event.title,
                                start: start,
                                end: end,
                                allDay: info.event.allDay,
                                color: info.event.backgroundColor,
                                type: 'update',
                                _token: '{{ csrf_token() }}'
                            },
                            type: "POST",
                            success: function () {
                                calendar.refetchEvents();
                            }
                        });
                    },
                    eventResize: function (info) {
                        let start = moment(info.event.start).format("YYYY-MM-DD HH:mm:ss");
                        let end = moment(info.event.end).format("YYYY-MM-DD HH:mm:ss");
                        $.ajax({
                            url: "{{ route('calendar.ajax') }}",
                            data: {
                                id: info.event.id,
                                title: info.event.title,
                                start: start,
                                end: end,
                                color: info.event.backgroundColor,
                                allDay: info.event.allDay,
                                type: 'update',
                                _token: '{{ csrf_token() }}'
                            },
                            type: "POST",
                            success: function () {
                                calendar.refetchEvents();

                            }
                        });
                    },
                    eventClick: function (info) {
                        const start = moment(info.event.start).format("YYYY-MM-DD HH:mm");
                        let end;
                        if (info.event.end == null) {
                            end = start;
                        } else {
                            end = moment(info.event.end).format("YYYY-MM-DD HH:mm");
                        }

                        const modal = document.getElementById('eventModal')
                        modal.addEventListener('show.bs.modal', function() {
                            const eventDeleteType = modal.querySelector('#form-delete-type');
                            eventDeleteType.value = "delete";

                            const modalDeleteId = modal.querySelector('#form-delete-id');
                            modalDeleteId.value = info.event.id;

                            const modalEditType = modal.querySelector('#form-edit-type');
                            modalEditType.value = "update";

                            const modalEditId = modal.querySelector('#form-edit-id');
                            modalEditId.value = info.event.id;

                            const modalBody = modal.querySelector('#modalTitle');
                            modalBody.value = info.event.title;

                            const modalStart = modal.querySelector('#modalStart');
                            modalStart.value = start;

                            const modalEnd = modal.querySelector('#modalEnd');
                            modalEnd.value = end;

                            const modalHeader = modal.querySelector('#modalHeader');
                            modalHeader.style.backgroundColor = info.event.backgroundColor;

                            const modalAllDay = modal.querySelector('#allDay');
                            modalAllDay.checked = !!info.event.allDay;

                        });

                        console.log(info.event.backgroundColor);

                        $('#edit input[type="radio"]').each(function(){
                            $(this).prop('checked', false);
                        });

                        eventModal.show(eventModal);
                    }
                });

        calendar.render();



        $('form#add').on('submit', function (e) {
            e.preventDefault();
            $.ajax({
                type: 'post',
                url: "{{ url('/calendarAjax') }}",
                data: $('form#add').serialize(),
                success: function (response) {
                    calendar.refetchEvents();
                }
            });
        });

        $('form#edit').on('submit', function (e) {
            e.preventDefault();
            $.ajax({
                url: "{{ route('calendar.ajax') }}",
                data: $('form#edit').serialize(),
                type: "POST",
                success: function (response) {
                    calendar.refetchEvents();
                    eventModal.hide();
                }
            });
        });

        $('form#delete').on('submit', function (e) {
            e.preventDefault();
            const id = $('#form-delete-id').val();
            $.ajax({
                type: 'post',
                url: "{{ url('/calendarAjax') }}",
                data: {
                    type: 'delete',
                    id: id,
                    _token: '{{ csrf_token() }}'
                },
                success: function (response) {
                    calendar.refetchEvents();
                    calendar.render();
                    eventModal.hide();
                }
            });
        });

    });
    </script>
    <style>
        input:checked + label {-webkit-box-shadow: 0 0 5px 0 rgba(88, 88, 88, 1);-moz-box-shadow: 0 0 5px 0 rgba(88, 88, 88, 1);box-shadow: 0 0 5px 0 rgba(88, 88, 88, 1);border: 3px solid #fff;}
        .selDefault, .selTwo, .selTree, .selFour {display: block;width: 50px;height: 30px;border-radius: 0.375rem;cursor: pointer;}
        .selDefault {background-color:{{ $color['one'] }};}
        .selTwo {background-color:{{ $color['two'] }};}
        .selTree {background-color: {{ $color['tree'] }};}
        .selFour {background-color: {{ $color['four'] }};}
        </style>
    </head>
    <body>
        <div class="container">
            <div class="row mt-4">
                <div class="col-xl-9">
                    <div id="calendar"></div>
                </div>
                <div class="col-xl-3" style="max-width: 320px">
                    <div class="card">
                        <div class="card-header">New Event</div>
                        <div class="card-body">
                            <form action="{{ route('calendar.ajax') }}" method="post" id="add">
                                @csrf
                                <input type="hidden" name="type" value="form">

                                <label for="eventTitle">Title</label>
                                <input class="form-control mt-1 mb-3" type="text" name="title" id="title" autocomplete="off" required/>

                                <label for="">Color</label>
                                <div class="d-flex justify-content-between mt-1 mb-3">
                                    <input type="radio" class="selDefault btn-check" name="color" id="formDefault" value="{{ $color['one'] }}">
                                    <label class="labelCenter selDefault" for="formDefault"></label>
                                    <input type="radio" class="selTwo btn-check" name="color" id="formRed" value="{{ $color['two'] }}">
                                    <label class="labelCenter selTwo" for="formRed"></label>
                                    <input type="radio" class="selTree btn-check" name="color" id="formGreen" value="{{ $color['tree'] }}">
                                    <label class="labelCenter selTree" for="formGreen"></label>
                                    <input type="radio" class="selFour btn-check" name="color" id="formBlue" value="{{ $color['four'] }}">
                                    <label class="labelCenter selFour" for="formBlue"></label>
                                </div>

                                <label for="eventStartDate">Start</label>
                                <input
                                    id="eventStartDate"
                                    type="text"
                                    class="form-control mt-1 mb-3"
                                    name="start"
                                    value="{{ date("Y-m-d") }} {{ date("H")+1 }}:00"
                                    autocomplete="off"
                                    required />

                                <label for="eventEndDate" class="">End</label>
                                <input
                                    id="eventEndDate"
                                    type="text"
                                    class="form-control mt-1 mb-3"
                                    name="end"
                                    value="{{ date("Y-m-d") }} {{ date("H")+2 }}:00"
                                    autocomplete="off"
                                    required />

                                <div class="text-end p-2">
                                    <label for="allday">All day</label> <input type="checkbox" id="allDay" name="allDay" value="1" checked="checked" />
                                </div>

                                <button class="btn btn-primary w-100 mt-3" type="submit">Add New Event</button>

                            </form>
                        </div>
                    </div>


                </div>
            </div>


        </div>
        <div class="modal fade" id="eventModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header" id="modalHeader">
                        <h5 class="modal-title text-light" id="modalLabel">Event</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('calendar.ajax') }}" method="post" id="edit">
                            @csrf
                            <input type="hidden" id="form-edit-type" name="type" value="update">
                            <input type="hidden" id="form-edit-id" name="id" value="0">
                            <p>
                                <label for="modalTitle">Title</label>
                                <input class="form-control" type="text" name="title" id="modalTitle" value="" />
                            </p>
                            <p>
                                <label for="modalStart">Start</label>
                                <input class="form-control" type="text" name="start" id="modalStart" value="" autocomplete="off" />
                            </p>
                            <p>
                                <label for="modalEnd">End</label>
                                <input class="form-control" type="text" name="end" id="modalEnd" value="" autocomplete="off" />
                            </p>
                            <div class="text-end"><label for="allday">All day</label> <input type="checkbox" id="allDay" name="allDay" value="1" checked="checked" /></div>



                            <label for="">Change color to</label>
                            <div class="card mt-1 mb-5 py-3">
                                <div class="d-flex justify-content-evenly">
                                    <input type="radio" class="selDefault btn-check" name="color" id="modalDefault" value="{{ $color['one'] }}">
                                    <label class="labelCenter selDefault" for="modalDefault"></label>

                                    <input type="radio" class="selTwo btn-check" name="color" id="modalRed" value="{{ $color['two'] }}">
                                    <label class="labelCenter selTwo" for="modalRed"></label>

                                    <input type="radio" class="selTree btn-check" name="color" id="modalGreen" value="{{ $color['tree'] }}">
                                    <label class="labelCenter selTree" for="modalGreen"></label>

                                    <input type="radio" class="selFour btn-check" name="color" id="modalBlue" value="{{ $color['four'] }}">
                                    <label class="labelCenter selFour" for="modalBlue"></label>
                                </div>
                            </div>
                            <div class="float-end">
                                <button type="submit" class="btn btn-success">Save</button>
                            </div>
                        </form>

                        <form action="{{ route('calendar.ajax') }}" method="post" id="delete" class="float-start">
                            @csrf
                            <input type="hidden" id="form-delete-type" name="type" value="delete">
                            <input type="hidden" id="form-delete-id" name="id" value="0">
                            <button type="submit" class="btn btn-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
