// Функция для форматирования даты
export function formatDate(date) {
    const year = date.year;
    const month = String(date.month).padStart(2, '0');
    const day = String(date.day).padStart(2, '0');

    return `${year}-${month}-${day}`;
}
