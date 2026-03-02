@extends('layout.master')

@section('content')
<div class="row">
  <div class="col-12 grid-margin">
    <div class="card">
      <div class="position-relative">
        <figure class="overflow-hidden mb-0 d-flex justify-content-center">
          <img src="{{ url('https://placehold.co/1300x272') }}" class="rounded-top" alt="profile cover">
        </figure>
        <div class="d-flex justify-content-between align-items-center position-absolute top-90 w-100 px-2 px-md-4 mt-n4">
          <div>
            <img class="w-70px rounded-circle" src="{{ url('https://placehold.co/70x70') }}" alt="profile">
            <span class="h4 ms-3 text-dark">Amiah Burton</span>
          </div>
          <div class="d-none d-md-block">
            <button class="btn btn-primary btn-icon-text">
              <i data-lucide="edit" class="btn-icon-prepend"></i> Edit profile
            </button>
          </div>
        </div>
      </div>
      <div class="d-flex justify-content-center p-3 rounded-bottom">
        <ul class="d-flex align-items-center m-0 p-0">
          <li class="d-flex align-items-center active">
            <i class="me-1 icon-md text-primary" data-lucide="columns"></i>
            <a class="pt-1px d-none d-md-block text-primary" href="#">Timeline</a>
          </li>
          <li class="ms-3 ps-3 border-start d-flex align-items-center">
            <i class="me-1 icon-md" data-lucide="user"></i>
            <a class="pt-1px d-none d-md-block text-body" href="#">About</a>
          </li>
          <li class="ms-3 ps-3 border-start d-flex align-items-center">
            <i class="me-1 icon-md" data-lucide="users"></i>
            <a class="pt-1px d-none d-md-block text-body" href="#">Friends <span class="text-secondary fs-12px">3,765</span></a>
          </li>
          <li class="ms-3 ps-3 border-start d-flex align-items-center">
            <i class="me-1 icon-md" data-lucide="image"></i>
            <a class="pt-1px d-none d-md-block text-body" href="#">Photos</a>
          </li>
          <li class="ms-3 ps-3 border-start d-flex align-items-center">
            <i class="me-1 icon-md" data-lucide="video"></i>
            <a class="pt-1px d-none d-md-block text-body" href="#">Videos</a>
          </li>
        </ul>
      </div>
    </div>
  </div>
