@extends('layout.master')

@section('content')
<div class="row chat-wrapper">
  <div class="col-md-12">
    <div class="card">
      <div class="card-body">
        <div class="row position-relative">
          <div class="col-lg-4 chat-aside border-end-lg">
            <div class="aside-content">
              <div class="aside-header">
                <div class="d-flex justify-content-between align-items-center pb-2 mb-2">
                  <div class="d-flex align-items-center">
                    <figure class="me-2 mb-0">
                      <img src="{{ url('https://placehold.co/43x43') }}" class="img-sm rounded-circle" alt="profile">
                      <div class="status online"></div>
                    </figure>
                    <div>
                      <h6>Amiah Burton</h6>
                      <p class="text-secondary fs-13px">Software Developer</p>
                    </div>
                  </div>
                  <div class="dropdown">
                    <a type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                      <i class="icon-lg text-secondary pb-3px" data-lucide="settings"></i>
                    </a>
                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                      <a class="dropdown-item d-flex align-items-center" href="javascript:;"><i data-lucide="eye" class="icon-sm me-2"></i> <span class="">View Profile</span></a>
                      <a class="dropdown-item d-flex align-items-center" href="javascript:;"><i data-lucide="edit-2" class="icon-sm me-2"></i> <span class="">Edit Profile</span></a>
                      <a class="dropdown-item d-flex align-items-center" href="javascript:;"><i data-lucide="aperture" class="icon-sm me-2"></i> <span class="">Add status</span></a>
                      <a class="dropdown-item d-flex align-items-center" href="javascript:;"><i data-lucide="settings" class="icon-sm me-2"></i> <span class="">Settings</span></a>
                    </div>
                  </div>
                </div>
                <form class="search-form">
                  <div class="input-group">
                    <input type="text" class="form-control" id="searchForm" placeholder="Search here...">
                    <span class="input-group-text bg-transparent">
                      <i data-lucide="search" class="cursor-pointer"></i>
                    </span>
                  </div>
                </form>
              </div>
              <div class="aside-body">
                <ul class="nav nav-tabs nav-fill mt-3" role="tablist">
                  <li class="nav-item">
                    <a class="nav-link active" id="chats-tab" data-bs-toggle="tab" data-bs-target="#chats" role="tab" aria-controls="chats" aria-selected="true">
                      <div class="d-flex flex-row flex-lg-column flex-xl-row align-items-center justify-content-center">
                        <i data-lucide="message-square" class="icon-sm me-sm-2 me-lg-0 me-xl-2 mb-md-1 mb-xl-0"></i>
                        <p class="d-none d-sm-block">Chats</p>
                      </div>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" id="calls-tab" data-bs-toggle="tab" data-bs-target="#calls" role="tab" aria-controls="calls" aria-selected="false">
                      <div class="d-flex flex-row flex-lg-column flex-xl-row align-items-center justify-content-center">
                        <i data-lucide="phone-call" class="icon-sm me-sm-2 me-lg-0 me-xl-2 mb-md-1 mb-xl-0"></i>
                        <p class="d-none d-sm-block">Calls</p>
                      </div>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a class="nav-link" id="contacts-tab" data-bs-toggle="tab" data-bs-target="#contacts" role="tab" aria-controls="contacts" aria-selected="false">
                      <div class="d-flex flex-row flex-lg-column flex-xl-row align-items-center justify-content-center">
                        <i data-lucide="users" class="icon-sm me-sm-2 me-lg-0 me-xl-2 mb-md-1 mb-xl-0"></i>
                        <p class="d-none d-sm-block">Contacts</p>
                      </div>
                    </a>
                  </li>
                </ul>
                <div class="tab-content mt-3">
                  <div class="tab-pane fade show active" id="chats" role="tabpanel" aria-labelledby="chats-tab">
                    <div>
                      <p class="text-secondary mb-1">Recent chats</p>
                      <ul class="list-unstyled chat-list px-1">
                        <li class="chat-item pe-1">
                          <a href="javascript:;" class="d-flex align-items-center">
                            <figure class="mb-0 me-2">
                              <img src="{{ url('https://placehold.co/36x36') }}" class="img-xs rounded-circle" alt="user">
                              <div class="status online"></div>
                            </figure>
                            <div class="d-flex justify-content-between flex-grow-1 border-bottom">
                              <div>
                                <p class="text-body fw-bolder">John Doe</p>
                                <p class="text-secondary fs-13px">Hi, How are you?</p>
                              </div>
                              <div class="d-flex flex-column align-items-end">
                                <p class="text-secondary fs-13px mb-1">4:32 PM</p>
                                <div class="badge rounded-pill bg-primary ms-auto">5</div>
                              </div>
                            </div>
                          </a>
                        </li>
                        <li class="chat-item pe-1">
                          <a href="javascript:;" class="d-flex align-items-center">
                            <figure class="mb-0 me-2">
                              <img src="{{ url('https://placehold.co/36x36') }}" class="img-xs rounded-circle" alt="user">
                              <div class="status offline"></div>
                            </figure>
                            <div class="d-flex justify-content-between flex-grow-1 border-bottom">
                              <div>
                                <p class="text-body fw-bolder">Carl Henson</p>
                                <div class="d-flex align-items-center">
                                  <i data-lucide="image" class="text-secondary icon-md mb-2px"></i>
                                  <p class="text-secondary ms-1">Photo</p>
                                </div>
                              </div>
                              <div class="d-flex flex-column align-items-end">
                                <p class="text-secondary fs-13px mb-1">05:24 PM</p>
                                <div class="badge rounded-pill bg-danger ms-auto">3</div>
                              </div>
                            </div>
                          </a>
                        </li>
                        <li class="chat-item pe-1">
                          <a href="javascript:;" class="d-flex align-items-center">
                            <figure class="mb-0 me-2">
                              <img src="{{ url('https://placehold.co/36x36') }}" class="img-xs rounded-circle" alt="user">
                              <div class="status offline"></div>
                            </figure>
                            <div class="d-flex justify-content-between flex-grow-1 border-bottom">
                              <div>
                                <p class="text-body">John Doe</p>
                                <p class="text-secondary fs-13px">Hi, How are you?</p>
                              </div>
                              <div class="d-flex flex-column align-items-end">
                                <p class="text-secondary fs-13px mb-1">Yesterday</p>
                              </div>
                            </div>
                          </a>
                        </li>
                        <li class="chat-item pe-1">
                          <a href="javascript:;" class="d-flex align-items-center">
                            <figure class="mb-0 me-2">
                              <img src="{{ url('https://placehold.co/36x36') }}" class="img-xs rounded-circle" alt="user">
                              <div class="status online"></div>
                            </figure>
                            <div class="d-flex justify-content-between flex-grow-1 border-bottom">
                              <div>
                                <p class="text-body">Jensen Combs</p>
                                <div class="d-flex align-items-center">
                                  <i data-lucide="video" class="text-secondary icon-md mb-2px"></i>
                                  <p class="text-secondary ms-1">Video</p>
                                </div>
                              </div>
                              <div class="d-flex flex-column align-items-end">
                                <p class="text-secondary fs-13px mb-1">2 days ago</p>
                              </div>
                            </div>
                          </a>
                        </li>
                        <li class="chat-item pe-1">
                          <a href="javascript:;" class="d-flex align-items-center">
                            <figure class="mb-0 me-2">
                              <img src="{{ url('https://placehold.co/36x36') }}" class="img-xs rounded-circle" alt="user">
                              <div class="status offline"></div>
                            </figure>
                            <div class="d-flex justify-content-between flex-grow-1 border-bottom">
                              <div>
                                <p class="text-body">Yaretzi Mayo</p>
                                <p class="text-secondary fs-13px">Hi, How are you?</p>
                              </div>
                              <div class="d-flex flex-column align-items-end">
                                <p class="text-secondary fs-13px mb-1">4 week ago</p>
                              </div>
                            </div>
                          </a>
                        </li>
                        <li class="chat-item pe-1">
                          <a href="javascript:;" class="d-flex align-items-center">
                            <figure class="mb-0 me-2">
                              <img src="{{ url('https://placehold.co/36x36') }}" class="img-xs rounded-circle" alt="user">
                              <div class="status offline"></div>
                            </figure>
                            <div class="d-flex justify-content-between flex-grow-1 border-bottom">
                              <div>
                                <p class="text-body fw-bolder">John Doe</p>
                                <p class="text-secondary fs-13px">Hi, How are you?</p>
                              </div>
                              <div class="d-flex flex-column align-items-end">
                                <p class="text-secondary fs-13px mb-1">4:32 PM</p>
                                <div class="badge rounded-pill bg-primary ms-auto">5</div>
                              </div>
                            </div>
                          </a>
                        </li>
                        <li class="chat-item pe-1">
                          <a href="javascript:;" class="d-flex align-items-center">
                            <figure class="mb-0 me-2">
                              <img src="{{ url('https://placehold.co/36x36') }}" class="img-xs rounded-circle" alt="user">
                              <div class="status online"></div>
                            </figure>
                            <div class="d-flex justify-content-between flex-grow-1 border-bottom">
                              <div>
                                <p class="text-body fw-bolder">Leonardo Payne</p>
                                <div class="d-flex align-items-center">
                                  <i data-lucide="image" class="text-secondary icon-md mb-2px"></i>
                                  <p class="text-secondary ms-1">Photo</p>
                                </div>
                              </div>
                              <div class="d-flex flex-column align-items-end">
                                <p class="text-secondary fs-13px mb-1">6:11 PM</p>
                                <div class="badge rounded-pill bg-danger ms-auto">3</div>
                              </div>
                            </div>
                          </a>
                        </li>
                        <li class="chat-item pe-1">
                          <a href="javascript:;" class="d-flex align-items-center">
                            <figure class="mb-0 me-2">
                              <img src="{{ url('https://placehold.co/36x36') }}" class="img-xs rounded-circle" alt="user">
                              <div class="status online"></div>
                            </figure>
                            <div class="d-flex justify-content-between flex-grow-1 border-bottom">
                              <div>
                                <p class="text-body">John Doe</p>
                                <p class="text-secondary fs-13px">Hi, How are you?</p>
                              </div>
                              <div class="d-flex flex-column align-items-end">
                                <p class="text-secondary fs-13px mb-1">Yesterday</p>
                              </div>
                            </div>
                          </a>
                        </li>
                        <li class="chat-item pe-1">
                          <a href="javascript:;" class="d-flex align-items-center">
                            <figure class="mb-0 me-2">
                              <img src="{{ url('https://placehold.co/36x36') }}" class="img-xs rounded-circle" alt="user">
                              <div class="status online"></div>
                            </figure>
                            <div class="d-flex justify-content-between flex-grow-1 border-bottom">
                              <div>
                                <p class="text-body">Leonardo Payne</p>
                                <div class="d-flex align-items-center">
                                  <i data-lucide="video" class="text-secondary icon-md mb-2px"></i>
                                  <p class="text-secondary ms-1">Video</p>
                                </div>
                              </div>
                              <div class="d-flex flex-column align-items-end">
                                <p class="text-secondary fs-13px mb-1">2 days ago</p>
                              </div>
                            </div>
                          </a>
                        </li>
                        <li class="chat-item pe-1">
                          <a href="javascript:;" class="d-flex align-items-center">
                            <figure class="mb-0 me-2">
                              <img src="{{ url('https://placehold.co/36x36') }}" class="img-xs rounded-circle" alt="user">
                              <div class="status online"></div>
                            </figure>
                            <div class="d-flex justify-content-between flex-grow-1 border-bottom">
                              <div>
                                <p class="text-body">John Doe</p>
                                <p class="text-secondary fs-13px">Hi, How are you?</p>
                              </div>
                              <div class="d-flex flex-column align-items-end">
                                <p class="text-secondary fs-13px mb-1">4 week ago</p>
                              </div>
                            </div>
                          </a>
                        </li>
                      </ul>
                    </div>
                  </div>
                  <div class="tab-pane fade" id="calls" role="tabpanel" aria-labelledby="calls-tab">
                    <p class="text-secondary mb-1">Recent calls</p>
                    <ul class="list-unstyled chat-list px-1">
                      <li class="chat-item pe-1">
                        <a href="javascript:;" class="d-flex align-items-center">
                          <figure class="mb-0 me-2">
                            <img src="{{ url('https://placehold.co/36x36') }}" class="img-xs rounded-circle" alt="user">
                            <div class="status online"></div>
                          </figure>
                          <div class="d-flex align-items-center justify-content-between flex-grow-1 border-bottom">
                            <div>
                              <p class="text-body">Jensen Combs</p>
                              <div class="d-flex align-items-center">
                                <i data-lucide="arrow-up-right" class="icon-sm text-success me-1"></i>
                                <p class="text-secondary fs-13px">Today, 03:11 AM</p>
                              </div>
                            </div>
                            <div class="d-flex flex-column align-items-end">
                              <i data-lucide="phone-call" class="text-secondary icon-md"></i>
                            </div>
                          </div>
                        </a>
                      </li>
                      <li class="chat-item pe-1">
                        <a href="javascript:;" class="d-flex align-items-center">
                          <figure class="mb-0 me-2">
                            <img src="{{ url('https://placehold.co/36x36') }}" class="img-xs rounded-circle" alt="user">
                            <div class="status offline"></div>
                          </figure>
                          <div class="d-flex align-items-center justify-content-between flex-grow-1 border-bottom">
                            <div>
                              <p class="text-body">Leonardo Payne</p>
                              <div class="d-flex align-items-center">
                                <i data-lucide="arrow-down-left" class="icon-sm text-success me-1"></i>
                                <p class="text-secondary fs-13px">Today, 11:41 AM</p>
                              </div>
                            </div>
                            <div class="d-flex flex-column align-items-end">
                              <i data-lucide="video" class="text-secondary icon-md"></i>
                            </div>
                          </div>
                        </a>
                      </li>
                      <li class="chat-item pe-1">
                        <a href="javascript:;" class="d-flex align-items-center">
                          <figure class="mb-0 me-2">
                            <img src="{{ url('https://placehold.co/36x36') }}" class="img-xs rounded-circle" alt="user">
                            <div class="status offline"></div>
                          </figure>
                          <div class="d-flex align-items-center justify-content-between flex-grow-1 border-bottom">
                            <div>
                              <p class="text-body">Carl Henson</p>
                              <div class="d-flex align-items-center">
                                <i data-lucide="arrow-down-left" class="icon-sm text-danger me-1"></i>
                                <p class="text-secondary fs-13px">Today, 04:24 PM</p>
                              </div>
                            </div>
                            <div class="d-flex flex-column align-items-end">
                              <i data-lucide="phone-call" class="text-secondary icon-md"></i>
                            </div>
                          </div>
                        </a>
                      </li>
                      <li class="chat-item pe-1">
                        <a href="javascript:;" class="d-flex align-items-center">
                          <figure class="mb-0 me-2">
                            <img src="{{ url('https://placehold.co/36x36') }}" class="img-xs rounded-circle" alt="user">
                            <div class="status online"></div>
                          </figure>
                          <div class="d-flex align-items-center justify-content-between flex-grow-1 border-bottom">
                            <div>
                              <p class="text-body">Jensen Combs</p>
                              <div class="d-flex align-items-center">
                                <i data-lucide="arrow-down-left" class="icon-sm text-danger me-1"></i>
                                <p class="text-secondary fs-13px">Today, 12:53 AM</p>
                              </div>
                            </div>
                            <div class="d-flex flex-column align-items-end">
                              <i data-lucide="video" class="text-secondary icon-md"></i>
                            </div>
                          </div>
                        </a>
                      </li>
                      <li class="chat-item pe-1">
                        <a href="javascript:;" class="d-flex align-items-center">
                          <figure class="mb-0 me-2">
                            <img src="{{ url('https://placehold.co/36x36') }}" class="img-xs rounded-circle" alt="user">
                            <div class="status online"></div>
                          </figure>
                          <div class="d-flex align-items-center justify-content-between flex-grow-1 border-bottom">
                            <div>
                              <p class="text-body">John Doe</p>
                              <div class="d-flex align-items-center">
                                <i data-lucide="arrow-down-left" class="icon-sm text-success me-1"></i>
                                <p class="text-secondary fs-13px">Today, 01:42 AM</p>
                              </div>
                            </div>
                            <div class="d-flex flex-column align-items-end">
                              <i data-lucide="video" class="text-secondary icon-md"></i>
                            </div>
                          </div>
                        </a>
                      </li>
                      <li class="chat-item pe-1">
                        <a href="javascript:;" class="d-flex align-items-center">
                          <figure class="mb-0 me-2">
                            <img src="{{ url('https://placehold.co/36x36') }}" class="img-xs rounded-circle" alt="user">
                            <div class="status offline"></div>
                          </figure>
                          <div class="d-flex align-items-center justify-content-between flex-grow-1 border-bottom">
                            <div>
                              <p class="text-body">John Doe</p>
                              <div class="d-flex align-items-center">
                                <i data-lucide="arrow-up-right" class="icon-sm text-success me-1"></i>
                                <p class="text-secondary fs-13px">Today, 12:01 AM</p>
                              </div>
                            </div>
                            <div class="d-flex flex-column align-items-end">
                              <i data-lucide="phone-call" class="text-secondary icon-md"></i>
                            </div>
                          </div>
                        </a>
                      </li>
                    </ul>
                  </div>
                  <div class="tab-pane fade" id="contacts" role="tabpanel" aria-labelledby="contacts-tab">
                    <p class="text-secondary mb-1">Contacts</p>
                    <ul class="list-unstyled chat-list px-1">
                      <li class="chat-item pe-1">
                        <a href="javascript:;" class="d-flex align-items-center">
                          <figure class="mb-0 me-2">
                            <img src="{{ url('https://placehold.co/36x36') }}" class="img-xs rounded-circle" alt="user">
                            <div class="status offline"></div>
                          </figure>
                          <div class="d-flex align-items-center justify-content-between flex-grow-1 border-bottom">
                            <div>
                              <p class="text-body">Amiah Burton</p>
                              <div class="d-flex align-items-center">
                                <p class="text-secondary fs-13px">Front-end Developer</p>
                              </div>
                            </div>
                            <div class="d-flex align-items-end text-body">
                              <i data-lucide="message-square" class="icon-md text-secondary me-2"></i>
                              <i data-lucide="phone-call" class="icon-md text-secondary me-2"></i>
                              <i data-lucide="video" class="icon-md text-secondary"></i>
                            </div>
                          </div>
                        </a>
                      </li>
                      <li class="chat-item pe-1">
                        <a href="javascript:;" class="d-flex align-items-center">
                          <figure class="mb-0 me-2">
                            <img src="{{ url('https://placehold.co/36x36') }}" class="img-xs rounded-circle" alt="user">
                            <div class="status online"></div>
                          </figure>
                          <div class="d-flex align-items-center justify-content-between flex-grow-1 border-bottom">
                            <div>
                              <p class="text-body">John Doe</p>
                              <div class="d-flex align-items-center">
                                <p class="text-secondary fs-13px">Back-end Developer</p>
                              </div>
                            </div>
                            <div class="d-flex align-items-end text-body">
                              <i data-lucide="message-square" class="icon-md text-secondary me-2"></i>
                              <i data-lucide="phone-call" class="icon-md text-secondary me-2"></i>
                              <i data-lucide="video" class="icon-md text-secondary"></i>
                            </div>
                          </div>
                        </a>
                      </li>
                      <li class="chat-item pe-1">
                        <a href="javascript:;" class="d-flex align-items-center">
                          <figure class="mb-0 me-2">
                            <img src="{{ url('https://placehold.co/36x36') }}" class="img-xs rounded-circle" alt="user">
                            <div class="status offline"></div>
                          </figure>
                          <div class="d-flex align-items-center justify-content-between flex-grow-1 border-bottom">
                            <div>
                              <p class="text-body">Yaretzi Mayo</p>
                              <div class="d-flex align-items-center">
                                <p class="text-secondary fs-13px">Fullstack Developer</p>
                              </div>
                            </div>
                            <div class="d-flex align-items-end text-body">
                              <i data-lucide="message-square" class="icon-md text-secondary me-2"></i>
                              <i data-lucide="phone-call" class="icon-md text-secondary me-2"></i>
                              <i data-lucide="video" class="icon-md text-secondary"></i>
                            </div>
                          </div>
                        </a>
                      </li>
                      <li class="chat-item pe-1">
                        <a href="javascript:;" class="d-flex align-items-center">
                          <figure class="mb-0 me-2">
                            <img src="{{ url('https://placehold.co/36x36') }}" class="img-xs rounded-circle" alt="user">
                            <div class="status offline"></div>
                          </figure>
                          <div class="d-flex align-items-center justify-content-between flex-grow-1 border-bottom">
                            <div>
                              <p class="text-body">John Doe</p>
                              <div class="d-flex align-items-center">
                                <p class="text-secondary fs-13px">Front-end Developer</p>
                              </div>
                            </div>
                            <div class="d-flex align-items-end text-body">
                              <i data-lucide="message-square" class="icon-md text-secondary me-2"></i>
                              <i data-lucide="phone-call" class="icon-md text-secondary me-2"></i>
                              <i data-lucide="video" class="icon-md text-secondary"></i>
                            </div>
                          </div>
                        </a>
                      </li>
                    </ul>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-8 chat-content">
            <div class="chat-header border-bottom pb-2">
              <div class="d-flex justify-content-between">
                <div class="d-flex align-items-center">
                  <i data-lucide="corner-up-left" id="backToChatList" class="icon-lg me-2 ms-n2 text-secondary d-lg-none"></i>
                  <figure class="mb-0 me-2">
                    <img src="{{ url('https://placehold.co/43x43') }}" class="img-sm rounded-circle" alt="image">
                    <div class="status online"></div>
                    <div class="status online"></div>
                  </figure>
                  <div>
                    <p>Mariana Zenha</p>
                    <p class="text-secondary fs-13px">Front-end Developer</p>
                  </div>
                </div>
                <div class="d-flex align-items-center me-n1">
                  <a class="me-3" type="button" data-bs-toggle="tooltip" data-bs-title="Start video call">
                    <i data-lucide="video" class="icon-lg text-secondary"></i>
                  </a>
                  <a class="me-0 me-sm-3" data-bs-toggle="tooltip" data-bs-title="Start voice call" type="button">
                    <i data-lucide="phone-call" class="icon-lg text-secondary"></i>
                  </a>
                  <a type="button" class="d-none d-sm-block"  data-bs-toggle="tooltip" data-bs-title="Add to contacts">
                    <i data-lucide="user-plus" class="icon-lg text-secondary"></i>
                  </a>
                </div>
              </div>
            </div>
            <div class="chat-body">
              <ul class="messages">
                <li class="message-item friend">
                  <img src="{{ url('https://placehold.co/36x36') }}" class="img-xs rounded-circle" alt="avatar">
                  <div class="content">
                    <div class="message">
                      <div class="bubble">
                        <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry.</p>
                      </div>
                      <span>8:12 PM</span>
                    </div>
                  </div>
                </li>
                <li class="message-item me">
                  <img src="{{ url('https://placehold.co/36x36') }}" class="img-xs rounded-circle" alt="avatar">
                  <div class="content">
                    <div class="message">
                      <div class="bubble">
                        <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry printing and typesetting industry.</p>
                      </div>
                    </div>
                    <div class="message">
                      <div class="bubble">
                        <p>Lorem Ipsum.</p>
                      </div>
                      <span>8:13 PM</span>
                    </div>
                  </div>
                </li>
                <li class="message-item friend">
                  <img src="{{ url('https://placehold.co/36x36') }}" class="img-xs rounded-circle" alt="avatar">
                  <div class="content">
                    <div class="message">
                      <div class="bubble">
                        <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry.</p>
                      </div>
                      <span>8:15 PM</span>
                    </div>
                  </div>
                </li>
                <li class="message-item me">
                  <img src="{{ url('https://placehold.co/36x36') }}" class="img-xs rounded-circle" alt="avatar">
                  <div class="content">
                    <div class="message">
                      <div class="bubble">
                        <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry printing and typesetting industry.</p>
                      </div>
                      <span>8:15 PM</span>
                    </div>
                  </div>
                </li>
                <li class="message-item friend">
                  <img src="{{ url('https://placehold.co/36x36') }}" class="img-xs rounded-circle" alt="avatar">
                  <div class="content">
                    <div class="message">
                      <div class="bubble">
                        <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry.</p>
                      </div>
                      <span>8:17 PM</span>
                    </div>
                  </div>
                </li>
                <li class="message-item me">
                  <img src="{{ url('https://placehold.co/36x36') }}" class="img-xs rounded-circle" alt="avatar">
                  <div class="content">
                    <div class="message">
                      <div class="bubble">
                        <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry printing and typesetting industry.</p>
                      </div>
                    </div>
                    <div class="message">
                      <div class="bubble">
                        <p>Lorem Ipsum.</p>
                      </div>
                      <span>8:18 PM</span>
                    </div>
                  </div>
                </li>
                <li class="message-item friend">
                  <img src="{{ url('https://placehold.co/36x36') }}" class="img-xs rounded-circle" alt="avatar">
                  <div class="content">
                    <div class="message">
                      <div class="bubble">
                        <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry.</p>
                      </div>
                      <span>8:22 PM</span>
                    </div>
                  </div>
                </li>
                <li class="message-item me">
                  <img src="{{ url('https://placehold.co/36x36') }}" class="img-xs rounded-circle" alt="avatar">
                  <div class="content">
                    <div class="message">
                      <div class="bubble">
                        <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry printing and typesetting industry.</p>
                      </div>
                      <span>8:30 PM</span>
                    </div>
                  </div>
                </li>
              </ul>
            </div>
            <div class="chat-footer d-flex">
              <div>
                <button type="button" class="btn border btn-icon rounded-circle me-2" data-bs-toggle="tooltip" data-bs-title="Emoji">
                  <i data-lucide="smile" class="text-secondary"></i>
                </button>
              </div>
              <div class="d-none d-md-block">
                <button type="button" class="btn border btn-icon rounded-circle me-2" data-bs-toggle="tooltip" data-bs-title="Attatch files">
                  <i data-lucide="paperclip" class="text-secondary"></i>
                </button>
              </div>
              <div class="d-none d-md-block">
                <button type="button" class="btn border btn-icon rounded-circle me-2" data-bs-toggle="tooltip" data-bs-title="Record you voice">
                  <i data-lucide="mic" class="text-secondary"></i>
                </button>
              </div>
              <form class="search-form flex-grow-1 me-2">
                <div class="input-group">
                  <input type="text" class="form-control rounded-pill" id="chatForm" placeholder="Type a message">
                </div>
              </form>
              <div>
                <button type="button" class="btn btn-primary btn-icon rounded-circle">
                  <i data-lucide="send"></i>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('custom-scripts')
  @vite(['resources/js/pages/chat.js'])
@endpush