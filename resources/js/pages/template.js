'use strict';

(function () {

  // Root css-variable value
  const getCssVariableValue = function(variableName) {
    let hex = getComputedStyle(document.documentElement).getPropertyValue(variableName);
    if ( hex && hex.length > 0 ) {
      hex = hex.trim();
    }
    return hex;
  }

  // Global variables
  window.config = {
    colors: {
      primary        : getCssVariableValue('--bs-primary'),
      secondary      : getCssVariableValue('--bs-secondary'),
      success        : getCssVariableValue('--bs-success'),
      info           : getCssVariableValue('--bs-info'),
      warning        : getCssVariableValue('--bs-warning'),
      danger         : getCssVariableValue('--bs-danger'),
      light          : getCssVariableValue('--bs-light'),
      dark           : getCssVariableValue('--bs-dark'),
      gridBorder     : "rgba(77, 138, 240, .15)",
    },
    fontFamily       : "'Roboto', Helvetica, sans-serif"
  }



  const body = document.body;
  const sidebar = document.querySelector('.sidebar');
  const sidebarBody = document.querySelector('.sidebar .sidebar-body');
  const sidebarStorageKey = 'pickdrop-sidebar-state';
  const sidebarDesktopQuery = window.matchMedia('(min-width: 992px)');
  const sidebarMainToggler = document.querySelector('.sidebar .sidebar-toggler');
  let sidebarHoverBadge = null;

  if (sidebar) {
    sidebarHoverBadge = document.createElement('div');
    sidebarHoverBadge.className = 'sidebar-hover-badge';
    sidebarHoverBadge.setAttribute('aria-hidden', 'true');
    document.body.appendChild(sidebarHoverBadge);
  }

  const getSidebarState = function() {
    try {
      return window.localStorage.getItem(sidebarStorageKey);
    } catch (error) {
      return null;
    }
  };

  const setSidebarState = function(value) {
    try {
      window.localStorage.setItem(sidebarStorageKey, value);
    } catch (error) {
      return;
    }
  };

  const syncSidebarToggler = function() {
    if (sidebarMainToggler) {
      sidebarMainToggler.classList.toggle('active', sidebarDesktopQuery.matches && body.classList.contains('sidebar-folded'));
    }
  };

  const hideSidebarBadge = function() {
    if (sidebarHoverBadge) {
      sidebarHoverBadge.classList.remove('is-visible');
    }
  };

  const applySidebarState = function() {
    if (sidebarDesktopQuery.matches) {
      body.classList.remove('sidebar-open', 'open-sidebar-folded');
      if (getSidebarState() === 'folded') {
        body.classList.add('sidebar-folded');
      } else {
        body.classList.remove('sidebar-folded');
      }
    } else {
      body.classList.remove('sidebar-folded', 'open-sidebar-folded');
    }
    syncSidebarToggler();
    hideSidebarBadge();
  };

  const getSidebarLinkLabel = function(link) {
    const title = link.querySelector('.link-title');
    return (title ? title.textContent : link.textContent).trim();
  };

  const showSidebarBadge = function(link) {
    if (!sidebarHoverBadge || !sidebarDesktopQuery.matches || !body.classList.contains('sidebar-folded')) {
      return;
    }

    const label = getSidebarLinkLabel(link);

    if (!label) {
      hideSidebarBadge();
      return;
    }

    const linkRect = link.getBoundingClientRect();
    const sidebarRect = sidebar.getBoundingClientRect();

    sidebarHoverBadge.textContent = label;
    sidebarHoverBadge.style.left = `${sidebarRect.right + 10}px`;
    sidebarHoverBadge.style.top = `${linkRect.top + (linkRect.height / 2)}px`;
    sidebarHoverBadge.classList.add('is-visible');
  };

  const pageScrollIndicator = document.createElement('div');
  pageScrollIndicator.className = 'page-scroll-indicator';
  pageScrollIndicator.setAttribute('aria-hidden', 'true');
  pageScrollIndicator.innerHTML = '<span></span>';
  document.body.appendChild(pageScrollIndicator);

  const updatePageScrollIndicator = function() {
    const scrollTop = window.scrollY || document.documentElement.scrollTop;
    const scrollHeight = document.documentElement.scrollHeight - window.innerHeight;
    const thumb = pageScrollIndicator.querySelector('span');

    if (!thumb || scrollHeight <= 12) {
      pageScrollIndicator.classList.remove('is-visible');
      return;
    }

    const trackHeight = pageScrollIndicator.offsetHeight;
    const thumbHeight = thumb.offsetHeight;
    const maxMove = Math.max(trackHeight - thumbHeight, 0);
    const progress = Math.min(Math.max(scrollTop / scrollHeight, 0), 1);

    thumb.style.transform = `translateY(${progress * maxMove}px)`;
    pageScrollIndicator.classList.add('is-visible');
  };

  window.addEventListener('scroll', updatePageScrollIndicator, { passive: true });
  window.addEventListener('resize', updatePageScrollIndicator);
  updatePageScrollIndicator();


  // Initializing bootstrap tooltip
  const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
  const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl)
  })



  // Initializing bootstrap popover
  const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
  const popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
    return new bootstrap.Popover(popoverTriggerEl)
  })



  // Applying perfect-scrollbar 
  if (document.querySelector('.sidebar .sidebar-body')) {
    const sidebarBodyScroll = new PerfectScrollbar('.sidebar-body');
  }



  // Sidebar toggle to sidebar-folded
  const sidebarTogglers = document.querySelectorAll('.sidebar-toggler');
  // there are two sidebar togglers. 
  // 1: on sidebar - for min-width 992px (laptop, desktop) 
  // 2: on navbar - for max-width 991px (mobile phone, tablet)
  if (sidebarTogglers.length) {

    sidebarTogglers.forEach( toggler => {

      toggler.addEventListener('click', function(e) {
        e.preventDefault();
        if (sidebarDesktopQuery.matches) {
          body.classList.toggle('sidebar-folded');
          body.classList.remove('open-sidebar-folded');
          setSidebarState(body.classList.contains('sidebar-folded') ? 'folded' : 'expanded');
        } else {
          body.classList.toggle('sidebar-open');
        }
        syncSidebarToggler();
        hideSidebarBadge();
      });

    });

    applySidebarState();

    // Keep desktop collapse and mobile drawer states separated on resize.
    window.addEventListener('resize', function(event) {
      applySidebarState();
    }, true);

  }



  //  sidebar-folded on min-width:992px and max-width: 1199px (in lg only not in xl)
  // Warning!!! this results apex chart width issue
  // 
  // const desktopMedium = window.matchMedia('(min-width:992px) and (max-width: 1199px)');
  // function iconSidebar() {
  //   if (desktopMedium.matches) {
  //     body.classList.add('sidebar-folded');
  //   } else {
  //     body.classList.remove('sidebar-folded');
  //   }
  // }
  // window.addEventListener('resize', iconSidebar)
  // iconSidebar();



  // Keep folded sidebar icon-only until the user opens it from the toggler.
  if (sidebarBody) {
    sidebarBody.addEventListener('mouseenter', function () {
      body.classList.remove('open-sidebar-folded');
    });

    sidebarBody.addEventListener('mouseleave', function () {
      body.classList.remove('open-sidebar-folded');
      hideSidebarBadge();
    });
  }

  if (sidebar) {
    sidebar.addEventListener('mouseover', function(e) {
      const link = e.target.closest('.nav-link');

      if (link && sidebar.contains(link)) {
        showSidebarBadge(link);
      }
    });

    sidebar.addEventListener('mouseout', function(e) {
      const link = e.target.closest('.nav-link');

      if (link && (!e.relatedTarget || !link.contains(e.relatedTarget))) {
        hideSidebarBadge();
      }
    });

    sidebar.addEventListener('click', function(e) {
      const foldedGroupToggle = e.target.closest('.nav-link[data-bs-toggle="collapse"]');

      if (foldedGroupToggle && sidebarDesktopQuery.matches && body.classList.contains('sidebar-folded')) {
        body.classList.remove('sidebar-folded', 'open-sidebar-folded');
        setSidebarState('expanded');
        syncSidebarToggler();
        hideSidebarBadge();
      }
    });
  }



  // Close sidebar on click outside in phone/tablet
  const mainWrapper = document.querySelector('.main-wrapper');
  if (sidebar) {
    document.addEventListener('touchstart', function(e) {
      if (e.target === mainWrapper && body.classList.contains('sidebar-open')) {
        body.classList.remove('sidebar-open');
        document.querySelector('.sidebar .sidebar-toggler').classList.remove('active');
      }
    });
  }



  // Prevent body scrolling while sidebar scroll
  // 
  // if (sidebarBody) {
  //   sidebarBody.addEventListener('mouseover', function () {
  //     body.classList.add('overflow-hidden');
  //   });
  //   sidebarBody.addEventListener('mouseout', function () {
  //     body.classList.remove('overflow-hidden');
  //   });
  // }




  // Setup clipboard.js plugin (https://github.com/zenorocha/clipboard.js)
  const clipboardButtons = document.querySelectorAll('.btn-clipboard');

  if (clipboardButtons.length) {

    clipboardButtons.forEach( btn => {
      btn.addEventListener('mouseover', function() {
        this.innerText = 'Copy to clipboard';
      });
      btn.addEventListener('mouseout', function() {
        this.innerText = 'Copy';
      });
    });

    const clipboard = new ClipboardJS('.btn-clipboard');

    clipboard.on('success', function(e) {
      e.trigger.innerHTML = 'Copied';
      setTimeout(function() {
        e.trigger.innerHTML = 'Copy';
        e.clearSelection();
      },800)
    });
  }



  // Enable lucide icons with SVG markup
  lucide.createIcons();

})();
