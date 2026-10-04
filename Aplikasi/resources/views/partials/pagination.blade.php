@if($paginator->hasPages())<nav class="pagination" aria-label="Halaman data"><span class="muted">{{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} dari {{ $paginator->total() }}</span><div>
@if($paginator->onFirstPage())<span class="page-disabled">Sebelumnya</span>
@else<a href="{{ $paginator->previousPageUrl() }}">Sebelumnya</a>
@endif<span>Halaman {{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}</span>
@if($paginator->hasMorePages())<a href="{{ $paginator->nextPageUrl() }}">Berikutnya</a>
@else<span class="page-disabled">Berikutnya</span>
@endif</div></nav>
@endif