</div>
<div class="row profile-body">
  <!-- left wrapper start -->
  <div class="d-none d-md-block col-md-4 col-xl-3 left-wrapper">
    <div class="card rounded">
      <div class="card-body">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <h6 class="card-title mb-0">About</h6>
          <div class="dropdown">
            <button class="btn btn-link p-0" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
              <i class="icon-lg text-secondary pb-3px" data-lucide="more-horizontal"></i>
            </button>
            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
              <a class="dropdown-item d-flex align-items-center" href="javascript:;"><i data-lucide="edit-2" class="icon-sm me-2"></i> <span class="">Edit</span></a>
              <a class="dropdown-item d-flex align-items-center" href="javascript:;"><i data-lucide="git-branch" class="icon-sm me-2"></i> <span class="">Update</span></a>
              <a class="dropdown-item d-flex align-items-center" href="javascript:;"><i data-lucide="eye" class="icon-sm me-2"></i> <span class="">View all</span></a>
            </div>
          </div>
        </div>
        <p>Hi! I'm Amiah the Senior UI Designer at NobleUI. We hope you enjoy the design and quality of Social.</p>
        <div class="mt-3">
          <label class="fs-11px fw-bolder mb-0 text-uppercase">Joined:</label>
          <p class="text-secondary">November 15, 2015</p>
        </div>
        <div class="mt-3">
          <label class="fs-11px fw-bolder mb-0 text-uppercase">Lives:</label>
          <p class="text-secondary">New York, USA</p>
        </div>
        <div class="mt-3">
          <label class="fs-11px fw-bolder mb-0 text-uppercase">Email:</label>
          <p class="text-secondary">me@nobleui.com</p>
        </div>
        <div class="mt-3">
          <label class="fs-11px fw-bolder mb-0 text-uppercase">Website:</label>
          <p class="text-secondary">www.nobleui.com</p>
        </div>
        <div class="mt-3 d-flex social-links">
          <a href="javascript:;" class="btn btn-icon border btn-xs me-2">
            <i data-lucide="github"></i>
          </a>
          <a href="javascript:;" class="btn btn-icon border btn-xs me-2">
            <i data-lucide="twitter"></i>
          </a>
          <a href="javascript:;" class="btn btn-icon border btn-xs me-2">
            <i data-lucide="instagram"></i>
          </a>
        </div>
      </div>
    </div>
  </div>
  <!-- left wrapper end -->
  <!-- middle wrapper start -->
  <div class="col-md-8 col-xl-6 middle-wrapper">
    <div class="row">
      <div class="col-md-12 grid-margin">
        <div class="card rounded">
          <div class="card-header">
            <div class="d-flex align-items-center justify-content-between">
              <div class="d-flex align-items-center">
                <img class="img-xs rounded-circle" src="{{ url('https://placehold.co/36x36') }}" alt="">													
                <div class="ms-2">
                  <p>Mike Popescu</p>
                  <p class="fs-11px text-secondary">1 min ago</p>
                </div>
              </div>
              <div class="dropdown">
                <button class="btn btn-link p-0" type="button" id="dropdownMenuButton2" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                  <i class="icon-lg pb-3px" data-lucide="more-horizontal"></i>
                </button>
                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton2">
                  <a class="dropdown-item d-flex align-items-center" href="javascript:;"><i data-lucide="meh" class="icon-sm me-2"></i> <span class="">Unfollow</span></a>
                  <a class="dropdown-item d-flex align-items-center" href="javascript:;"><i data-lucide="corner-right-up" class="icon-sm me-2"></i> <span class="">Go to post</span></a>
                  <a class="dropdown-item d-flex align-items-center" href="javascript:;"><i data-lucide="share-2" class="icon-sm me-2"></i> <span class="">Share</span></a>
                  <a class="dropdown-item d-flex align-items-center" href="javascript:;"><i data-lucide="copy" class="icon-sm me-2"></i> <span class="">Copy link</span></a>
                </div>
              </div>
            </div>
          </div>
          <div class="card-body">
            <p class="mb-3 fs-14px">Lorem ipsum dolor sit amet, consectetur adipisicing elit. Accusamus minima delectus nemo unde quae recusandae assumenda.</p>
            <img class="img-fluid" src="{{ url('https://placehold.co/513x365') }}" alt="">
          </div>
          <div class="card-footer">
            <div class="d-flex post-actions">
              <a href="javascript:;" class="d-flex align-items-center text-secondary me-4">
                <i class="icon-md" data-lucide="heart"></i>
                <p class="d-none d-md-block ms-2">Like</p>
              </a>
              <a href="javascript:;" class="d-flex align-items-center text-secondary me-4">
                <i class="icon-md" data-lucide="message-square"></i>
                <p class="d-none d-md-block ms-2">Comment</p>
              </a>
              <a href="javascript:;" class="d-flex align-items-center text-secondary">
                <i class="icon-md" data-lucide="share"></i>
                <p class="d-none d-md-block ms-2">Share</p>
              </a>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-12">
        <div class="card rounded">
          <div class="card-header">
            <div class="d-flex align-items-center justify-content-between">
              <div class="d-flex align-items-center">
                <img class="img-xs rounded-circle" src="{{ url('https://placehold.co/36x36') }}" alt="">													
                <div class="ms-2">
                  <p>Mike Popescu</p>
                  <p class="fs-11px text-secondary">5 min ago</p>
                </div>
              </div>
              <div class="dropdown">
                <button class="btn btn-link p-0" type="button" id="dropdownMenuButton3" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                  <i class="icon-lg pb-3px" data-lucide="more-horizontal"></i>
                </button>
                <div class="dropdown-menu" aria-labelledby="dropdownMenuButton3">
                  <a class="dropdown-item d-flex align-items-center" href="javascript:;"><i data-lucide="meh" class="icon-sm me-2"></i> <span class="">Unfollow</span></a>
                  <a class="dropdown-item d-flex align-items-center" href="javascript:;"><i data-lucide="corner-right-up" class="icon-sm me-2"></i> <span class="">Go to post</span></a>
                  <a class="dropdown-item d-flex align-items-center" href="javascript:;"><i data-lucide="share-2" class="icon-sm me-2"></i> <span class="">Share</span></a>
                  <a class="dropdown-item d-flex align-items-center" href="javascript:;"><i data-lucide="copy" class="icon-sm me-2"></i> <span class="">Copy link</span></a>
                </div>
              </div>
            </div>
          </div>
          <div class="card-body">
            <p class="mb-3 fs-14px">Lorem ipsum dolor sit amet, consectetur adipisicing elit.</p>
            <img class="img-fluid" src="{{ url('https://placehold.co/513x365') }}" alt="">
          </div>
          <div class="card-footer">
            <div class="d-flex post-actions">
              <a href="javascript:;" class="d-flex align-items-center text-secondary me-4">
                <i class="icon-md" data-lucide="heart"></i>
                <p class="d-none d-md-block ms-2">Like</p>
              </a>
              <a href="javascript:;" class="d-flex align-items-center text-secondary me-4">
                <i class="icon-md" data-lucide="message-square"></i>
                <p class="d-none d-md-block ms-2">Comment</p>
              </a>
              <a href="javascript:;" class="d-flex align-items-center text-secondary">
                <i class="icon-md" data-lucide="share"></i>
                <p class="d-none d-md-block ms-2">Share</p>
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- middle wrapper end -->
  <!-- right wrapper start -->
  <div class="d-none d-xl-block col-xl-3">
    <div class="row">
      <div class="col-md-12 grid-margin">
        <div class="card rounded">
          <div class="card-body">
            <h6 class="card-title">latest photos</h6>
            <div class="row ms-0 me-0">
              <a href="javascript:;" class="col-md-4 ps-1 pe-1">
                <figure class="mb-2">
                  <img class="img-fluid rounded" src="{{ url('https://placehold.co/77x36') }}" alt="">
                </figure>
              </a>
              <a href="javascript:;" class="col-md-4 ps-1 pe-1">
                <figure class="mb-2">
                  <img class="img-fluid rounded" src="{{ url('https://placehold.co/77x36') }}" alt="">
                </figure>
              </a>
              <a href="javascript:;" class="col-md-4 ps-1 pe-1">
                <figure class="mb-2">
                  <img class="img-fluid rounded" src="{{ url('https://placehold.co/77x36') }}" alt="">
                </figure>
              </a>
              <a href="javascript:;" class="col-md-4 ps-1 pe-1">
                <figure class="mb-2">
                  <img class="img-fluid rounded" src="{{ url('https://placehold.co/77x36') }}" alt="">
                </figure>
              </a>
              <a href="javascript:;" class="col-md-4 ps-1 pe-1">
                <figure class="mb-2">
                  <img class="img-fluid rounded" src="{{ url('https://placehold.co/77x36') }}" alt="">
                </figure>
              </a>
              <a href="javascript:;" class="col-md-4 ps-1 pe-1">
                <figure class="mb-2">
                  <img class="img-fluid rounded" src="{{ url('https://placehold.co/77x36') }}" alt="">
                </figure>
              </a>
              <a href="javascript:;" class="col-md-4 ps-1 pe-1">
                <figure class="mb-0">
                  <img class="img-fluid rounded" src="{{ url('https://placehold.co/77x36') }}" alt="">
                </figure>
              </a>
              <a href="javascript:;" class="col-md-4 ps-1 pe-1">
                <figure class="mb-0">
                  <img class="img-fluid rounded" src="{{ url('https://placehold.co/77x36') }}" alt="">
                </figure>
              </a>
              <a href="javascript:;" class="col-md-4 ps-1 pe-1">
                <figure class="mb-0">
                  <img class="img-fluid rounded" src="{{ url('https://placehold.co/77x77') }}" alt="">
                </figure>
              </a>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-12 grid-margin">
        <div class="card rounded">
          <div class="card-body">
            <h6 class="card-title">suggestions for you</h6>
            <div class="d-flex justify-content-between mb-2 pb-2 border-bottom">
              <div class="d-flex align-items-center hover-pointer">
                <img class="img-xs rounded-circle" src="{{ url('https://placehold.co/36x36') }}" alt="">													
                <div class="ms-2">
                  <p>Mike Popescu</p>
                  <p class="fs-11px text-secondary">12 Mutual Friends</p>
                </div>
              </div>
              <button class="btn btn-icon btn-link"><i data-lucide="user-plus" class="text-secondary"></i></button>
            </div>
            <div class="d-flex justify-content-between mb-2 pb-2 border-bottom">
              <div class="d-flex align-items-center hover-pointer">
                <img class="img-xs rounded-circle" src="{{ url('https://placehold.co/36x36') }}" alt="">													
                <div class="ms-2">
                  <p>Mike Popescu</p>
                  <p class="fs-11px text-secondary">12 Mutual Friends</p>
                </div>
              </div>
              <button class="btn btn-icon btn-link"><i data-lucide="user-plus" class="text-secondary"></i></button>
            </div>
            <div class="d-flex justify-content-between mb-2 pb-2 border-bottom">
              <div class="d-flex align-items-center hover-pointer">
                <img class="img-xs rounded-circle" src="{{ url('https://placehold.co/36x36') }}" alt="">													
                <div class="ms-2">
                  <p>Mike Popescu</p>
                  <p class="fs-11px text-secondary">12 Mutual Friends</p>
                </div>
              </div>
              <button class="btn btn-icon btn-link"><i data-lucide="user-plus" class="text-secondary"></i></button>
            </div>
            <div class="d-flex justify-content-between mb-2 pb-2 border-bottom">
              <div class="d-flex align-items-center hover-pointer">
                <img class="img-xs rounded-circle" src="{{ url('https://placehold.co/36x36') }}" alt="">													
                <div class="ms-2">
                  <p>Mike Popescu</p>
                  <p class="fs-11px text-secondary">12 Mutual Friends</p>
                </div>
              </div>
              <button class="btn btn-icon btn-link"><i data-lucide="user-plus" class="text-secondary"></i></button>
            </div>
            <div class="d-flex justify-content-between mb-2 pb-2 border-bottom">
              <div class="d-flex align-items-center hover-pointer">
                <img class="img-xs rounded-circle" src="{{ url('https://placehold.co/36x36') }}" alt="">													
                <div class="ms-2">
                  <p>Mike Popescu</p>
                  <p class="fs-11px text-secondary">12 Mutual Friends</p>
                </div>
              </div>
              <button class="btn btn-icon btn-link"><i data-lucide="user-plus" class="text-secondary"></i></button>
            </div>
            <div class="d-flex justify-content-between">
              <div class="d-flex align-items-center hover-pointer">
                <img class="img-xs rounded-circle" src="{{ url('https://placehold.co/36x36') }}" alt="">													
                <div class="ms-2">
                  <p>Mike Popescu</p>
                  <p class="fs-11px text-secondary">12 Mutual Friends</p>
                </div>
              </div>
              <button class="btn btn-icon btn-link"><i data-lucide="user-plus" class="text-secondary"></i></button>
            </div>

          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- right wrapper end -->
</div>
@endsection