import { ref } from 'vue'

export const loggedIn = ref(false)
export const csrfToken = ref('')

export async function getSession() {
    const response = await fetch('/api/shortlink.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            action: 'getSession'
        })
    })

    if (!response.ok) {
        throw new Error('Failed to retrieve session')
    }

    const data = await response.json()

    loggedIn.value = data.loggedIn
    csrfToken.value = data.csrfToken
}

export function checkUsername(username: string) {
    username = username.trim();
    if(username === '') return 'Username is required';
    if(!/^[a-zA-Z0-9_]+$/.test(username)) return 'Username can only contain letters, numbers and underscores';
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
