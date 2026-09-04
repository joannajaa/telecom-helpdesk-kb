document.addEventListener('DOMContentLoaded', () => {

    const themeSwitch = document.getElementById('theme-switch');
    const savedTheme = localStorage.getItem('theme') || 'light';

    document.documentElement.setAttribute('data-theme', savedTheme);
    if (themeSwitch) {
        themeSwitch.value = savedTheme;

        themeSwitch.addEventListener('change', (e) => {
            const selectedTheme = e.target.value;
            document.documentElement.setAttribute('data-theme', selectedTheme);
            localStorage.setItem('theme', selectedTheme);
        });
    }

    const likeBtn = document.getElementById('like-btn');
    const likesCount = document.getElementById('likes-count');
    const feedback = document.getElementById('rating-feedback');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    const searchInput = document.querySelector('.filters-form input[name="search"]');
    const articlesList = document.querySelector('.articles-list');
    let searchTimer;
    let searchRequest;

    if (searchInput && articlesList) {
        const escapeHtml = (value) => String(value)
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');

        const renderArticles = (articles) => {
            if (!articles.length) {
                articlesList.innerHTML = '<p>Brak artykułów spełniających kryteria.</p>';
                return;
            }

            articlesList.innerHTML = articles.map((article) => {
                const isOutdated = article.category_name === 'Nieaktualne';
                const cardStyle = isOutdated
                    ? 'border-left: 4px solid #9ca3af; background: rgba(156, 163, 175, 0.08);'
                    : (article.is_pinned ? 'border-left: 4px solid #f59e0b; background: rgba(245, 158, 11, 0.05);' : '');
                const image = article.image
                    ? `<div class="article-thumbnail"><a href="article.php?id=${article.id}"><img src="uploads/${escapeHtml(article.image)}" alt="${escapeHtml(article.title)}"></a></div>`
                    : '';
                const tags = article.tags.map((tag) => `<a href="index.php?tag[]=${encodeURIComponent(tag)}" class="tag-link">#${escapeHtml(tag)}</a>`).join(' ');
                const titleClass = isOutdated ? 'outdated-title' : '';
                const rating = isOutdated ? '0' : `+${article.upvotes_count}`;

                return `<article class="article-card" style="${cardStyle}">
                    ${image}
                    <div class="article-body">
                        <h3>
                            ${article.is_pinned ? '<span style="color: #f59e0b; font-size: 0.9em; margin-right: 4px;" title="Przypięty artykuł">📌</span>' : ''}
                            <a href="article.php?id=${article.id}" class="${titleClass}">${escapeHtml(article.title)}</a>
                        </h3>
                        <p class="article-meta">Kategoria:
                            <a class="category-link" href="index.php?cat=${article.category_id}"><strong>${escapeHtml(article.category_name)}</strong></a>
                            | Autor: ${escapeHtml(article.username)}
                            | ${escapeHtml(new Date(article.created_at).toLocaleDateString('pl-PL'))}
                            | Oceny: <strong>${rating}</strong>
                        </p>
                        <div class="article-tags">${tags}</div>
                        <p>${escapeHtml(article.content.substring(0, 150))}...</p>
                        <a href="article.php?id=${article.id}" class="read-more">Czytaj całą instrukcję &rarr;</a>
                    </div>
                </article>`;
            }).join('');
        };

        searchInput.addEventListener('input', () => {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(async () => {
                const form = searchInput.form;
                const params = new URLSearchParams(new FormData(form));
                params.set('search', searchInput.value.trim());
                params.delete('page');
                params.set('sort', form.querySelector('[name="sort"]').value);
                if (searchRequest) {
                    searchRequest.abort();
                }
                searchRequest = new AbortController();
                articlesList.classList.add('is-loading');

                try {
                    const response = await fetch(`search_ajax.php?${params.toString()}`, { signal: searchRequest.signal });
                    if (!response.ok) {
                        throw new Error('Search request failed');
                    }
                    const data = await response.json();
                    renderArticles(data.articles);
                    document.querySelectorAll('.pagination').forEach((pagination) => {
                        pagination.hidden = true;
                    });
                } catch (error) {
                    if (error.name !== 'AbortError') {
                        articlesList.innerHTML = '<p>Nie udało się wyszukać artykułów.</p>';
                    }
                } finally {
                    articlesList.classList.remove('is-loading');
                }
            }, 250);
        });
    }

    if (likeBtn) {
        likeBtn.addEventListener('click', async () => {
            const articleId = likeBtn.getAttribute('data-id');
            feedback.textContent = '';
            likeBtn.disabled = true;

            try {
                const response = await fetch('rate_ajax.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': csrfToken
                    },
                    body: JSON.stringify({ article_id: articleId })
                });

                const data = await response.json();

                if (data.success) {
                    likesCount.textContent = data.likes ?? data.new_likes;
                    feedback.style.color = '#10b981';
                    feedback.textContent = data.message;
                } else {
                    feedback.style.color = '#ef4444';
                    feedback.textContent = data.message;
                }
            } catch (error) {
                feedback.style.color = '#ef4444';
                feedback.textContent = 'Wystąpił błąd podczas wysyłania oceny.';
            } finally {
                likeBtn.disabled = false;
            }
        });
    }

    document.querySelectorAll('.reply-toggle').forEach((toggle) => {
        toggle.addEventListener('click', () => {
            const replyForm = document.getElementById(toggle.dataset.target);
            if (!replyForm) {
                return;
            }

            replyForm.hidden = !replyForm.hidden;
            if (!replyForm.hidden) {
                replyForm.querySelector('input[name="comment_content"]').focus();
            }
        });
    });

    const notificationsMenu = document.querySelector('.notifications-menu');
    if (notificationsMenu) {
        notificationsMenu.addEventListener('toggle', async () => {
            if (!notificationsMenu.open || !notificationsMenu.querySelector('.notification-badge')) {
                return;
            }

            const formData = new FormData();
            formData.append('action', 'mark_all_read');
            formData.append('csrf_token', csrfToken);

            try {
                const response = await fetch('notification_read.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();
                if (!data.success) {
                    console.error(data.message || 'Nie udało się oznaczyć powiadomień jako przeczytanych.');
                    return;
                }

                const badge = notificationsMenu.querySelector('.notification-badge');
                const summary = notificationsMenu.querySelector('.notification-summary');
                if (badge) {
                    badge.remove();
                }
                if (summary) {
                    summary.classList.remove('has-unread');
                }
            } catch (error) {
                console.error(error);
            }
        });
    }
});