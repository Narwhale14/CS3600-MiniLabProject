import { ref } from 'vue';

export const loggedIn = ref(false);

export async function api(action: string, body: object = {}) {
    try {
        const response = await fetch('/api/shortlink.php', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ action, ...body }) });
        return await response.json();
    } catch {
        return { error: 'Unable to connect to the server' };
    }
}

export async function getSession() {
    loggedIn.value = (await api('getSession')).loggedIn ?? false;
}

export function checkUsername(username: string) {
    username = username.trim();
    if(username === '') return 'Username is required';
    if(username.length > 50) return 'Username must be 50 characters or less';
    if(!/^[a-zA-Z0-9_]+$/.test(username)) return 'Username can only contain letters, numbers and underscores';
    return '';
}

export function checkEmail(email: string) {
    email = email.trim();
    if(email === '') return 'Email is required';
    if(!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) return 'Please enter a valid email';
    return '';
}

export function checkPassword(password: string) {
    if(password === '') return 'Password is required';
    if(password.length < 8) return 'Password must be at least 8 characters';
    return '';
}

export function checkConfirmPassword(password: string, confirmPassword: string) {
    if(confirmPassword === '') return 'Please confirm your password';
    if(confirmPassword !== password) return 'Passwords do not match';
    return '';
}
