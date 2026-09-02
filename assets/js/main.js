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

    if (likeBtn) {
        likeBtn.addEventListener('click', async () => {
            const articleId = likeBtn.getAttribute('data-id');

            try {
                const response = await fetch('rate_ajax.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ article_id: articleId })
                });

                const data = await response.json();

                if (data.success) {
                    likesCount.textContent = data.new_likes;
                    feedback.style.color = 'green';
                    feedback.textContent = data.message;
                    likeBtn.disabled = true; 
                } else {
                    feedback.style.color = 'red';
                    feedback.textContent = data.message;
                }
            } catch (error) {
                feedback.style.color = 'red';
                feedback.textContent = 'Wystąpił błąd podczas wysyłania oceny.';
            }
        });
    }
});