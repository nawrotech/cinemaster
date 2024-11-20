export function timeDisplayConverter(date) {
    const dateTimeObject = dateTimeObjectConverter(date);
    return `${String(dateTimeObject.hour).padStart(2, "0")}:${String(dateTimeObject.minute).padStart(2, "0")}`;
}

export function dateTimeObjectConverter(date) {
    const dateTimeObject = new Date(date);
    return {
        day: dateTimeObject.getUTCDay(),
        year: dateTimeObject.getUTCFullYear(),
        month: dateTimeObject.getUTCMonth(),
        hour: dateTimeObject.getUTCHours(),
        minute: dateTimeObject.getUTCMinutes(),
    };
}