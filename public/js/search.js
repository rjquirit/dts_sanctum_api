document.addEventListener('DOMContentLoaded', function () {
    // Defensive: wait for all elements to exist
    function initSearch() {
        const searchBtn = document.getElementById('search-btn');
        const docIdInput = document.getElementById('doc_id_input');
        const errorDiv = document.getElementById('search-error');
        const timelineContainer = document.getElementById('timeline-container');
        if (!searchBtn || !docIdInput || !errorDiv || !timelineContainer) {
            // Try again shortly if elements not found
            setTimeout(initSearch, 100);
            return;
        }
        searchBtn.addEventListener('click', function () {
            const docId = docIdInput.value.trim();
            errorDiv.classList.add('hidden');
            timelineContainer.innerHTML = '';
            if (!docId) {
                errorDiv.textContent = 'Please enter a document tracking number.';
                errorDiv.classList.remove('hidden');
                return;
            }
            // Debug: show loading
            timelineContainer.innerHTML = '<div class="text-gray-500">Searching...</div>';
            fetch(`/api/docmain/track/${encodeURIComponent(docId)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error || data.message) {
                        errorDiv.textContent = data.error || data.message;
                        errorDiv.classList.remove('hidden');
                        timelineContainer.innerHTML = '';
                    } else if (data.timeline && data.timeline.length > 0) {
                        timelineContainer.innerHTML = renderTimeline(data.timeline);
                    } else {
                        errorDiv.textContent = 'No results found.';
                        errorDiv.classList.remove('hidden');
                        timelineContainer.innerHTML = '';
                    }
                })
                .catch((e) => {
                    errorDiv.textContent = 'An error occurred while searching.';
                    errorDiv.classList.remove('hidden');
                    timelineContainer.innerHTML = '';
                    console.error('Search error:', e);
                });
        });
    }
    function renderTimeline(timeline) {
        return `<ul class="timeline-list">${timeline.map(item => `
            <li class="mb-4">
                <div class="font-semibold">${item.status}</div>
                <div class="text-sm text-gray-600">${item.date}</div>
                <div>${item.details || ''}</div>
            </li>
        `).join('')}</ul>`;
    }
    initSearch();
});
