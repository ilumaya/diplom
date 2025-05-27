// Функция для переключения видимости содержимого аккордеона
function toggleAccordionContent(event) {
    const button = event.currentTarget; // Получаем кнопку, на которую нажали
    const content = button.nextElementSibling; // Находим следующий элемент (содержимое)

    // Переключаем видимость содержимого
    content.style.display = content.style.display === 'block' ? 'none' : 'block';
}

// Назначаем обработчик событий для всех кнопок аккордеона
document.querySelectorAll('.accordion-button').forEach(button => {
    button.addEventListener('click', toggleAccordionContent);
});

// Открытие модальных окон
document.getElementById('registerBtn').onclick = function() {
    document.getElementById('registerModal').style.display = 'block';
}
document.getElementById('loginBtn').onclick = function() {
    document.getElementById('loginModal').style.display = 'block';
}

// Закрытие модальных окон
function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

document.getElementById('closeRegister').onclick = function() {
    closeModal('registerModal');
}
document.getElementById('closeLogin').onclick = function() {
    closeModal('loginModal');
}

// Закрытие модального окна при клике вне его
window.onclick = function(event) {
    if (event.target === document.getElementById('registerModal')) {
        closeModal('registerModal');
    }
    if (event.target === document.getElementById('loginModal')) {
        closeModal('loginModal');
    }
}
