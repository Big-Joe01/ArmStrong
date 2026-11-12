<section class="section">
    <div class="container container-narrow text-center">
        <span class="eyebrow">Error 500</span>
        <h1>Something went wrong</h1>
        <p class="lead">We could not complete that request. Please try again, or contact us if the problem continues.</p>
        <div class="flex" style="justify-content:center;gap:var(--sp-3);flex-wrap:wrap;margin-top:var(--sp-5)">
            <a class="btn btn-primary btn-inline" href="<?= e(url('/')) ?>">Back to homepage</a>
            <a class="btn btn-outline btn-inline" href="<?= e(url('/contact')) ?>">Contact us</a>
        </div>
    </div>
</section>
