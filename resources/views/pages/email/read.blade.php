@extends('layout.master')

@section('content')
<div class="row inbox-wrapper">
  <div class="col-lg-12">
    <div class="card">
      <div class="card-body">
        <div class="row">
          <div class="col-lg-3 col-xxl-2 border-end-lg">
            <div class="aside-content">
              <div class="d-flex align-items-center justify-content-between">
                <button class="navbar-toggle btn btn-icon border d-block d-lg-none" data-bs-target=".email-aside-nav" data-bs-toggle="collapse" type="button">
                  <span class="icon"><i data-lucide="chevron-down"></i></span>
                </button>
                <div class="order-first">
                  <h4>Mail Service</h4>
                  <p class="text-secondary">amiahburton@gmail.com</p>
                </div>
              </div>
              <div class="d-grid my-3">
                <a class="btn btn-primary" href="{{ url('/email/compose') }}">Compose Email</a>
              </div>
            <div class="email-aside-nav collapse">
              <ul class="nav flex-column">
                <li class="nav-item">
                  <a class="nav-link d-flex align-items-center" href="{{ url('/email/inbox') }}">
                    <i data-lucide="inbox" class="icon-lg me-2"></i>
                    Inbox
                    <span class="badge bg-danger fw-bolder ms-auto">2
                  </a>
                </li>
                <li class="nav-item">
                  <a class="nav-link d-flex align-items-center" href="#">
                    <i data-lucide="mail" class="icon-lg me-2"></i>
                    Sent Mail
                  </a>
                </li>
                <li class="nav-item">
                  <a class="nav-link d-flex align-items-center" href="#">
                    <i data-lucide="briefcase" class="icon-lg me-2"></i>
                    Important
                    <span class="badge bg-secondary fw-bolder ms-auto">4
                  </a>
                </li>
                <li class="nav-item">
                  <a class="nav-link d-flex align-items-center" href="#">
                    <i data-lucide="file" class="icon-lg me-2"></i>
                    Drafts
                  </a>
                </li>
                <li class="nav-item">
                  <a class="nav-link d-flex align-items-center" href="#">
                    <i data-lucide="star" class="icon-lg me-2"></i>
                    Tags
                  </a>
                </li>
                <li class="nav-item">
                  <a class="nav-link d-flex align-items-center" href="#">
                    <i data-lucide="trash" class="icon-lg me-2"></i>
                    Trash
                  </a>
                </li>
              </ul>
              <p class="text-secondary fs-12px fw-bolder text-uppercase mb-2 mt-4">Labels</p>
              <ul class="nav flex-column">
                <li class="nav-item">
                  <a class="nav-link d-flex align-items-center" href="#">
                    <i data-lucide="tag" class="text-warning icon-lg me-2"></i>
                    Important
                  </a>
                </li>
                <li class="nav-item active">
                  <a class="nav-link d-flex align-items-center" href="#">
                  <i data-lucide="tag" class="text-primary icon-lg me-2"></i> 
                  Business 
                </a>
                </li>
                <li class="nav-item">
                  <a class="nav-link d-flex align-items-center" href="#">
                    <i data-lucide="tag" class="text-info icon-lg me-2"></i> 
                    Inspiration 
                  </a>
                </li>
              </ul>
            </div>
            </div>
          </div>
          <div class="col-lg-9 col-xxl-10">
            <div class="d-flex align-items-center justify-content-between p-3 border-bottom fs-16px">
              <div class="d-flex align-items-center">
                <i data-lucide="star" class="text-primary icon-md me-2"></i>
                <span>New Project</span>
              </div>
              <div>
                <a class="me-2" type="button" data-bs-toggle="tooltip" data-bs-title="Forward"><i data-lucide="share" class="text-secondary icon-lg"></i></a>
                <a class="me-2" type="button" data-bs-toggle="tooltip" data-bs-title="Print"><i data-lucide="printer" class="text-secondary icon-lg"></i></a>
                <a type="button" data-bs-toggle="tooltip" data-bs-title="Delete"><i data-lucide="trash" class="text-secondary icon-lg"></i></a>
              </div>
            </div>
            <div class="d-flex align-items-center justify-content-between flex-wrap px-3 py-2 border-bottom">
              <div class="d-flex align-items-center">
                <div class="me-2">
                  <img src="{{ url('https://placehold.co/36x36') }}" alt="Avatar" class="rounded-circle img-xs">
                </div>
                <div class="d-flex align-items-center">
                  <a href="#" class="text-body">John Doe</a> 
                  <span class="mx-2 text-secondary">to</span>
                  <a href="#" class="text-body me-2">me</a>
                  <div class="actions dropdown">
                    <a href="#" data-bs-toggle="dropdown"><i data-lucide="chevron-down" class="icon-lg text-secondary"></i></a>
                    <div class="dropdown-menu" role="menu">
                      <a class="dropdown-item" href="#">Mark as read</a>
                      <a class="dropdown-item" href="#">Mark as unread</a>
                      <a class="dropdown-item" href="#">Spam</a>
                      <div class="dropdown-divider"></div>
                      <a class="dropdown-item text-danger" href="#">Delete</a>
                    </div>
                  </div>
                </div>
              </div>
              <div class="fs-13px text-secondary mt-2 mt-sm-0">Nov 20, 11:20</div>
            </div>
            <div class="p-4 border-bottom">
              <p>Hello,</p>
              <br>
              <p>Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem.</p>
              <br>
              <p>Quisque rutrum. Aenean imperdiet. Etiam ultricies nisi vel augue. Curabitur ullamcorper ultricies nisi. Nam eget dui. Etiam rhoncus. Maecenas tempus, tellus eget condimentum rhoncus, sem quam semper libero, sit amet adipiscing sem neque sed ipsum. Nam quam nunc, blandit vel, luctus pulvinar, hendrerit id, lorem. Maecenas nec odio et ante tincidunt tempus. Donec vitae sapien ut libero venenatis faucibus. Nullam quis ante. Etiam sit amet orci eget eros faucibus tincidunt. Duis leo. Sed fringilla mauris sit amet nibh. Donec sodales sagittis magna.</p>
              <br>
              <p><strong>Regards</strong>,<br> John Doe</p>
            </div>
            <div class="p-3">
              <div class="mb-3">Attachments <span>(3 files, 12,44 KB)</span></div>
              <ul class="nav flex-column">
                <li class="nav-item"><a href="javascript:;" class="nav-link text-body"><span data-lucide="file" class="icon-lg text-secondary"></span> Reference.zip <span class="text-secondary fs-11px">(5.10 MB)</span></a></li>
                <li class="nav-item"><a href="javascript:;" class="nav-link text-body"><span data-lucide="file" class="icon-lg text-secondary"></span> Instructions.zip <span class="text-secondary fs-11px">(3.15 MB)</span></a></li>
                <li class="nav-item"><a href="javascript:;" class="nav-link text-body"><span data-lucide="file" class="icon-lg text-secondary"></span> Team-list.pdf <span class="text-secondary fs-11px">(4.5 MB)</span></a></li>
              </ul>
            </div>
          </div>
        </div>
          
      </div>
    </div>
  </div>
</div>
@endsection