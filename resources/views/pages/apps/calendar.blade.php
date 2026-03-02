@extends('layout.master')

@section('content')
<div class="row">
  <div class="col-12 grid-margin">
    <div class="card">
      <div class="card-body">
        <div id='fullcalendar'></div>
      </div>
    </div>
  </div>
  <div class="col-12 d-none d-md-block">
    <div class="card">
      <div class="card-body">
        <h6 class="mb-3 text-secondary">Draggable Events</h6>
        <div id='external-events' class='d-flex'>
          <div class="fc-event" data-bg="rgba(253,126,20,.25)" data-border="#fd7e14" style="background-color: rgba(253,126,20,.25); border-color: #fd7e14;">
            Product Launch Meeting
          </div>
          <div class="fc-event" data-bg="rgba(241,0,117,.25)" data-border="#f10075" style="background-color: rgba(241,0,117,.25); border-color: #f10075;">
            Quarterly Review
          </div>
          <div class="fc-event" data-bg="rgba(0,204,204,.25)" data-border="#00cccc" style="background-color: rgba(0,204,204,.25); border-color: #00cccc;">
            Stakeholder Presentation
          </div>
          <div class="fc-event" data-bg="rgb(18,182,89,.25)" data-border="#10b759" style="background-color: rgb(18,182,89,.25); border-color: #10b759;">
            Client Strategy Session
          </div>
          <div class="fc-event" data-bg="rgba(91,71,251,.2)" data-border="#5b47fb" style="background-color: rgba(91,71,251,.2); border-color: #5b47fb;">
            Team Building Workshop
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div id="fullCalModal" class="modal fade">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h4 id="modalTitle1" class="modal-title"></h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal"><span class="visually-hidden">close</span></button>
      </div>
      <div id="modalBody1" class="modal-body"></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button class="btn btn-primary">Event Page</button>
      </div>
    </div>
  </div>
</div>

<div id="createEventModal" class="modal fade">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h4 id="modalTitle2" class="modal-title">Add event</h4>
        <button type="button" class="btn-close" data-bs-dismiss="modal"><span class="visually-hidden">close</span></button>
      </div>
      <div id="modalBody2" class="modal-body">
        <form>
          <div class="mb-3">
            <label for="formGroupExampleInput" class="form-label">Example label</label>
            <input type="text" class="form-control" id="formGroupExampleInput" placeholder="Example input">
          </div>
          <div class="mb-3">
            <label for="formGroupExampleInput2" class="form-label">Another label</label>
            <input type="text" class="form-control" id="formGroupExampleInput2" placeholder="Another input">
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button class="btn btn-primary">Add</button>
      </div>
    </div>
  </div>
</div>
@endsection

@push('plugin-scripts')
  <script src="{{ asset('build/plugins/moment/moment.min.js') }}"></script>
  <script src="{{ asset('build/plugins/fullcalendar/index.global.min.js') }}"></script>
@endpush

@push('custom-scripts')
  @vite(['resources/js/pages/fullcalendar.js'])
@endpush