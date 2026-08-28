@props(['paginator', 'label' => 'items'])

@if(isset($paginator) && method_exists($paginator, 'total') && $paginator->total() > 0)
  <div {{ $attributes->merge(['class' => 'card-footer bg-transparent app-pagination-footer']) }}>
    <small class="app-pagination-summary">
      Showing {{ $paginator->firstItem() }} to {{ $paginator->lastItem() }} of {{ $paginator->total() }} {{ $label }}
    </small>
    <div class="app-pagination-controls">
      @if($paginator->hasPages())
        {{ $paginator->links('pagination::bootstrap-5') }}
      @else
        <nav class="app-pagination-nav" role="navigation" aria-label="Pagination">
          <ul class="pagination mb-0">
            <li class="page-item disabled" aria-disabled="true" aria-label="Previous">
              <span class="page-link" aria-hidden="true">&lsaquo;</span>
            </li>
            <li class="page-item active" aria-current="page">
              <span class="page-link">1</span>
            </li>
            <li class="page-item disabled" aria-disabled="true" aria-label="Next">
              <span class="page-link" aria-hidden="true">&rsaquo;</span>
            </li>
          </ul>
        </nav>
      @endif
    </div>
  </div>
@endif
