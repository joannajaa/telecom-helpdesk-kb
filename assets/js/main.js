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
    const likeText = document.getElementById('like-text');
    const likesCount = document.getElementById('likes-count');
    const feedback = document.getElementById('rating-feedback');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

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
                    if (likeText) {
                        likeText.textContent = data.action === 'added' ? 'Cofnij ocenę' : 'Pomocna';
                    }
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
});