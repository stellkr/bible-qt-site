// 학년별 반 설정
const gradeToClassMap = {
    "1학년": ["1반", "2반", "3반"],
    "2학년": ["1반", "2반"],
    "3학년": ["1반", "2반", "3반", "4반"]
};

const gradeSelect = document.getElementById('grade');
const classSelect = document.getElementById('class');
const errorMessage = document.getElementById('error-message');

// 📌 학년 선택 시 반 목록 업데이트 (기존 옵션 초기화 후 추가)
gradeSelect.addEventListener('change', function() {
    const selectedGrade = gradeSelect.value;
    classSelect.innerHTML = '<option value="">선택하세요</option>'; // 기본 옵션 추가

    if (selectedGrade && gradeToClassMap[selectedGrade]) {
        gradeToClassMap[selectedGrade].forEach(cls => {
            let option = document.createElement('option');
            option.value = cls;
            option.textContent = cls;
            classSelect.appendChild(option);
        });
    }
});

document.getElementById('registrationForm').addEventListener('submit', function(event) {
    event.preventDefault(); // 기본 제출 이벤트 방지

    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;
    const name = document.getElementById('name').value;
    const grade = gradeSelect.value;
    const classValue = classSelect.value;

    // 오류 메시지 초기화
    errorMessage.style.display = 'none';
    const inputFields = [username, password, name, grade, classValue];
    const inputs = [
        document.getElementById('username'),
        document.getElementById('password'),
        document.getElementById('name'),
        gradeSelect,
        classSelect
    ];

    let hasError = false;
    const errorMessages = [];

    // 입력값 검사
    inputs.forEach((input, index) => {
        if (!inputFields[index]) {
            input.classList.add('error');
            hasError = true;
            errorMessages.push(`${inputs[index].previousElementSibling.textContent} 필드를 입력하세요.`);
        } else {
            input.classList.remove('error');
        }
    });

    // 아이디 유효성 검사
    if (username.length < 6 || /[\u3131-\u3163\uac00-\ud7a3]/.test(username)) {
        errorMessages.push('아이디는 6글자 이상이어야 하며 한글을 포함할 수 없습니다.');
        hasError = true;
    }

    // 비밀번호 유효성 검사
    if (password.length < 6) {
        errorMessages.push('비밀번호는 6글자 이상이어야 합니다.');
        hasError = true;
    }

    // 비밀번호 기호 포함 여부 검사
    const specialCharPattern = /[!@#$%^&*(),.?":{}|<>]/;
    if (!specialCharPattern.test(password)) {
        errorMessages.push("비밀번호는 하나 이상의 기호를 포함해야 합니다.");
        hasError = true;
    }

    if (!/[a-z]/.test(password)) {
        errorMessages.push("비밀번호는 최소 하나의 영문자를 포함해야 합니다.");
        hasError = true;
    }
    if (!/[0-9]/.test(password)) {
        errorMessages.push("비밀번호는 최소 하나의 숫자를 포함해야 합니다.");
        hasError = true;
    }

    if (hasError) {
        errorMessage.innerHTML = errorMessages.join('<br>');
        errorMessage.style.display = 'block';
    } else {
        const data = {
            username,
            password,
            name,
            grade,
            class: classValue
        };

        fetch('http://localhost/register.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams(data),
        })
        .then(response => response.json())
        .then(data => {
            if (data.message === '회원가입이 완료되었습니다') {
                alert('회원가입이 완료되었습니다.');
                setTimeout(() => {
                    window.location.href = '../login/login.html';
                }, 1000);
            } else {
                errorMessage.innerHTML = data.message;
                errorMessage.style.display = 'block';
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }
});
