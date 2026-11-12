<section class="section">
    <div class="container container-narrow text-center">
        <span class="eyebrow">Error 404</span>
        <h1>Page not found</h1>
        <p class="lead">The page you are looking for may have been moved, or the link may be incorrect.</p>
        <div class="flex" style="justify-content:center;gap:var(--sp-3);flex-wrap:wrap;margin-top:var(--sp-5)">
            <a class="btn btn-primary btn-inline" href="<?= e(url('/')) ?>">Back to homepage</a>
            <a class="btn btn-outline btn-inline" href="<?= e(url('/real-estate')) ?>">Browse properties</a>
            <a class="btn btn-outline btn-inline" href="<?= e(url('/building-materials')) ?>">Browse products</a>
        </div>
    </div>
</section>
