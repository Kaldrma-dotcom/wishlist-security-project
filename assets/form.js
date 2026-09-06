(function () {
    const typeEl = document.getElementById('type');
    const statusEl = document.getElementById('status');
    const ratingWrap = document.getElementById('rating-wrap');
    const ratingEl = document.getElementById('rating');
    const searchInput = document.getElementById('media-search');
    const searchBtn = document.getElementById('media-search-btn');
    const resultsEl = document.getElementById('search-results');
    const titleEl = document.getElementById('title');
    const posterEl = document.getElementById('poster_url');
    const externalEl = document.getElementById('external_id');

    if (!typeEl || !statusEl) {
        return;
    }

    const currentStatus = statusEl.dataset.current || statusEl.value;

    function statusesFor(type) {
        if (type === 'movie') {
            return [
                { value: 'plan_to_watch', label: 'Plan to Watch' },
                { value: 'completed', label: 'Completed' }
            ];
        }
        return [
            { value: 'plan_to_watch', label: 'Plan to Watch' },
            { value: 'watching', label: 'Watching' },
            { value: 'completed', label: 'Completed' }
        ];
    }

    function syncStatusOptions() {
        const type = typeEl.value;
        const allowed = statusesFor(type);
        const previous = statusEl.value || currentStatus;
        statusEl.innerHTML = '';
        const selected = allowed.some(function (s) { return s.value === previous; })
            ? previous
            : 'plan_to_watch';
        allowed.forEach(function (s) {
            const opt = document.createElement('option');
            opt.value = s.value;
            opt.textContent = s.label;
            opt.selected = s.value === selected;
            statusEl.appendChild(opt);
        });
        syncRating();
    }

    function syncRating() {
        const hide = statusEl.value === 'plan_to_watch';
        ratingWrap.classList.toggle('hidden', hide);
        if (hide && ratingEl) {
            ratingEl.value = '';
        }
    }

    typeEl.addEventListener('change', function () {
        if (posterEl) posterEl.value = '';
        if (externalEl) externalEl.value = '';
        if (resultsEl) resultsEl.replaceChildren();
        syncStatusOptions();
    });
    statusEl.addEventListener('change', syncRating);

    function isHttpsUrl(value) {
        try {
            const u = new URL(value);
            return u.protocol === 'https:';
        } catch (e) {
            return false;
        }
    }

    function renderResults(items) {
        resultsEl.replaceChildren();
        if (!items.length) {
            const p = document.createElement('p');
            p.className = 'muted';
            p.textContent = 'No results from the external API.';
            resultsEl.appendChild(p);
            return;
        }
        items.forEach(function (item) {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'search-item';

            if (item.poster && isHttpsUrl(item.poster)) {
                const img = document.createElement('img');
                img.className = 'poster';
                img.alt = '';
                img.src = item.poster;
                btn.appendChild(img);
            } else {
                const ph = document.createElement('div');
                ph.className = 'poster';
                btn.appendChild(ph);
            }

            const span = document.createElement('span');
            span.textContent = item.title + (item.year ? ' (' + item.year + ')' : '');
            btn.appendChild(span);

            btn.addEventListener('click', function () {
                titleEl.value = item.title;
                if (posterEl) posterEl.value = (item.poster && isHttpsUrl(item.poster)) ? item.poster : '';
                if (externalEl) externalEl.value = item.external_id || '';
                resultsEl.replaceChildren();
                const p = document.createElement('p');
                p.className = 'muted';
                p.textContent = 'Selected: ' + item.title;
                resultsEl.appendChild(p);
            });
            resultsEl.appendChild(btn);
        });
    }

    async function runSearch() {
        const q = (searchInput.value || '').trim();
        if (q.length < 2) {
            resultsEl.replaceChildren();
            const p = document.createElement('p');
            p.className = 'muted';
            p.textContent = 'Type at least 2 characters.';
            resultsEl.appendChild(p);
            return;
        }
        resultsEl.replaceChildren();
        const loading = document.createElement('p');
        loading.className = 'muted';
        loading.textContent = 'Searching external API…';
        resultsEl.appendChild(loading);

        const url = '../api/search.php?q=' + encodeURIComponent(q) + '&type=' + encodeURIComponent(typeEl.value);
        try {
            const res = await fetch(url, { credentials: 'same-origin' });
            const data = await res.json();
            if (!res.ok) {
                resultsEl.replaceChildren();
                const p = document.createElement('p');
                p.className = 'error';
                p.textContent = data.error || 'Search failed';
                resultsEl.appendChild(p);
                return;
            }
            renderResults(data.results || []);
        } catch (e) {
            resultsEl.replaceChildren();
            const p = document.createElement('p');
            p.className = 'error';
            p.textContent = 'Could not reach the search API.';
            resultsEl.appendChild(p);
        }
    }

    if (searchBtn) {
        searchBtn.addEventListener('click', runSearch);
        searchInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                runSearch();
            }
        });
    }

    syncStatusOptions();
})();
