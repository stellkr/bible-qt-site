document.getElementById('loginForm').addEventListener('submit', function(event) {
    event.preventDefault(); // 기본 제출 이벤트 방지

    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;
    const errorMessage = document.getElementById('error-message');

    errorMessage.style.display = 'none';

    const data = { username, password };

    fetch('http://localhost/login.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams(data),
    })
    .then(response => response.json())
    .then(data => {
        if (data.message === '로그인 성공') {
            // ✅ user_type이 undefined인 경우 빈 문자열로 대체
            const userType = data.user_type ? ` ${data.user_type}` : "";
            
            alert(`${data.name}님${userType}, 로그인되었습니다.`);
            setTimeout(() => { window.location.href = 'http://localhost/index.php'; }, 1000);
        } else {
            errorMessage.innerHTML = data.message;
            errorMessage.style.display = 'block';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        errorMessage.innerHTML = '서버에 연결할 수 없습니다. 나중에 다시 시도해주세요.';
        errorMessage.style.display = 'block';
    });
});
